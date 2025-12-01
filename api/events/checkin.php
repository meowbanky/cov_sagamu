<?php
/**
 * Mobile Check-in API
 * Allows users to check in to events with device binding and location validation
 */

require_once('../../Connections/cov.php');
require_once('../../libs/utils/DistanceCalculator.php');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

mysqli_select_db($cov, $database_cov);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $eventId = intval($input['event_id'] ?? 0);
    $userCoopId = intval($input['user_coop_id'] ?? 0);
    $latitude = floatval($input['latitude'] ?? 0);
    $longitude = floatval($input['longitude'] ?? 0);
    $deviceId = trim($input['device_id'] ?? '');
    
    // Validation
    if (!$eventId || !$userCoopId) {
        throw new Exception('Event ID and User Coop ID are required', 400);
    }
    if ($latitude == 0 || $longitude == 0) {
        throw new Exception('Location coordinates are required', 400);
    }
    if (empty($deviceId)) {
        throw new Exception('Device ID is required', 400);
    }
    
    // Get event details
    $eventQuery = "SELECT 
        id, start_time, end_time, location_lat, location_lng, 
        geofence_radius, COALESCE(grace_period_minutes, 20) as grace_period_minutes
    FROM events WHERE id = ?";
    $eventStmt = mysqli_prepare($cov, $eventQuery);
    mysqli_stmt_bind_param($eventStmt, "i", $eventId);
    mysqli_stmt_execute($eventStmt);
    $eventResult = mysqli_stmt_get_result($eventStmt);
    $event = mysqli_fetch_assoc($eventResult);
    mysqli_stmt_close($eventStmt);
    
    if (!$event) {
        throw new Exception('Event not found', 404);
    }
    
    // Validate user exists
    $userQuery = "SELECT memberid FROM tbl_personalinfo WHERE memberid = ? LIMIT 1";
    $userStmt = mysqli_prepare($cov, $userQuery);
    mysqli_stmt_bind_param($userStmt, "i", $userCoopId);
    mysqli_stmt_execute($userStmt);
    $userResult = mysqli_stmt_get_result($userStmt);
    if (mysqli_num_rows($userResult) === 0) {
        mysqli_stmt_close($userStmt);
        throw new Exception('Invalid User Coop ID', 400);
    }
    mysqli_stmt_close($userStmt);
    
    $now = new DateTime();
    $startTime = new DateTime($event['start_time']);
    $endTime = new DateTime($event['end_time']);
    $endTime->modify("+{$event['grace_period_minutes']} minutes");
    $checkInDeadline = $endTime->format('Y-m-d H:i:s');
    
    // Time window validation
    if ($now < $startTime) {
        $startFormatted = $startTime->format('F j, Y g:i A');
        throw new Exception("Check-in is only available during the event. Event starts at {$startFormatted}", 400);
    }
    
    if ($now > $endTime) {
        $deadlineFormatted = $endTime->format('F j, Y g:i A');
        throw new Exception("Check-in period has ended. The grace period expired at {$deadlineFormatted}", 400);
    }
    
    // Check if user already checked in
    $checkUserQuery = "SELECT id FROM event_attendance 
        WHERE event_id = ? AND user_coop_id = ?";
    $checkUserStmt = mysqli_prepare($cov, $checkUserQuery);
    mysqli_stmt_bind_param($checkUserStmt, "ii", $eventId, $userCoopId);
    mysqli_stmt_execute($checkUserStmt);
    $checkUserResult = mysqli_stmt_get_result($checkUserStmt);
    
    if (mysqli_num_rows($checkUserResult) > 0) {
        mysqli_stmt_close($checkUserStmt);
        throw new Exception('You have already checked in to this event', 400);
    }
    mysqli_stmt_close($checkUserStmt);
    
    // Check if device already used by another user
    $checkDeviceQuery = "SELECT user_coop_id FROM event_attendance 
        WHERE event_id = ? AND device_id = ? AND user_coop_id != ? LIMIT 1";
    $checkDeviceStmt = mysqli_prepare($cov, $checkDeviceQuery);
    mysqli_stmt_bind_param($checkDeviceStmt, "isi", $eventId, $deviceId, $userCoopId);
    mysqli_stmt_execute($checkDeviceStmt);
    $checkDeviceResult = mysqli_stmt_get_result($checkDeviceStmt);
    
    if (mysqli_num_rows($checkDeviceResult) > 0) {
        mysqli_stmt_close($checkDeviceStmt);
        throw new Exception('This device has already been used to check in another user for this event', 400);
    }
    mysqli_stmt_close($checkDeviceStmt);
    
    // Calculate distance from event location
    $distance = DistanceCalculator::calculateDistance(
        $event['location_lat'],
        $event['location_lng'],
        $latitude,
        $longitude
    );
    
    // Location validation
    if ($distance > $event['geofence_radius']) {
        echo json_encode([
            'success' => false,
            'message' => 'You are too far from the event location',
            'distance' => round($distance, 2),
            'required_radius' => $event['geofence_radius'],
            'within_range' => false
        ]);
        exit;
    }
    
    // Insert check-in record
    $insertQuery = "INSERT INTO event_attendance 
        (event_id, user_coop_id, check_in_lat, check_in_lng, 
         distance_from_event, device_id, status, admin_override)
        VALUES (?, ?, ?, ?, ?, ?, 'present', 0)";
    
    $insertStmt = mysqli_prepare($cov, $insertQuery);
    mysqli_stmt_bind_param($insertStmt, "iiddds", 
        $eventId, $userCoopId, $latitude, $longitude, 
        $distance, $deviceId);
    
    if (mysqli_stmt_execute($insertStmt)) {
        echo json_encode([
            'success' => true,
            'message' => 'Check-in successful',
            'distance' => round($distance, 2),
            'within_range' => true
        ]);
    } else {
        throw new Exception('Failed to check in: ' . mysqli_error($cov), 500);
    }
    mysqli_stmt_close($insertStmt);
    
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

