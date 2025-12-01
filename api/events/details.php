<?php
/**
 * Mobile Event Details API
 * Get single event details with check-in status
 */

require_once('../../Connections/cov.php');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

mysqli_select_db($cov, $database_cov);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
    
    $eventId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $userCoopId = isset($_GET['user_coop_id']) ? intval($_GET['user_coop_id']) : null;
    
    if (!$eventId) {
        throw new Exception('Event ID is required', 400);
    }
    
    $query = "SELECT * FROM events WHERE id = ?";
    $stmt = mysqli_prepare($cov, $query);
    mysqli_stmt_bind_param($stmt, "i", $eventId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $event = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$event) {
        throw new Exception('Event not found', 404);
    }
    
    $hasCheckedIn = false;
    
    // Check if user has checked in (if user_coop_id provided)
    if ($userCoopId) {
        $checkQuery = "SELECT id FROM event_attendance 
            WHERE event_id = ? AND user_coop_id = ? LIMIT 1";
        $checkStmt = mysqli_prepare($cov, $checkQuery);
        mysqli_stmt_bind_param($checkStmt, "ii", $eventId, $userCoopId);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);
        $hasCheckedIn = mysqli_num_rows($checkResult) > 0;
        mysqli_stmt_close($checkStmt);
    }
    
    // Calculate status and check-in deadline
    $now = new DateTime();
    $startTime = new DateTime($event['start_time']);
    $endTime = new DateTime($event['end_time']);
    $endTime->modify("+{$event['grace_period_minutes']} minutes");
    
    $status = 'past';
    if ($now < $startTime) {
        $status = 'upcoming';
    } elseif ($now <= $endTime) {
        $status = 'active';
    }
    
    $event['status'] = $status;
    $event['check_in_deadline'] = $endTime->format('Y-m-d H:i:s');
    $event['has_checked_in'] = $hasCheckedIn;
    $event['grace_period_minutes'] = intval($event['grace_period_minutes'] ?? 20);
    $event['geofence_radius'] = intval($event['geofence_radius']);
    $event['location_lat'] = floatval($event['location_lat']);
    $event['location_lng'] = floatval($event['location_lng']);
    
    echo json_encode([
        'success' => true,
        'data' => $event
    ]);
    
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

