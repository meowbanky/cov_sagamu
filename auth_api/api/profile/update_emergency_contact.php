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

    $input = json_decode(file_get_contents('php://input'));

    $database = new Database();
    $db = $database->getConnection();

    // First check if record exists
    $checkQuery = "SELECT memberid FROM tbl_nok WHERE memberid = :coop_id";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':coop_id', $input->coop_id);
    $checkStmt->execute();
error_log('emmergency '.$input->coop_id);
    if ($checkStmt->rowCount() > 0) {
        // Update existing record
        $query = "UPDATE tbl_nok SET 
            nokfirstname = :first_name,
            nokmiddlename = :middle_name,
            noklastname = :last_name,
            NOKPhone = :phone
            WHERE memberid = :coop_id";
    } else {
        // Insert new record
        $query = "INSERT INTO tbl_nok (
            memberid,
            nokfirstname,
            nokmiddlename,
            noklastname,
            NOKPhone
        ) VALUES (
            :coop_id,
            :first_name,
            :middle_name,
            :last_name,
            :phone
        )";
    }

    $stmt = $db->prepare($query);
    $stmt->bindParam(':first_name', $input->nok_first_name);
    $stmt->bindParam(':middle_name', $input->nok_middle_name);
    $stmt->bindParam(':last_name', $input->nok_last_name);
    $stmt->bindParam(':phone', $input->nok_tel);
    $stmt->bindParam(':coop_id', $input->coop_id);

    if($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Emergency contact updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update emergency contact');
    }
} catch(Exception $e) {
    error_log("Emergency contact error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code($e->getCode() ?: 400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}