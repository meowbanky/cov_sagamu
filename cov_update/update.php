<?php
// cov_update/update.php
//
// Step 2 of a verified self-update. Applies a member's own changes to their
// contact and bank details.
//
// SECURITY HISTORY: this endpoint used to accept a coop_no plus new details
// from anyone, overwrite the record with no proof of ownership, and then email
// the member's PLAINTEXT password to the (attacker-supplied) address — a full
// account-takeover path. It now requires a one-time code that was sent to the
// member's EXISTING registered email (see send_update_otp.php), and it no
// longer emails any password.

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../auth_api/utils/MemberLookup.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

header('Content-Type: text/plain; charset=UTF-8');
ini_set('display_errors', '0');

function fail($msg)
{
    http_response_code(400);
    echo $msg;
    exit();
}

$coop_no    = trim($_POST['coop_no'] ?? '');
$otp        = trim($_POST['otp'] ?? '');
$mobile     = htmlspecialchars($_POST['mobile'] ?? '', ENT_QUOTES, 'UTF-8');
$email      = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$account_no = htmlspecialchars($_POST['account_no'] ?? '', ENT_QUOTES, 'UTF-8');
$bank       = htmlspecialchars($_POST['bank'] ?? '', ENT_QUOTES, 'UTF-8');
$surname    = htmlspecialchars($_POST['surname'] ?? '', ENT_QUOTES, 'UTF-8');
$firstname  = htmlspecialchars($_POST['firstname'] ?? '', ENT_QUOTES, 'UTF-8');
$middlename = htmlspecialchars($_POST['middlename'] ?? '', ENT_QUOTES, 'UTF-8');

if ($coop_no === '') {
    fail('Member number is required.');
}
if ($otp === '') {
    fail('A verification code is required. Please request one first.');
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fail('Please enter a valid email address.');
}

try {
    $conn = new PDO(
        'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'],
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD']
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- Verify the one-time code against the member's existing contact ---
    $key = 'covupdate:' . $coop_no;
    $stmt = $conn->prepare(
        'SELECT id FROM tbl_password_resets
          WHERE email = :key AND otp = :otp
            AND expiry_time > UTC_TIMESTAMP()
          ORDER BY id DESC LIMIT 1'
    );
    $stmt->bindParam(':key', $key);
    $stmt->bindParam(':otp', $otp);
    $stmt->execute();
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset) {
        fail('That verification code is invalid or has expired. Please request a new one.');
    }

    // Single-use: burn every outstanding code for this member.
    $del = $conn->prepare('DELETE FROM tbl_password_resets WHERE email = :key');
    $del->bindParam(':key', $key);
    $del->execute();

    // Member must exist and be active.
    $stmt = $conn->prepare(
        'SELECT memberid FROM tbl_personalinfo WHERE memberid = ? AND deleted_at IS NULL'
    );
    $stmt->execute([$coop_no]);
    if (!$stmt->fetch()) {
        fail('Member not found.');
    }

    // --- Apply the changes ---
    // Only overwrite fields the member actually supplied. The search step no
    // longer prefills existing values (it must not leak them), so a blank field
    // means "leave unchanged", never "erase". Without this, submitting the form
    // to change one field would wipe the member's bank account number.
    $personalSets = [];
    $personalArgs = [];
    foreach ([
        'mobilephone'  => $mobile,
        'emailaddress' => $email,
        'lname'        => strtoupper($surname),
        'fname'        => strtoupper($firstname),
        'mname'        => strtoupper($middlename),
    ] as $column => $value) {
        if ($value !== '') {
            $personalSets[] = "$column = ?";
            $personalArgs[] = $value;
        }
    }
    if ($personalSets) {
        $personalArgs[] = $coop_no;
        $conn->prepare(
            'UPDATE tbl_personalinfo SET ' . implode(', ', $personalSets)
            . ' WHERE memberid = ?'
        )->execute($personalArgs);
    }

    // Bank details are a pair — only touch them when both are provided, so a
    // half-filled form cannot leave an account number without its bank.
    if ($account_no !== '' && $bank !== '') {
        $stmt = $conn->prepare('SELECT coopno FROM tblaccountno WHERE coopno = ?');
        $stmt->execute([$coop_no]);
        if ($stmt->fetch()) {
            $conn->prepare('UPDATE tblaccountno SET accountNo = ?, bank_code = ? WHERE coopno = ?')
                 ->execute([$account_no, $bank, $coop_no]);
        } else {
            $conn->prepare('INSERT INTO tblaccountno (accountNo, bank_code, coopno) VALUES (?,?,?)')
                 ->execute([$account_no, $bank, $coop_no]);
        }
    }

    // --- Confirmation email: NO credentials, sent to the address on file now ---
    $confirmTo = MemberLookup::emailForMember($conn, $coop_no);
    if ($confirmTo !== null) {
        sendConfirmation($confirmTo, strtoupper($firstname));
    }

    echo '2'; // legacy success sentinel the front-end checks for
} catch (Exception $e) {
    error_log('cov_update/update: ' . $e->getMessage());
    fail('We could not save your changes. Please try again later.');
}

function sendConfirmation($to, $firstName)
{
    $body = "Dear {$firstName}, your cooperative contact and account details "
        . "have just been updated. If you did not make this change, contact the "
        . "cooperative office immediately.";

    try {
        require_once __DIR__ . '/mail/mail/vendor/autoload.php';
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->SMTPDebug  = SMTP::DEBUG_OFF;
        $mail->Host       = $_ENV['SMTP_HOST'] ?? '';
        $mail->Port       = $_ENV['SMTP_PORT'] ?? 465;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USERNAME'] ?? '';
        $mail->Password   = $_ENV['SMTP_PASSWORD'] ?? '';
        $mail->setFrom('no-reply@emmaggi.com', 'VCMS');
        $mail->addReplyTo('no-reply@emmaggi.com', 'VCMS');
        $mail->addAddress($to, $firstName);
        $mail->Subject = 'VCMS — your details were updated';
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);
        $mail->send();
    } catch (Exception $e) {
        // A failed confirmation email must not fail the update itself.
        error_log('cov_update confirmation email failed: ' . $e->getMessage());
    }
}
