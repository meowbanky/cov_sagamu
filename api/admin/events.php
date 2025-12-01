<?php
/**
 * Admin Event Management API
 * Handles CRUD operations for events
 * 
 * Endpoints:
 * POST   - Create event
 * GET    - List all events or get single event
 * PUT    - Update event
 * DELETE - Delete event
 */

session_start();
require_once('../../Connections/cov.php');
require_once('../../libs/utils/DistanceCalculator.php');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Check authentication
if (!isset($_SESSION['UserID'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

mysqli_select_db($cov, $database_cov);
$method = $_SERVER['REQUEST_METHOD'];
$adminUsername = $_SESSION['UserID'] ?? 'admin';

try {
    // Handle OPTIONS request
    if ($method === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
    
    // Handle GET request (list or single event)
    if ($method === 'GET') {
        $eventId = isset($_GET['id']) ? intval($_GET['id']) : null;
        
        if ($eventId) {
            // Get single event with attendance
            $query = "SELECT 
                e.*,
                COUNT(DISTINCT ea.id) as attendance_count
            FROM events e
            LEFT JOIN event_attendance ea ON ea.event_id = e.id
            WHERE e.id = ?
            GROUP BY e.id";
            
            $stmt = mysqli_prepare($cov, $query);
            mysqli_stmt_bind_param($stmt, "i", $eventId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($row = mysqli_fetch_assoc($result)) {
                // Get attendance list
                $attendanceQuery = "SELECT 
                    ea.*,
                    CONCAT(IFNULL(p.Lname, ''), ', ', IFNULL(p.Fname, ''), ' ', IFNULL(p.Mname, '')) as member_name
                FROM event_attendance ea
                LEFT JOIN tbl_personalinfo p ON p.memberid = ea.user_coop_id
                WHERE ea.event_id = ?
                ORDER BY ea.check_in_time DESC";
                
                $attStmt = mysqli_prepare($cov, $attendanceQuery);
                mysqli_stmt_bind_param($attStmt, "i", $eventId);
                mysqli_stmt_execute($attStmt);
                $attResult = mysqli_stmt_get_result($attStmt);
                
                $attendance = [];
                while ($attRow = mysqli_fetch_assoc($attResult)) {
                    $attendance[] = [
                        'id' => intval($attRow['id']),
                        'user_coop_id' => intval($attRow['user_coop_id']),
                        'member_name' => trim($attRow['member_name'] ?? 'Unknown'),
                        'check_in_time' => $attRow['check_in_time'],
                        'check_in_lat' => floatval($attRow['check_in_lat']),
                        'check_in_lng' => floatval($attRow['check_in_lng']),
                        'distance_from_event' => floatval($attRow['distance_from_event']),
                        'device_id' => $attRow['device_id'],
                        'status' => $attRow['status'],
                        'admin_override' => (bool)($attRow['admin_override'] ?? 0),
                        'checked_in_by_admin' => $attRow['checked_in_by_admin']
                    ];
                }
                mysqli_stmt_close($attStmt);
                
                $row['attendance_count'] = intval($row['attendance_count']);
                $row['attendance'] = $attendance;
                
                echo json_encode([
                    'success' => true,
                    'data' => $row
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Event not found']);
            }
            mysqli_stmt_close($stmt);
        } else {
            // List all events
            $query = "SELECT 
                e.*,
                COUNT(DISTINCT ea.id) as attendance_count,
                CASE 
                    WHEN NOW() < e.start_time THEN 'upcoming'
                    WHEN NOW() > DATE_ADD(e.end_time, INTERVAL e.grace_period_minutes MINUTE) THEN 'past'
                    ELSE 'active'
                END as status
            FROM events e
            LEFT JOIN event_attendance ea ON ea.event_id = e.id
            GROUP BY e.id
            ORDER BY e.start_time DESC";
            
            $result = mysqli_query($cov, $query);
            
            if (!$result) {
                throw new Exception("Failed to query events: " . mysqli_error($cov));
            }
            
            $events = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $events[] = [
                    'id' => intval($row['id']),
                    'title' => $row['title'],
                    'description' => $row['description'],
                    'start_time' => $row['start_time'],
                    'end_time' => $row['end_time'],
                    'location_lat' => floatval($row['location_lat']),
                    'location_lng' => floatval($row['location_lng']),
                    'geofence_radius' => intval($row['geofence_radius']),
                    'grace_period_minutes' => intval($row['grace_period_minutes'] ?? 20),
                    'created_by' => $row['created_by'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                    'attendance_count' => intval($row['attendance_count']),
                    'status' => $row['status']
                ];
            }
            
            echo json_encode([
                'success' => true,
                'data' => $events
            ]);
        }
    }
    
    // Handle POST request (create event)
    elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $title = trim($input['title'] ?? '');
        $description = trim($input['description'] ?? '');
        $startTime = trim($input['start_time'] ?? '');
        $endTime = trim($input['end_time'] ?? '');
        $locationLat = floatval($input['location_lat'] ?? 0);
        $locationLng = floatval($input['location_lng'] ?? 0);
        $geofenceRadius = intval($input['geofence_radius'] ?? 50);
        $gracePeriodMinutes = intval($input['grace_period_minutes'] ?? 20);
        
        // Validation
        if (empty($title)) {
            throw new Exception('Event title is required', 400);
        }
        if (empty($startTime) || empty($endTime)) {
            throw new Exception('Start time and end time are required', 400);
        }
        if ($locationLat == 0 || $locationLng == 0) {
            throw new Exception('Event location is required', 400);
        }
        if ($geofenceRadius < 10 || $geofenceRadius > 500) {
            throw new Exception('Geofence radius must be between 10 and 500 meters', 400);
        }
        if ($gracePeriodMinutes < 0 || $gracePeriodMinutes > 120) {
            throw new Exception('Grace period must be between 0 and 120 minutes', 400);
        }
        
        $startDateTime = new DateTime($startTime);
        $endDateTime = new DateTime($endTime);
        
        if ($endDateTime <= $startDateTime) {
            throw new Exception('End time must be after start time', 400);
        }
        
        $query = "INSERT INTO events 
            (title, description, start_time, end_time, location_lat, location_lng, 
             geofence_radius, grace_period_minutes, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($cov, $query);
        mysqli_stmt_bind_param($stmt, "ssssddiis", 
            $title, $description, $startTime, $endTime, 
            $locationLat, $locationLng, $geofenceRadius, $gracePeriodMinutes, $adminUsername);
        
        if (mysqli_stmt_execute($stmt)) {
            $eventId = mysqli_insert_id($cov);
            echo json_encode([
                'success' => true,
                'message' => 'Event created successfully',
                'event_id' => $eventId
            ]);
        } else {
            throw new Exception('Failed to create event: ' . mysqli_error($cov), 500);
        }
        mysqli_stmt_close($stmt);
    }
    
    // Handle PUT request (update event)
    elseif ($method === 'PUT') {
        $eventId = isset($_GET['id']) ? intval($_GET['id']) : null;
        if (!$eventId) {
            throw new Exception('Event ID is required', 400);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $updates = [];
        $types = '';
        $params = [];
        
        if (isset($input['title'])) {
            $updates[] = "title = ?";
            $types .= "s";
            $params[] = trim($input['title']);
        }
        if (isset($input['description'])) {
            $updates[] = "description = ?";
            $types .= "s";
            $params[] = trim($input['description']);
        }
        if (isset($input['start_time'])) {
            $updates[] = "start_time = ?";
            $types .= "s";
            $params[] = trim($input['start_time']);
        }
        if (isset($input['end_time'])) {
            $updates[] = "end_time = ?";
            $types .= "s";
            $params[] = trim($input['end_time']);
        }
        if (isset($input['location_lat'])) {
            $updates[] = "location_lat = ?";
            $types .= "d";
            $params[] = floatval($input['location_lat']);
        }
        if (isset($input['location_lng'])) {
            $updates[] = "location_lng = ?";
            $types .= "d";
            $params[] = floatval($input['location_lng']);
        }
        if (isset($input['geofence_radius'])) {
            $geofenceRadius = intval($input['geofence_radius']);
            if ($geofenceRadius < 10 || $geofenceRadius > 500) {
                throw new Exception('Geofence radius must be between 10 and 500 meters', 400);
            }
            $updates[] = "geofence_radius = ?";
            $types .= "i";
            $params[] = $geofenceRadius;
        }
        if (isset($input['grace_period_minutes'])) {
            $gracePeriodMinutes = intval($input['grace_period_minutes']);
            if ($gracePeriodMinutes < 0 || $gracePeriodMinutes > 120) {
                throw new Exception('Grace period must be between 0 and 120 minutes', 400);
            }
            $updates[] = "grace_period_minutes = ?";
            $types .= "i";
            $params[] = $gracePeriodMinutes;
        }
        
        if (empty($updates)) {
            throw new Exception('No fields to update', 400);
        }
        
        $types .= "i"; // for event_id
        $params[] = $eventId;
        
        $query = "UPDATE events SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = mysqli_prepare($cov, $query);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode([
                'success' => true,
                'message' => 'Event updated successfully'
            ]);
        } else {
            throw new Exception('Failed to update event: ' . mysqli_error($cov), 500);
        }
        mysqli_stmt_close($stmt);
    }
    
    // Handle DELETE request
    elseif ($method === 'DELETE') {
        $eventId = isset($_GET['id']) ? intval($_GET['id']) : null;
        if (!$eventId) {
            throw new Exception('Event ID is required', 400);
        }
        
        $query = "DELETE FROM events WHERE id = ?";
        $stmt = mysqli_prepare($cov, $query);
        mysqli_stmt_bind_param($stmt, "i", $eventId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode([
                'success' => true,
                'message' => 'Event deleted successfully'
            ]);
        } else {
            throw new Exception('Failed to delete event: ' . mysqli_error($cov), 500);
        }
        mysqli_stmt_close($stmt);
    }
    
    else {
        throw new Exception('Method not allowed', 405);
    }
    
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

