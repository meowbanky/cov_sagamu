<?php
// api/complaints/list.php
//
// Returns the authenticated member's own complaints, newest first. The member
// id comes from the token, so one member can never read another's.

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../utils/MobileAuth.php';

MobileAuth::applyCors('GET, OPTIONS');

const MAX_COMPLAINTS_RETURNED = 50;

try {
    $memberId = MobileAuth::requireMemberId();

    $database = new Database();
    $db = $database->getConnection();

    $stmt = $db->prepare(
        'SELECT id, category, subject, body, status, admin_reply, created_at, updated_at
           FROM tbl_complaints
          WHERE memberid = :member_id
          ORDER BY created_at DESC
          LIMIT ' . MAX_COMPLAINTS_RETURNED
    );
    $stmt->bindParam(':member_id', $memberId);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
    ]);
} catch (Exception $e) {
    error_log('complaints/list: ' . $e->getMessage());
    MobileAuth::fail($e);
}
