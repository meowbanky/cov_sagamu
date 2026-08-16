<?php
// api/account/deletion_status.php
//
// Lets the app show the current state of a member's deletion request, so a
// member who has already asked sees "awaiting approval" rather than a fresh
// form. Returns status null when no request has ever been made.

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../utils/MobileAuth.php';
require_once __DIR__ . '/../../utils/MemberBalance.php';

MobileAuth::applyCors('GET, OPTIONS');

try {
    $memberId = MobileAuth::requireMemberId();

    $database = new Database();
    $db = $database->getConnection();

    $stmt = $db->prepare(
        'SELECT status, outstanding_at_request, requested_at, reviewed_at, review_note
           FROM tbl_account_deletions
          WHERE memberid = :member_id
          ORDER BY requested_at DESC
          LIMIT 1'
    );
    $stmt->bindParam(':member_id', $memberId);
    $stmt->execute();
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    // Always report today's balance too — a member who has since settled up
    // should see that reflected, not the figure captured at request time.
    $outstanding = MemberBalance::outstandingLoan($db, $memberId);

    if (!$request) {
        echo json_encode([
            'success' => true,
            'data' => [
                'status' => null,
                'outstanding' => $outstanding,
            ],
        ]);
        exit();
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'status' => $request['status'],
            'outstanding' => $outstanding,
            'outstanding_at_request' => (float) $request['outstanding_at_request'],
            'requested_at' => $request['requested_at'],
            'reviewed_at' => $request['reviewed_at'],
            'review_note' => $request['review_note'],
        ],
        'message' => MemberBalance::deletionMessage($request['status'], $outstanding),
    ]);
} catch (Exception $e) {
    error_log('deletion_status: ' . $e->getMessage());
    MobileAuth::fail($e);
}
