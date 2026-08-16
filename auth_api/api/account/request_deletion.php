<?php
// api/account/request_deletion.php
//
// Member-initiated account deletion REQUEST (App Store Guideline 5.1.1(v)).
//
// A cooperative member may not erase their own record while they owe the
// society money, so deletion is not self-service: the member always initiates
// it here, and an officer approves it in cov_admin/deletion_requests.php.
// Apple permits this completion step for regulated financial services provided
// the request itself can always be raised inside the app — so this endpoint
// NEVER refuses to record a request, it only reports the outcome.

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../utils/MobileAuth.php';
require_once __DIR__ . '/../../utils/MemberBalance.php';

MobileAuth::applyCors('POST, OPTIONS');

const MAX_MEMBER_NOTE = 500;

try {
    $memberId = MobileAuth::requireMemberId();
    $body = MobileAuth::jsonBody();

    $password = isset($body['password']) ? (string) $body['password'] : '';
    if ($password === '') {
        throw new Exception('Password confirmation is required', 400);
    }

    $memberNote = isset($body['note']) ? trim((string) $body['note']) : '';
    if (mb_strlen($memberNote) > MAX_MEMBER_NOTE) {
        throw new Exception('Note is too long', 400);
    }

    $database = new Database();
    $db = $database->getConnection();

    // Re-authenticate: an unlocked handset must not be able to start this.
    $stmt = $db->prepare('SELECT UPassword FROM tblusers WHERE UserID = :member_id');
    $stmt->bindParam(':member_id', $memberId);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['UPassword'])) {
        throw new Exception('Password is incorrect', 401);
    }

    // Already closed?
    $stmt = $db->prepare('SELECT deleted_at FROM tbl_personalinfo WHERE memberid = :member_id');
    $stmt->bindParam(':member_id', $memberId);
    $stmt->execute();
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$member) {
        throw new Exception('Member not found', 404);
    }
    if ($member['deleted_at'] !== null) {
        throw new Exception('This account has already been closed', 409);
    }

    // An existing open request is not an error — report it back so the app can
    // show the same pending screen instead of creating duplicates.
    $stmt = $db->prepare(
        "SELECT id, status, outstanding_at_request, requested_at
           FROM tbl_account_deletions
          WHERE memberid = :member_id AND status IN ('pending', 'pending_settlement')
          ORDER BY requested_at DESC LIMIT 1"
    );
    $stmt->bindParam(':member_id', $memberId);
    $stmt->execute();
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        echo json_encode([
            'success' => true,
            'data' => [
                'status' => $existing['status'],
                'outstanding' => (float) $existing['outstanding_at_request'],
                'requested_at' => $existing['requested_at'],
                'already_requested' => true,
            ],
            'message' => MemberBalance::deletionMessage(
                $existing['status'],
                (float) $existing['outstanding_at_request']
            ),
        ]);
        exit();
    }

    $outstanding = MemberBalance::outstandingLoan($db, $memberId);
    $status = $outstanding > 0 ? 'pending_settlement' : 'pending';

    $stmt = $db->prepare(
        'INSERT INTO tbl_account_deletions
            (memberid, requested_at, status, outstanding_at_request, source, member_note)
         VALUES (:member_id, NOW(), :status, :outstanding, :source, :member_note)'
    );
    $source = 'mobile_app';
    $note = $memberNote === '' ? null : $memberNote;
    $stmt->bindParam(':member_id', $memberId);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':outstanding', $outstanding);
    $stmt->bindParam(':source', $source);
    $stmt->bindParam(':member_note', $note);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'data' => [
            'status' => $status,
            'outstanding' => $outstanding,
            'already_requested' => false,
        ],
        'message' => MemberBalance::deletionMessage($status, $outstanding),
    ]);
} catch (Exception $e) {
    error_log('request_deletion: ' . $e->getMessage());
    MobileAuth::fail($e);
}
