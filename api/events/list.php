<?php
/**
 * Mobile Event List API
 * Lists events filtered by status (upcoming/active/past/all)
 * Includes has_checked_in flag for current user if authenticated
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
    
    $filter = strtolower($_GET['filter'] ?? 'all'); // upcoming, active, past, all
    $userCoopId = isset($_GET['user_coop_id']) ? intval($_GET['user_coop_id']) : null; // For checking if user checked in
    
    $now = date('Y-m-d H:i:s');
    
    // Base query
    $query = "SELECT 
        e.*,
        CASE 
            WHEN NOW() < e.start_time THEN 'upcoming'
            WHEN NOW() > DATE_ADD(e.end_time, INTERVAL e.grace_period_minutes MINUTE) THEN 'past'
            ELSE 'active'
        END as status
    FROM events e";
    
    // Apply filter
    if ($filter === 'upcoming') {
        $query .= " WHERE NOW() < e.start_time";
    } elseif ($filter === 'active') {
        $query .= " WHERE NOW() >= e.start_time 
            AND NOW() <= DATE_ADD(e.end_time, INTERVAL e.grace_period_minutes MINUTE)";
    } elseif ($filter === 'past') {
        $query .= " WHERE NOW() > DATE_ADD(e.end_time, INTERVAL e.grace_period_minutes MINUTE)";
    }
    
    $query .= " ORDER BY e.start_time DESC";
    
    $result = mysqli_query($cov, $query);
    
    if (!$result) {
        throw new Exception("Failed to query events: " . mysqli_error($cov));
    }
    
    $events = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $hasCheckedIn = false;
        
        // Check if user has checked in (if user_coop_id provided)
        if ($userCoopId) {
            $checkQuery = "SELECT id FROM event_attendance 
                WHERE event_id = ? AND user_coop_id = ? LIMIT 1";
            $checkStmt = mysqli_prepare($cov, $checkQuery);
            mysqli_stmt_bind_param($checkStmt, "ii", $row['id'], $userCoopId);
            mysqli_stmt_execute($checkStmt);
            $checkResult = mysqli_stmt_get_result($checkStmt);
            $hasCheckedIn = mysqli_num_rows($checkResult) > 0;
            mysqli_stmt_close($checkStmt);
        }
        
        // Calculate check-in deadline
        $endTime = new DateTime($row['end_time']);
        $endTime->modify("+{$row['grace_period_minutes']} minutes");
        $checkInDeadline = $endTime->format('Y-m-d H:i:s');
        
        $events[] = [
            'id' => intval($row['id']),
            'title' => $row['title'],
            'description' => $row['description'],
            'start_time' => $row['start_time'],
            'end_time' => $row['end_time'],
            'check_in_deadline' => $checkInDeadline,
            'location_lat' => floatval($row['location_lat']),
            'location_lng' => floatval($row['location_lng']),
            'geofence_radius' => intval($row['geofence_radius']),
            'grace_period_minutes' => intval($row['grace_period_minutes'] ?? 20),
            'status' => $row['status'],
            'has_checked_in' => $hasCheckedIn
        ];
    }
    
    echo json_encode([
        'success' => true,
        'filter' => $filter,
        'data' => $events
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

