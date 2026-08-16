<?php
// api/payments/initialize.php
//
// Starts a Paystack checkout for a cooperative contribution or loan repayment.
//
// These are real-world financial services provided by the society outside the
// app, so they are exempt from Apple in-app purchase (Guideline 3.1.3(a)).
//
// The amount and reference are decided server-side; the client sends only a
// naira amount and a purpose, and gets back a URL to open.

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../utils/MobileAuth.php';
require_once __DIR__ . '/../../utils/Paystack.php';

MobileAuth::applyCors('POST, OPTIONS');

const PAYMENT_CALLBACK_URL = 'https://www.emmaggi.com/cov/payment_complete.html';

try {
    $memberId = MobileAuth::requireMemberId();
    $body = MobileAuth::jsonBody();

    $purpose = isset($body['purpose']) ? trim((string) $body['purpose']) : '';
    if (!in_array($purpose, Paystack::PURPOSES, true)) {
        throw new Exception('Please choose what you are paying towards', 400);
    }

    // Accept naira as a number or numeric string, convert to integer kobo.
    // Never trust a client-supplied kobo figure.
    $amountNaira = $body['amount'] ?? null;
    if (!is_numeric($amountNaira)) {
        throw new Exception('Please enter a valid amount', 400);
    }

    $amountKobo = (int) round(((float) $amountNaira) * 100);
    if ($amountKobo < Paystack::MIN_KOBO) {
        throw new Exception('The minimum payment is ₦100', 400);
    }
    if ($amountKobo > Paystack::MAX_KOBO) {
        throw new Exception('That amount is too large. Please contact the office.', 400);
    }

    $database = new Database();
    $db = $database->getConnection();

    // Paystack requires an email; use the member's own.
    $stmt = $db->prepare(
        'SELECT EmailAddress, Fname, Lname, deleted_at
           FROM tbl_personalinfo WHERE memberid = :member_id'
    );
    $stmt->bindParam(':member_id', $memberId);
    $stmt->execute();
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$member || $member['deleted_at'] !== null) {
        throw new Exception('Member not found', 404);
    }
    if (empty($member['EmailAddress'])) {
        throw new Exception(
            'Please add an email address to your profile before paying online',
            400
        );
    }

    $reference = Paystack::newReference($memberId);

    // Record the intent BEFORE calling Paystack, so a webhook that arrives
    // before the response lands still finds a row to update.
    $stmt = $db->prepare(
        'INSERT INTO tbl_payments
            (memberid, reference, amount_kobo, purpose, status, created_at)
         VALUES (:member_id, :reference, :amount_kobo, :purpose, :status, NOW())'
    );
    $pending = 'pending';
    $stmt->bindParam(':member_id', $memberId);
    $stmt->bindParam(':reference', $reference);
    $stmt->bindParam(':amount_kobo', $amountKobo, PDO::PARAM_INT);
    $stmt->bindParam(':purpose', $purpose);
    $stmt->bindParam(':status', $pending);
    $stmt->execute();

    $paystack = new Paystack();
    $data = $paystack->initialize(
        $member['EmailAddress'],
        $amountKobo,
        $reference,
        PAYMENT_CALLBACK_URL,
        [
            'member_id' => $memberId,
            'purpose' => $purpose,
            'member_name' => trim($member['Fname'] . ' ' . $member['Lname']),
        ]
    );

    if (empty($data['authorization_url'])) {
        throw new Exception('Payment provider did not return a checkout link', 502);
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'authorization_url' => $data['authorization_url'],
            'reference' => $reference,
            'amount' => round($amountKobo / 100, 2),
        ],
    ]);
} catch (Exception $e) {
    error_log('payments/initialize: ' . $e->getMessage());
    MobileAuth::fail($e);
}
