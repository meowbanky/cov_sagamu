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
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../utils/MobileAuth.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Required a token: this took coop_id from the body with no authentication,
    // so anyone could repoint another member's push notifications at their device.
    $coop_id = MobileAuth::requireMemberId();

    $data = json_decode(file_get_contents("php://input"));

    if (!isset($data->onesignal_id)) {
        throw new Exception("Missing required parameters", 400);
    }

    $query = "UPDATE tbl_personalinfo 
              SET onesignal_id = :onesignal_id 
              WHERE memberid = :coop_id";

    $stmt = $db->prepare($query);
    $stmt->bindParam(":onesignal_id", $data->onesignal_id);
    $stmt->bindParam(":coop_id", $coop_id);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "OneSignal ID stored successfully"
        ]);
    } else {
        throw new Exception("Failed to store OneSignal ID");
    }

} catch(Exception $e) {
    $code = $e->getCode();
    http_response_code(is_int($code) && $code >= 400 && $code <= 599 ? $code : 500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>