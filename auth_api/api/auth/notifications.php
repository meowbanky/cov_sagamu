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


// Set JSON content type
header('Content-Type: application/json; charset=UTF-8');

try {
    // Include dependencies
    require_once __DIR__ . '/../../config/Database.php';
    require_once __DIR__ . '/../../models/User.php';
    require_once __DIR__ . '/../../utils/JWTHandler.php';
    require_once __DIR__ . '/../../utils/MobileAuth.php';

    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();

    // The coop_id in the query string is ignored. It previously let any member
    // read any other member's notifications by changing one number.
    $coop_id = MobileAuth::requireMemberId();

    // Handle different request methods
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if (isset($_GET['unread-count'])&& isset($_GET['count'])) {
                getUnreadCount($db, $coop_id);
            } else {
                getNotifications($db, $coop_id);
            }
            break;

        case 'PUT':
            if (preg_match('/\/notifications\.php\/(\d+)\/read$/', $_SERVER['REQUEST_URI'], $matches)) {
                markAsRead($db, $matches[1], $coop_id); // Pass the extracted notification ID
            } else {
                throw new Exception('Invalid endpoint', 404);
            }

            break;

        default:
            throw new Exception('Method not allowed', 405);
    }

} catch (Exception $e) {
    error_log("Notification error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    http_response_code($e->getCode() ?: 400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function getNotifications($db, $coop_id) {
    try {
        $query = "SELECT id, memberid, title, message, status, created_at, updated_at 
              FROM notifications 
              WHERE memberid = :coop_id 
              ORDER BY created_at DESC";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':coop_id', $coop_id, PDO::PARAM_STR);

        $stmt->execute();

        $notifications = [];

        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $notifications[] = $row;
            }

            echo json_encode([
                'success' => true,
                'data' => $notifications
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No notifications found for the given Coop ID'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function getUnreadCount($db, $coop_id) {
    try {

        $query = "SELECT COUNT(*) as count 
                  FROM notifications 
                  WHERE memberid = :coop_id AND status != 'read'";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':coop_id', $coop_id, PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'count' => intval($result['count'])
        ]);
        error_log('Unread count: ' . $result['count']);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}


function markAsRead($db, $notification_id, $coop_id) {
    try {
        $notification_id = intval($notification_id);

        // Scoped to the caller: without the memberid clause any member could mark
        // any other member's notification as read.
        $query = "UPDATE notifications 
                  SET status = 'read', updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :notification_id AND memberid = :coop_id";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':notification_id', $notification_id, PDO::PARAM_INT);
        $stmt->bindParam(':coop_id', $coop_id, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        } else {
            throw new Exception('Notification not found or unauthorized');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
