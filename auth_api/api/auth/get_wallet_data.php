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

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../utils/JWTHandler.php';

try {
    // Validate token
    $headers = getallheaders();
    $token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');

    // Log the token for debugging
    error_log("Token: " . $token);

    $jwt = new JWTHandler();
    $userData = $jwt->validateToken($token);

    // Log the user data for debugging
    error_log("User Data: " . print_r($userData, true));

    // Validate user data
    if (!$userData || !isset($userData['user_id'])) {
        throw new Exception('Invalid token or missing user_id');
    }

    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT 
        e.memberid as CoopID,
        IFNULL(e.Picture,'') as profile_image,
        e.Fname as FirstName,
        COALESCE(SUM(t.shares), 0) as shares_balance,
        COALESCE(SUM(t.savings), 0) as savings_balance,
        COALESCE(SUM(t.loanAmount), 0) - COALESCE(SUM(t.loanRepayment), 0) as unpaid_loan,
        (COALESCE(SUM(t.shares), 0) + COALESCE(SUM(t.savings), 0)) as total_balance
    FROM tbl_personalinfo e
    LEFT JOIN tlb_mastertransaction t ON e.memberid = t.memberid
    WHERE e.memberid = :coop_id
    GROUP BY e.memberid, e.Picture, e.Fname";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':coop_id', $userData['user_id']); // Use user_id from the token payload
    $stmt->execute();

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode([
            'success' => true,
            'data' => $row
        ]);
    } else {
        throw new Exception('No data found');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>