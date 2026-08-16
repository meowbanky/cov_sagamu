<?php
// api/payments/verify.php
//
// Called by the app when the member returns from the Paystack checkout.
// The webhook is the authoritative path; this exists so the member gets an
// immediate answer instead of waiting for Paystack to call us.

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../utils/MobileAuth.php';
require_once __DIR__ . '/../../utils/Paystack.php';
require_once __DIR__ . '/../../utils/PaymentLedger.php';

MobileAuth::applyCors('POST, OPTIONS');

try {
    $memberId = MobileAuth::requireMemberId();
    $body = MobileAuth::jsonBody();

    $reference = isset($body['reference']) ? trim((string) $body['reference']) : '';
    if ($reference === '') {
        throw new Exception('reference is required', 400);
    }

    $database = new Database();
    $db = $database->getConnection();

    // A member may only verify their own payment.
    $stmt = $db->prepare('SELECT memberid FROM tbl_payments WHERE reference = :reference');
    $stmt->bindParam(':reference', $reference);
    $stmt->execute();
    $owner = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$owner) {
        throw new Exception('Unknown payment reference', 404);
    }
    if ((string) $owner['memberid'] !== (string) $memberId) {
        throw new Exception('Unknown payment reference', 404);
    }

    $paystack = new Paystack();
    $result = PaymentLedger::settle($db, $reference, $paystack->verify($reference));

    echo json_encode([
        'success' => true,
        'data' => [
            'reference' => $reference,
            'status' => $result['status'],
            'amount' => $result['amount'],
        ],
        'message' => $result['status'] === 'paid'
            ? 'Payment received. Thank you.'
            : 'This payment was not completed.',
    ]);
} catch (Exception $e) {
    error_log('payments/verify: ' . $e->getMessage());
    MobileAuth::fail($e);
}
