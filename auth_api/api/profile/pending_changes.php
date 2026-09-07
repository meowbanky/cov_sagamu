<?php
// api/profile/pending_changes.php

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


    $database = new Database();
    $db = $database->getConnection();

    // Get profile changes
    $profile_stmt = $db->prepare("
        SELECT field_name, old_value, new_value, submitted_at, status
        FROM pending_profile_changes
        WHERE staff_id = ? AND status = 'pending'
        ORDER BY submitted_at DESC
    ");
    $profile_stmt->execute([$staff_id]);
    $profile_changes = $profile_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get qualification changes
    $qual_stmt = $db->prepare("
        SELECT id, qua_id, field, institution, year_obtained, 
               change_type, original_qualification_id, submitted_at, status
        FROM pending_qualification_changes
        WHERE staff_id = ? AND status = 'pending'
        ORDER BY submitted_at DESC
    ");
    $qual_stmt->execute([$staff_id]);
    $qualification_changes = $qual_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get overall status
    $status_stmt = $db->prepare("
        SELECT has_pending_changes, 
               (SELECT MAX(submitted_at) FROM pending_profile_changes 
                WHERE staff_id = e.staff_id AND status = 'pending') as latest_submission
        FROM employee e
        WHERE staff_id = ?
    ");
    $status_stmt->execute([$staff_id]);
    $status = $status_stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            'has_pending_changes' => (bool)$status['has_pending_changes'],
            'submitted_at' => $status['latest_submission'],
            'pending_profile_changes' => array_reduce($profile_changes, function($carry, $item) {
                $carry[$item['field_name']] = $item['new_value'];
                return $carry;
            }, []),
            'pending_qualification_changes' => array_map(function($item) {
                return [
                    'original_id' => $item['original_qualification_id'],
                    'change_type' => $item['change_type'],
                    'data' => [
                        'qua_id' => $item['qua_id'],
                        'field' => $item['field'],
                        'institution' => $item['institution'],
                        'year_obtained' => $item['year_obtained']
                    ]
                ];
            }, $qualification_changes),
            'status' => 'pending'
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