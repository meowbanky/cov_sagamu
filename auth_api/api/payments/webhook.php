<?php
// api/payments/webhook.php
//
// Paystack server-to-server callback. This is the authoritative settlement
// path — the member's device may never return to the app, but this still fires.
//
// No bearer token here: Paystack authenticates itself with an HMAC-SHA512
// signature over the raw body, keyed with our secret. Anything that fails that
// check is discarded without touching the database.
//
// Configure in the Paystack dashboard under Settings → API Keys & Webhooks:
//   https://www.emmaggi.com/cov/auth_api/api/payments/webhook.php

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../utils/Paystack.php';
require_once __DIR__ . '/../../utils/PaymentLedger.php';

header('Content-Type: application/json; charset=UTF-8');

// Paystack retries on any non-2xx, so respond 200 for anything we have
// deliberately decided not to act on, and non-2xx only for genuine failures we
// want retried.
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false]);
        exit();
    }

    $rawBody = file_get_contents('php://input');
    $signature = isset($_SERVER['HTTP_X_PAYSTACK_SIGNATURE'])
        ? $_SERVER['HTTP_X_PAYSTACK_SIGNATURE']
        : '';

    $paystack = new Paystack();
    if (!$paystack->isValidWebhookSignature($rawBody, $signature)) {
        error_log('payments/webhook: invalid signature, discarded');
        http_response_code(401);
        echo json_encode(['success' => false]);
        exit();
    }

    $event = json_decode($rawBody, true);
    if (!is_array($event) || empty($event['event'])) {
        http_response_code(400);
        echo json_encode(['success' => false]);
        exit();
    }

    // Only charge outcomes affect the ledger; acknowledge everything else so
    // Paystack stops retrying it.
    if ($event['event'] !== 'charge.success') {
        echo json_encode(['success' => true, 'ignored' => $event['event']]);
        exit();
    }

    $data = isset($event['data']) && is_array($event['data']) ? $event['data'] : [];
    $reference = isset($data['reference']) ? (string) $data['reference'] : '';

    if ($reference === '') {
        http_response_code(400);
        echo json_encode(['success' => false]);
        exit();
    }

    $database = new Database();
    $db = $database->getConnection();

    // Re-verify against Paystack rather than trusting the posted amount, even
    // though the signature checked out. Cheap, and removes a whole class of
    // replay concerns.
    $verified = $paystack->verify($reference);
    $result = PaymentLedger::settle($db, $reference, $verified);

    error_log(sprintf(
        'payments/webhook: %s -> %s (changed: %s)',
        $reference,
        $result['status'],
        $result['changed'] ? 'yes' : 'no'
    ));

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log('payments/webhook: ' . $e->getMessage());
    // 500 so Paystack retries — the ledger is idempotent, so a retry is safe.
    http_response_code(500);
    echo json_encode(['success' => false]);
}
