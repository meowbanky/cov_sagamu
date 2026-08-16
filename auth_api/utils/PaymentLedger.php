<?php
// utils/PaymentLedger.php
//
// Settles a Paystack transaction against tbl_payments.
//
// Both the webhook and the client-triggered verify call land here, and either
// may arrive first (or twice), so this must be idempotent: settling an already
// settled reference is a no-op, not a double credit.

class PaymentLedger
{
    /**
     * Applies a verified Paystack result to the stored payment.
     *
     * @param array $paystackData the `data` object from verify/webhook
     * @return array{status:string, changed:bool, amount:float}
     * @throws Exception
     */
    public static function settle(PDO $db, $reference, array $paystackData)
    {
        $stmt = $db->prepare(
            'SELECT id, memberid, amount_kobo, purpose, status
               FROM tbl_payments WHERE reference = :reference'
        );
        $stmt->bindParam(':reference', $reference);
        $stmt->execute();
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$payment) {
            throw new Exception('Unknown payment reference', 404);
        }

        $paystackStatus = isset($paystackData['status']) ? (string) $paystackData['status'] : 'unknown';

        // Already settled — report the stored outcome, change nothing.
        if (in_array($payment['status'], ['paid', 'failed'], true)) {
            return [
                'status' => $payment['status'],
                'changed' => false,
                'amount' => round(((int) $payment['amount_kobo']) / 100, 2),
            ];
        }

        if ($paystackStatus !== 'success') {
            self::updateStatus($db, $reference, 'failed', $paystackStatus, false);
            return [
                'status' => 'failed',
                'changed' => true,
                'amount' => round(((int) $payment['amount_kobo']) / 100, 2),
            ];
        }

        // Guard against a success callback for a different amount than the one
        // we recorded — treat a mismatch as suspicious rather than crediting it.
        $paidKobo = isset($paystackData['amount']) ? (int) $paystackData['amount'] : 0;
        if ($paidKobo !== (int) $payment['amount_kobo']) {
            error_log(sprintf(
                'PaymentLedger: amount mismatch for %s — expected %d kobo, Paystack reported %d',
                $reference,
                (int) $payment['amount_kobo'],
                $paidKobo
            ));
            self::updateStatus($db, $reference, 'mismatch', $paystackStatus, false);
            throw new Exception('Payment amount did not match. Please contact the office.', 409);
        }

        self::updateStatus($db, $reference, 'paid', $paystackStatus, true);

        return [
            'status' => 'paid',
            'changed' => true,
            'amount' => round($paidKobo / 100, 2),
        ];
    }

    private static function updateStatus(PDO $db, $reference, $status, $paystackStatus, $setPaidAt)
    {
        $sql = 'UPDATE tbl_payments
                   SET status = :status,
                       paystack_status = :paystack_status,
                       updated_at = NOW()'
             . ($setPaidAt ? ', paid_at = NOW()' : '')
             . ' WHERE reference = :reference';

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':paystack_status', $paystackStatus);
        $stmt->bindParam(':reference', $reference);
        $stmt->execute();
    }
}
