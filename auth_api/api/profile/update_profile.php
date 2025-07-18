<?php
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../utils/JWTHandler.php';

header('Content-Type: application/json');

try {
    // Validate JWT token
    $headers = getallheaders();
    $jwt = new JWTHandler();
    $token = str_replace('Bearer ', '', $headers['Authorization']);
    $decoded = $jwt->validateToken($token);

    if (!$decoded) {
        throw new Exception('Invalid token', 401);
    }

    // Debug the decoded token
    error_log("Decoded token: " . print_r($decoded, true));

    $input = json_decode(file_get_contents('php://input'));

    // Debug the input
    error_log("Input data: " . print_r($input, true));

    // Use the coop_id from the request body instead
    if (!isset($input->coop_id)) {
        throw new Exception('CoopID is required');
    }

    $database = new Database();
    $db = $database->getConnection();

    $query = "UPDATE tbl_personalinfo SET 
        EmailAddress = :email,
        MobilePhone = :mobile,
        Address = :address,
        City = :town,
        State = :state,
        UpdatedBy = :updated_by,
        DateUpdated = NOW()
        WHERE memberid = :coop_id";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $input->email);
    $stmt->bindParam(':mobile', $input->mobile);
    $stmt->bindParam(':address', $input->address);
    $stmt->bindParam(':town', $input->town);
    $stmt->bindParam(':state', $input->state);
    $stmt->bindParam(':updated_by', $input->coop_id);
    $stmt->bindParam(':coop_id', $input->coop_id);

    if($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update profile');
    }
} catch(Exception $e) {
    error_log("Error updating profile: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code($e->getCode() ?: 400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}