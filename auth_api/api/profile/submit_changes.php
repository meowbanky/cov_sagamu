<?php
// api/profile/submit_changes.php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../config/Database.php';
require_once '../../utils/JWTHandler.php';
require_once __DIR__ . '/../../utils/ProfileFields.php';
require_once __DIR__ . '/../../utils/MobileAuth.php';
function logProfileChange($db, $staffId, $fieldName, $oldValue, $newValue, $changedBy, $source = 'approval', $approvalId = null) {
    $stmt = $db->prepare("
        INSERT INTO profile_change_log 
        (staff_id, change_type, field_name, old_value, new_value, changed_by, change_source, approval_id)
        VALUES 
        (?, 'update', ?, ?, ?, ?, ?, ?)
    ");

    return $stmt->execute([
        $staffId,
        $fieldName,
        $oldValue,
        $newValue,
        $changedBy,
        $source,
        $approvalId
    ]);
}

function logQualificationChange($db, $staffId, $changeType, $qualificationData, $changedBy, $source = 'pending', $approvalId = null) {
    $stmt = $db->prepare("
        INSERT INTO profile_change_log 
        (staff_id, change_type, field_name, old_value, new_value, changed_by, change_source, approval_id)
        VALUES 
        (?, ?, 'qualification', ?, ?, ?, ?, ?)
    ");

    $newValue = json_encode($qualificationData);

    return $stmt->execute([
        $staffId,
        $changeType,
        null,
        $newValue,
        $changedBy,
        $source,
        $approvalId
    ]);
}

try {
    // Identity comes from the signed token, never from the request. Taking a
    // staff_id from the caller let any valid token read or change any other
    // person's record.
    $staff_id = MobileAuth::requireStaffId();

    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);

    // Both the subject and the submitter are the authenticated caller. A staff_id
    // in the body previously let anyone file changes against a colleague's record.
    $submitted_by = $staff_id;

    if (!isset($data['profile_changes'])) {
        throw new Exception('Invalid request data', 400);
    }

    $profile_changes = $data['profile_changes'];
    $qualification_changes = $data['qualification_changes'] ?? [];

    $database = new Database();
    $db = $database->getConnection();

    // Start transaction
    $db->beginTransaction();

    // Check if there are already pending changes
    $check_stmt = $db->prepare("SELECT has_pending_changes FROM employee WHERE staff_id = ?");
    $check_stmt->execute([$staff_id]);
    $current_status = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if ($current_status && $current_status['has_pending_changes']) {
        throw new Exception('There are already pending changes for this profile', 400);
    }

    // Insert profile changes
    $profile_stmt = $db->prepare("
        INSERT INTO pending_profile_changes 
        (staff_id, field_name, old_value, new_value, submitted_by)
        VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($profile_changes as $field => $value) {
        // $field is interpolated into the SELECT below, so it must be a known
        // column and not caller-supplied SQL.
        ProfileFields::assertEditable($field);

        // Get current value
        $current_stmt = $db->prepare("SELECT $field FROM employee WHERE staff_id = ?");
        $current_stmt->execute([$staff_id]);
        $current_value = $current_stmt->fetchColumn();

        // Only insert if value is actually changing
        if ($current_value !== $value) {
            $profile_stmt->execute([
                $staff_id,
                $field,
                $current_value,
                $value,
                $submitted_by  // Use the validated submitted_by value
            ]);

            logProfileChange(
                $db,
                $staff_id,
                $field,
                $current_value,
                $value,
                $submitted_by,
                'pending'  // Changed from 'approval' to 'pending'
            );
        }

    }

    // Handle qualification changes
    if (!empty($qualification_changes)) {
        $qual_stmt = $db->prepare("
            INSERT INTO pending_qualification_changes 
            (staff_id, qua_id, field, institution, year_obtained, 
             change_type, original_qualification_id, submitted_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($qualification_changes as $change) {
            $qual_stmt->execute([
                $staff_id,
                $change['data']['qua_id'] ?? null,
                $change['data']['field'] ?? null,
                $change['data']['institution'] ?? null,
                $change['data']['year_obtained'] ?? null,
                $change['change_type'],
                $change['id'] ?? null,
                $submitted_by  // Use the validated submitted_by value
            ]);

            logQualificationChange(
                $db,
                $staff_id,
                $change['change_type'],
                $change['data'],
                $submitted_by,
                'pending'
            );
        }
    }

    // Update employee pending status
    $update_stmt = $db->prepare("
        UPDATE employee 
        SET has_pending_changes = true 
        WHERE staff_id = ?
    ");
    $update_stmt->execute([$staff_id]);

    // Commit transaction
    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Changes submitted successfully'
    ]);

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }

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