<?php
// cov_update/send_update_otp.php
//
// Step 1 of a verified self-update: sends a one-time code to the member's
// EXISTING registered email. This is what stops the account-takeover — a caller
// must control the address already on file before they can change anything,
// including that address itself.
//
// It never reveals the email, and responds identically whether or not the
// member exists, so it cannot be used to probe member ids.

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../auth_api/utils/RateLimiter.php';
require_once __DIR__ . '/../auth_api/utils/MemberLookup.php';
require_once __DIR__ . '/../auth_api/utils/EmailService.php';

if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();
}

header('Content-Type: application/json; charset=UTF-8');
ini_set('display_errors', '0');

const OTP_RL_HITS = 5;
const OTP_RL_WINDOW = 900;

$genericOk = [
    'success' => true,
    'message' => 'If that member has an email on file, a verification code has been sent to it.',
];

try {
    $coopNo = isset($_POST['coop_no']) ? trim($_POST['coop_no']) : '';
    if ($coopNo === '') {
        throw new Exception('Member number is required', 400);
    }

    $db = new PDO(
        'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'],
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD']
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    RateLimiter::enforce($db, 'cov_update_otp', OTP_RL_HITS, OTP_RL_WINDOW);

    $email = MemberLookup::emailForMember($db, $coopNo);
    if ($email === null) {
        // Do not disclose that the member is unknown / has no address.
        echo json_encode($genericOk);
        exit();
    }

    $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiry = (new DateTime('now', new DateTimeZone('UTC')))
        ->add(new DateInterval('PT15M'))
        ->format('Y-m-d H:i:s');

    // Key the OTP by member id so update.php can verify it without the client
    // ever handling the address. (email column reused as the key store.)
    $stmt = $db->prepare(
        'INSERT INTO tbl_password_resets (email, otp, expiry_time)
         VALUES (:key, :otp, :expiry)'
    );
    $key = 'covupdate:' . $coopNo;
    $stmt->bindParam(':key', $key);
    $stmt->bindParam(':otp', $otp);
    $stmt->bindParam(':expiry', $expiry);
    $stmt->execute();

    (new EmailService())->sendOTP(
        $email,
        $otp
    );

    echo json_encode($genericOk);
} catch (Exception $e) {
    error_log('cov_update/send_update_otp: ' . $e->getMessage());
    // Still generic, so failures do not leak member existence.
    echo json_encode($genericOk);
}
