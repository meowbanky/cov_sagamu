<?php
// api/profile/get_change_history.php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../config/Database.php';
require_once '../../utils/JWTHandler.php';
require_once __DIR__ . '/../../utils/MobileAuth.php';

try {
    // Identity comes from the signed token, never from the request. Taking a
    // staff_id from the caller let any valid token read or change any other
    // person's record.
    $staff_id = MobileAuth::requireStaffId();

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
    $offset = ($page - 1) * $per_page;

    $database = new Database();
    $db = $database->getConnection();

    // Get total count
    $count_stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM profile_change_log 
        WHERE staff_id = :staff_id
    ");
    $count_stmt->execute([':staff_id' => $staff_id]);
    $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get changes with pagination
    $query = "
        SELECT 
            pcl.*,
            e.NAME as changed_by_name
        FROM profile_change_log pcl
        LEFT JOIN employee e ON e.staff_id = pcl.changed_by
        WHERE pcl.staff_id = :staff_id
        ORDER BY pcl.changed_at DESC
        LIMIT :offset, :limit
    ";

    $stmt = $db->prepare($query);
    $stmt->bindValue(':staff_id', $staff_id, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->execute();

    $changes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            'changes' => $changes,
            'pagination' => [
                'total' => $total,
                'per_page' => $per_page,
                'current_page' => $page,
                'total_pages' => ceil($total / $per_page)
            ]
        ]
    ]);

} catch (Exception $e) {
    $status_code = $e->getCode();
    if (!is_int($status_code) || $status_code < 100 || $status_code > 599) {
        $status_code = 400;
    }

    http_response_code($status_code);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}