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
require_once __DIR__ . '/../../utils/MemberLookup.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'));
    // Takes a member id, not an email: the client never handles a real
    // address, so none can be harvested from the reset flow.
    if (!isset($data->coop_id) || !isset($data->otp)) {
        throw new Exception('Email and OTP are required');
    }

    $database = new Database();
    $db = $database->getConnection();

    $memberId = trim($data->coop_id);
    $email = MemberLookup::emailForMember($db, $memberId);
    if ($email === null) {
        throw new Exception('Invalid or expired code');
    }

    $sql = "SELECT * FROM tbl_password_resets 
            WHERE email = :email 
            AND otp = :otp 
            AND expiry_time > UTC_TIMESTAMP()  
            AND used = 0 
            ORDER BY created_at DESC 
            LIMIT 1";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':otp', $data->otp);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'OTP verified successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid or expired OTP'
        ]);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}