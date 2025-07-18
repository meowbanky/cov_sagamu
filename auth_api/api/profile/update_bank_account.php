<?php

if (ob_get_level()) ob_end_clean();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 1728000');
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../utils/JWTHandler.php';

try {
    $headers = getallheaders();
    $token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');

    error_log("Token: " . $token);

    $jwt = new JWTHandler();
    $userData = $jwt->validateToken($token);

    error_log("User Data: " . print_r($userData, true));

    if (!$userData || !isset($userData['user_id'])) {
        throw new Exception('Invalid token or missing user_id');
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['bank_sort_code']) || !isset($data['account_number'])) {
        throw new Exception('Missing required fields');
    }

    $database = new Database();
    $db = $database->getConnection();

    // Start transaction
    $db->beginTransaction();

    try {
        // Get bank details
        $bankQuery = "SELECT Bank_Name FROM Bank_Sortcodes WHERE bank_code = :sort_code";
        $bankStmt = $db->prepare($bankQuery);
        $bankStmt->bindParam(':sort_code', $data['bank_sort_code']);
        $bankStmt->execute();
        $bankData = $bankStmt->fetch(PDO::FETCH_ASSOC);

        if (!$bankData) {
            throw new Exception('Invalid bank sort code');
        }

        // Check if record exists
        $checkQuery = "SELECT COOPNO FROM tblaccountno WHERE COOPNO = :coop_id";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':coop_id', $userData['user_id']);
        $checkStmt->execute();

        if ($checkStmt->fetch()) {
            // Update existing record
            $query = "UPDATE tblaccountno 
                     SET Bank = :bank_name,
                         AccountNo = :account_no,
                         bank_code = :sort_code 
                     WHERE COOPNO = :coop_id";
        } else {
            // Insert new record
            $query = "INSERT INTO tblaccountno 
                     (COOPNO, Bank, AccountNo, bank_code)
                     VALUES 
                     (:coop_id, :bank_name, :account_no, :sort_code)";
        }

        $stmt = $db->prepare($query);
        $stmt->bindParam(':coop_id', $userData['user_id']);
        $stmt->bindParam(':bank_name', $bankData['Bank_Name']);
        $stmt->bindParam(':account_no', $data['account_number']);
        $stmt->bindParam(':sort_code', $data['bank_sort_code']);
        $stmt->execute();

        // Commit transaction
        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Bank account details updated successfully'
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $db->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    error_log("Error in update bank account: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>