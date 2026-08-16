<?php
if (ob_get_level()) ob_end_clean();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Set all required CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 1728000');
header('Content-Type: application/json; charset=UTF-8');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ .'/../../utils/EmailService.php';
require_once __DIR__ . '/../../utils/MemberLookup.php';
require_once __DIR__ . '/../../utils/RateLimiter.php';
header('Content-Type: application/json');

// Unauthenticated endpoint that sends mail — do not let it become a relay.
const OTP_RATE_LIMIT_HITS = 5;
const OTP_RATE_LIMIT_WINDOW_SECONDS = 900;

try {
    $data = json_decode(file_get_contents('php://input'));

    // Takes a member id, NOT an email address. Previously this accepted any
    // address and mailed an OTP to it without checking it belonged to a member,
    // which made it an open mail relay and leaked which addresses exist.
    if (!isset($data->coop_id) || trim($data->coop_id) === '') {
        throw new Exception('Member ID is required');
    }
    $memberId = trim($data->coop_id);

    $database = new Database();
    $db = $database->getConnection();

    RateLimiter::enforce($db, 'password_reset_otp', OTP_RATE_LIMIT_HITS, OTP_RATE_LIMIT_WINDOW_SECONDS);

    $email = MemberLookup::emailForMember($db, $memberId);

    // Respond identically whether or not the member exists, so this cannot be
    // used to probe which member ids are real.
    if ($email === null) {
        echo json_encode([
            'success' => true,
            'message' => 'If that account exists, a code has been sent to the email address on file.',
        ]);
        exit();
    }

    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

// Calculate expiry time in UTC
    $expiryTime = (new DateTime('now', new DateTimeZone('UTC'))) // Current time in UTC
    ->add(new DateInterval('PT15M')) // Add 15 minutes
    ->format('Y-m-d H:i:s');
    // Store OTP in database
    $sql = "INSERT INTO tbl_password_resets (email, otp, expiry_time) 
            VALUES (:email, :otp, :expiry_time)";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':otp', $otp);
    $stmt->bindParam(':expiry_time', $expiryTime);
    $stmt->execute();

    // Send email
    $emailSender = new EmailService();
    $emailSender->sendOTP($email, $otp);

    echo json_encode([
        'success' => true,
        'message' => 'If that account exists, a code has been sent to the email address on file.'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}