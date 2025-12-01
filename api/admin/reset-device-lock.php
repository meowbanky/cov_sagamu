<?php
/**
 * Reset Device Lock API
 * Allows admin to reset device lock for an event
 * Deletes attendance records for a specific device_id
 */

session_start();
require_once('../../Connections/cov.php');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Check authentication
if (!isset($_SESSION['UserID'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

mysqli_select_db($cov, $database_cov);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $eventId = intval($input['event_id'] ?? 0);
    $deviceId = trim($input['device_id'] ?? '');
    
    // Validation
    if (!$eventId || empty($deviceId)) {
        throw new Exception('Event ID and Device ID are required', 400);
    }
    
    // Delete attendance records for this device
    $deleteQuery = "DELETE FROM event_attendance 
        WHERE event_id = ? AND device_id = ?";
    $deleteStmt = mysqli_prepare($cov, $deleteQuery);
    mysqli_stmt_bind_param($deleteStmt, "is", $eventId, $deviceId);
    mysqli_stmt_execute($deleteStmt);
    $deletedCount = mysqli_affected_rows($cov);
    mysqli_stmt_close($deleteStmt);
    
    echo json_encode([
        'success' => true,
        'message' => 'Device lock reset successfully',
        'deleted_count' => $deletedCount
    ]);
    
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

