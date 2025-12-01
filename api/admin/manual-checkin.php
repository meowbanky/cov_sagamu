<?php
/**
 * Manual Check-in API
 * Allows admin to manually check in members
 * Bypasses location and time validation if skip_location_check is true
 */

session_start();
require_once('../../Connections/cov.php');
require_once('../../libs/utils/DistanceCalculator.php');

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
$adminUsername = $_SESSION['UserID'];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $eventId = intval($input['event_id'] ?? 0);
    $userCoopId = intval($input['user_coop_id'] ?? 0);
    $deviceId = trim($input['device_id'] ?? '');
    $skipLocationCheck = isset($input['skip_location_check']) && $input['skip_location_check'] === true;
    
    // Validation
    if (!$eventId || !$userCoopId) {
        throw new Exception('Event ID and Member ID are required', 400);
    }
    
    // Validate member exists
    $validateQuery = "SELECT memberid FROM tbl_personalinfo WHERE memberid = ? LIMIT 1";
    $validateStmt = mysqli_prepare($cov, $validateQuery);
    mysqli_stmt_bind_param($validateStmt, "i", $userCoopId);
    mysqli_stmt_execute($validateStmt);
    $validateResult = mysqli_stmt_get_result($validateStmt);
    
    if (mysqli_num_rows($validateResult) === 0) {
        mysqli_stmt_close($validateStmt);
        throw new Exception('Invalid Member ID. Member not found in the system.', 400);
    }
    mysqli_stmt_close($validateStmt);
    
    // Get event details
    $eventQuery = "SELECT * FROM events WHERE id = ?";
    $eventStmt = mysqli_prepare($cov, $eventQuery);
    mysqli_stmt_bind_param($eventStmt, "i", $eventId);
    mysqli_stmt_execute($eventStmt);
    $eventResult = mysqli_stmt_get_result($eventStmt);
    $event = mysqli_fetch_assoc($eventResult);
    mysqli_stmt_close($eventStmt);
    
    if (!$event) {
        throw new Exception('Event not found', 404);
    }
    
    // Check if user already checked in
    $checkQuery = "SELECT id FROM event_attendance 
        WHERE event_id = ? AND user_coop_id = ?";
    $checkStmt = mysqli_prepare($cov, $checkQuery);
    mysqli_stmt_bind_param($checkStmt, "ii", $eventId, $userCoopId);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);
    
    if (mysqli_num_rows($checkResult) > 0) {
        mysqli_stmt_close($checkStmt);
        throw new Exception('This member has already checked in to this event', 400);
    }
    mysqli_stmt_close($checkStmt);
    
    // Check device if provided
    if (!empty($deviceId)) {
        $deviceCheckQuery = "SELECT user_coop_id FROM event_attendance 
            WHERE event_id = ? AND device_id = ? AND user_coop_id != ? LIMIT 1";
        $deviceCheckStmt = mysqli_prepare($cov, $deviceCheckQuery);
        mysqli_stmt_bind_param($deviceCheckStmt, "isi", $eventId, $deviceId, $userCoopId);
        mysqli_stmt_execute($deviceCheckStmt);
        $deviceCheckResult = mysqli_stmt_get_result($deviceCheckStmt);
        
        if (mysqli_num_rows($deviceCheckResult) > 0) {
            mysqli_stmt_close($deviceCheckStmt);
            throw new Exception('This device has already been used to check in another member for this event', 400);
        }
        mysqli_stmt_close($deviceCheckStmt);
    } else {
        // Auto-generate device ID if not provided
        $deviceId = 'admin-override-' . time() . '-' . rand(1000, 9999);
    }
    
    // Set location and distance
    if ($skipLocationCheck) {
        $checkInLat = floatval($event['location_lat']);
        $checkInLng = floatval($event['location_lng']);
        $distance = 0;
    } else {
        // Use event location (could also get current admin location if needed)
        $checkInLat = floatval($event['location_lat']);
        $checkInLng = floatval($event['location_lng']);
        $distance = 0;
    }
    
    // Insert check-in record
    $insertQuery = "INSERT INTO event_attendance 
        (event_id, user_coop_id, check_in_lat, check_in_lng, 
         distance_from_event, device_id, status, admin_override, checked_in_by_admin)
        VALUES (?, ?, ?, ?, ?, ?, 'present', 1, ?)";
    
    $insertStmt = mysqli_prepare($cov, $insertQuery);
    mysqli_stmt_bind_param($insertStmt, "iidddss", 
        $eventId, $userCoopId, $checkInLat, $checkInLng, 
        $distance, $deviceId, $adminUsername);
    
    if (mysqli_stmt_execute($insertStmt)) {
        echo json_encode([
            'success' => true,
            'message' => 'Member checked in successfully'
        ]);
    } else {
        throw new Exception('Failed to check in member: ' . mysqli_error($cov), 500);
    }
    mysqli_stmt_close($insertStmt);
    
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

