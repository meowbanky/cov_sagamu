<?php
// api/account/delete_account.php
//
// In-app account deletion, required by App Store Guideline 5.1.1(v).
//
// Cooperative financial records (contributions, loans, dividends) must be
// retained for statutory audit, so this is a soft delete: the member's
// personal details are anonymised and their access is revoked immediately,
// while the financial ledger keeps referring to the member id. The in-app
// confirmation screen states this in plain language before the member
// confirms.

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../utils/MobileAuth.php';

MobileAuth::applyCors('POST, OPTIONS');

try {
    // Identity comes from the token only — never from the request body.
    $memberId = MobileAuth::requireMemberId();
    $body = MobileAuth::jsonBody();

    $password = isset($body['password']) ? (string) $body['password'] : '';
    if ($password === '') {
        throw new Exception('Password confirmation is required', 400);
    }

    $database = new Database();
    $db = $database->getConnection();

    // Re-authenticate: a stolen handset with an unlocked app must not be able
    // to wipe the member's identity.
    $stmt = $db->prepare('SELECT UPassword FROM tblusers WHERE UserID = :member_id');
    $stmt->bindParam(':member_id', $memberId);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['UPassword'])) {
        throw new Exception('Password is incorrect', 401);
    }

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

    $db->beginTransaction();

    try {
        // Audit row first, so the request is recorded even if it is the only
        // trace left after anonymisation.
        $stmt = $db->prepare(
            'INSERT INTO tbl_account_deletions (memberid, requested_at, source, notes)
             VALUES (:member_id, NOW(), :source, :notes)'
        );
        $source = 'mobile_app';
        $notes = 'Member-initiated deletion from iOS/Android app';
        $stmt->bindParam(':member_id', $memberId);
        $stmt->bindParam(':source', $source);
        $stmt->bindParam(':notes', $notes);
        $stmt->execute();

        // Anonymise personal details, revoke access, drop the push token.
        $stmt = $db->prepare(
            'UPDATE tbl_personalinfo
                SET Fname         = :redacted_first,
                    Lname         = :redacted_last,
                    EmailAddress  = NULL,
                    MobilePhone   = NULL,
                    Address       = NULL,
                    City          = NULL,
                    State         = NULL,
                    Picture       = NULL,
                    fcm_token     = NULL,
                    deleted_at    = NOW(),
                    deletion_requested_by = :requested_by
              WHERE memberid = :member_id'
        );
        $redactedFirst = 'Deleted';
        $redactedLast = 'Member';
        $requestedBy = 'member';
        $stmt->bindParam(':redacted_first', $redactedFirst);
        $stmt->bindParam(':redacted_last', $redactedLast);
        $stmt->bindParam(':requested_by', $requestedBy);
        $stmt->bindParam(':member_id', $memberId);
        $stmt->execute();

        // Next of kin details are pure PII with no audit value.
        $stmt = $db->prepare('DELETE FROM tbl_nok WHERE memberid = :member_id');
        $stmt->bindParam(':member_id', $memberId);
        $stmt->execute();

        // Scramble the credential so the login cannot be replayed.
        $stmt = $db->prepare('UPDATE tblusers SET UPassword = :dead WHERE UserID = :member_id');
        $dead = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
        $stmt->bindParam(':dead', $dead);
        $stmt->bindParam(':member_id', $memberId);
        $stmt->execute();

        $db->commit();
    } catch (Exception $inner) {
        $db->rollBack();
        throw $inner;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Your account has been closed.',
    ]);
} catch (Exception $e) {
    error_log('delete_account: ' . $e->getMessage());
    MobileAuth::fail($e);
}
