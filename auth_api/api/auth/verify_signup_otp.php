<?php
require_once __DIR__ . '/../../config/Database.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'));
    if (!isset($data->email) || !isset($data->otp)) {
        throw new Exception('Email and OTP are required');
    }

    $database = new Database();
    $db = $database->getConnection();

    $sql = "SELECT * FROM tbl_signup_otp 
            WHERE email = :email 
            AND otp = :otp 
            AND expiry_time > UTC_TIMESTAMP()  
            AND used = 0 
            ORDER BY created_at DESC 
            LIMIT 1";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(':email', $data->email);
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