<?php
// api/auth/update_fcm_token.php
//
// Stores the calling device's Firebase Cloud Messaging registration token
// against the authenticated member. Replaces the OneSignal player-id flow.

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../utils/MobileAuth.php';

MobileAuth::applyCors('POST, OPTIONS');

try {
    $memberId = MobileAuth::requireMemberId();
    $body = MobileAuth::jsonBody();

    $fcmToken = isset($body['fcm_token']) ? trim($body['fcm_token']) : '';

    if ($fcmToken === '') {
        throw new Exception('fcm_token is required', 400);
    }

    // FCM tokens are long opaque strings; reject anything that cannot be one
    // rather than writing junk into the members table.
    if (strlen($fcmToken) > 512 || !preg_match('/^[A-Za-z0-9_:\-]+$/', $fcmToken)) {
        throw new Exception('fcm_token is malformed', 400);
    }

    $database = new Database();
    $db = $database->getConnection();

    $stmt = $db->prepare(
        'UPDATE tbl_personalinfo SET fcm_token = :fcm_token WHERE memberid = :member_id'
    );
    $stmt->bindParam(':fcm_token', $fcmToken);
    $stmt->bindParam(':member_id', $memberId);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Push token registered',
    ]);
} catch (Exception $e) {
    error_log('update_fcm_token: ' . $e->getMessage());
    MobileAuth::fail($e);
}
