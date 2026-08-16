<?php
// utils/Paystack.php
//
// Thin wrapper over the two Paystack endpoints this app needs. The secret key
// lives only in .env and never leaves the server — the app is given an
// authorization_url, never a key.

class Paystack
{
    const BASE_URL = 'https://api.paystack.co';
    const TIMEOUT_SECONDS = 20;

    /** Cooperative purposes a member may pay towards. */
    const PURPOSES = ['savings', 'shares', 'loan_repayment'];

    /** Paystack works in kobo; refuse anything below ₦100 or above ₦5,000,000. */
    const MIN_KOBO = 10000;
    const MAX_KOBO = 500000000;

    private $secretKey;

    public function __construct()
    {
        if (empty($_ENV['PAYSTACK_SECRET_KEY'])) {
            throw new Exception('Payments are not configured', 503);
        }
        $this->secretKey = $_ENV['PAYSTACK_SECRET_KEY'];
    }

    /**
     * Server-generated, unguessable reference. Never accept one from the client
     * — it is the key the webhook and verify both trust.
     */
    public static function newReference($memberId)
    {
        return 'cov_' . preg_replace('/[^A-Za-z0-9]/', '', (string) $memberId)
            . '_' . bin2hex(random_bytes(8));
    }

    public function initialize($email, $amountKobo, $reference, $callbackUrl, array $metadata = [])
    {
        return $this->request('POST', '/transaction/initialize', [
            'email' => $email,
            'amount' => $amountKobo,
            'reference' => $reference,
            'callback_url' => $callbackUrl,
            'metadata' => $metadata,
        ]);
    }

    public function verify($reference)
    {
        return $this->request('GET', '/transaction/verify/' . rawurlencode($reference));
    }

    /**
     * Constant-time check of the x-paystack-signature header.
     */
    public function isValidWebhookSignature($rawBody, $signature)
    {
        if (!$signature) {
            return false;
        }
        $expected = hash_hmac('sha512', $rawBody, $this->secretKey);
        return hash_equals($expected, $signature);
    }

    private function request($method, $path, ?array $payload = null)
    {
        $ch = curl_init(self::BASE_URL . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => self::TIMEOUT_SECONDS,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->secretKey,
                'Content-Type: application/json',
                'Cache-Control: no-cache',
            ],
        ]);

        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $body = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // curl_close() is a no-op since PHP 8.0 and deprecated in 8.5; a stray
        // deprecation notice would corrupt these JSON responses.

        if ($error !== '') {
            error_log('Paystack transport error: ' . $error);
            throw new Exception('Could not reach the payment provider', 502);
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            error_log('Paystack non-JSON response (HTTP ' . $httpCode . '): ' . substr((string) $body, 0, 300));
            throw new Exception('Unexpected response from the payment provider', 502);
        }

        if ($httpCode >= 400 || empty($decoded['status'])) {
            $message = isset($decoded['message']) ? $decoded['message'] : 'Payment provider rejected the request';
            error_log('Paystack error (HTTP ' . $httpCode . '): ' . $message);
            throw new Exception($message, 502);
        }

        return isset($decoded['data']) ? $decoded['data'] : [];
    }
}
