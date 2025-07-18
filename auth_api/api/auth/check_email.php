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

try {
    $coopId = $_GET['coopId'] ?? '';
    if (empty($coopId)) {
        throw new Exception('CoopID is required');
    }

    require_once __DIR__ . '/../../config/Database.php';
    $database = new Database();
    $db = $database->getConnection();

    // Check if user exists in tblusers_online
    $sql = "SELECT UserID FROM tblusers WHERE UserID = :coopId";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':coopId', $coopId);
    $stmt->execute();

    $hasRegisteredAccount = $stmt->rowCount() > 0;
//    error_log('email: '. $hasRegisteredAccount);
    echo json_encode([
        'success' => true,
        'hasEmail' => $hasRegisteredAccount,
        'message' => $hasRegisteredAccount ? 'User already registered' : 'User not registered'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}