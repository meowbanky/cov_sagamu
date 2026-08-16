<?php
// utils/FcmSender.php
//
// Sends push notifications via the Firebase Cloud Messaging HTTP v1 API.
//
// Replaces the OneSignal REST call. The legacy FCM server key is gone (Google
// retired it in 2024), so v1 requires an OAuth2 access token minted from a
// service account. We do that with firebase/php-jwt, which is already vendored
// — no new Composer dependency.
//
// Setup:
//   1. Firebase console -> Project settings -> Service accounts
//      -> Generate new private key  (downloads a JSON file)
//   2. Put it OUTSIDE the web root, e.g. /home/emmaggic/firebase-service-account.json
//   3. Add to .env:
//        FIREBASE_PROJECT_ID=cov-sagamu
//        FIREBASE_SERVICE_ACCOUNT=/home/emmaggic/firebase-service-account.json

require_once __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;

class FcmSender
{
    const OAUTH_TOKEN_URL = 'https://oauth2.googleapis.com/token';
    const FCM_SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';
    const TIMEOUT_SECONDS = 20;

    /** Access tokens last an hour; refresh a minute early to avoid races. */
    const TOKEN_TTL_SECONDS = 3600;
    const TOKEN_SKEW_SECONDS = 60;

    private $projectId;
    private $serviceAccount;

    /** @var array{token:string, expires:int}|null */
    private static $cachedToken = null;

    public function __construct()
    {
        if (empty($_ENV['FIREBASE_PROJECT_ID'])) {
            throw new Exception('FIREBASE_PROJECT_ID is not set');
        }
        if (empty($_ENV['FIREBASE_SERVICE_ACCOUNT'])) {
            throw new Exception('FIREBASE_SERVICE_ACCOUNT is not set');
        }

        $path = $_ENV['FIREBASE_SERVICE_ACCOUNT'];
        if (!is_readable($path)) {
            throw new Exception('Firebase service account file is not readable');
        }

        $json = json_decode(file_get_contents($path), true);
        if (!is_array($json) || empty($json['client_email']) || empty($json['private_key'])) {
            throw new Exception('Firebase service account file is malformed');
        }

        $this->projectId = $_ENV['FIREBASE_PROJECT_ID'];
        $this->serviceAccount = $json;
    }

    /**
     * Sends a notification to one device token.
     *
     * @return array{ok:bool, retryable:bool, invalidToken:bool, message:string}
     */
    public function sendToToken($fcmToken, $title, $body, array $data = [])
    {
        if (empty($fcmToken)) {
            return self::result(false, false, false, 'No FCM token');
        }

        // FCM v1 requires all data values to be strings.
        $stringData = [];
        foreach ($data as $key => $value) {
            $stringData[(string) $key] = (string) $value;
        }

        $payload = [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $stringData,
                'android' => [
                    'priority' => 'high',
                    'notification' => ['sound' => 'default'],
                ],
                'apns' => [
                    'headers' => ['apns-priority' => '10'],
                    'payload' => ['aps' => ['sound' => 'default', 'badge' => 1]],
                ],
            ],
        ];

        return $this->post($payload);
    }

    private function post(array $payload)
    {
        $url = 'https://fcm.googleapis.com/v1/projects/' . $this->projectId . '/messages:send';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => self::TIMEOUT_SECONDS,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken(),
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error !== '') {
            return self::result(false, true, false, 'FCM transport error: ' . $error);
        }

        if ($httpCode === 200) {
            return self::result(true, false, false, 'sent');
        }

        $decoded = json_decode($response, true);
        $status = isset($decoded['error']['status']) ? $decoded['error']['status'] : 'UNKNOWN';

        // UNREGISTERED / INVALID_ARGUMENT mean the token is dead — the caller
        // should clear it rather than retrying forever.
        $invalidToken = in_array($status, ['UNREGISTERED', 'NOT_FOUND', 'INVALID_ARGUMENT'], true);
        $retryable = in_array($httpCode, [429, 500, 502, 503, 504], true);

        error_log('FCM send failed (HTTP ' . $httpCode . ', ' . $status . '): ' . substr((string) $response, 0, 300));

        return self::result(false, $retryable, $invalidToken, $status);
    }

    /**
     * OAuth2 access token for the service account, cached for the request's
     * lifetime (and across calls within one PHP process).
     */
    private function accessToken()
    {
        if (self::$cachedToken !== null && self::$cachedToken['expires'] > time()) {
            return self::$cachedToken['token'];
        }

        $now = time();
        $assertion = JWT::encode([
            'iss' => $this->serviceAccount['client_email'],
            'scope' => self::FCM_SCOPE,
            'aud' => self::OAUTH_TOKEN_URL,
            'iat' => $now,
            'exp' => $now + self::TOKEN_TTL_SECONDS,
        ], $this->serviceAccount['private_key'], 'RS256');

        $ch = curl_init(self::OAUTH_TOKEN_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => self::TIMEOUT_SECONDS,
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $assertion,
            ]),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($response, true);
        if ($httpCode !== 200 || empty($decoded['access_token'])) {
            error_log('FCM token exchange failed (HTTP ' . $httpCode . '): ' . substr((string) $response, 0, 300));
            throw new Exception('Could not authenticate with Firebase');
        }

        self::$cachedToken = [
            'token' => $decoded['access_token'],
            'expires' => $now + self::TOKEN_TTL_SECONDS - self::TOKEN_SKEW_SECONDS,
        ];

        return self::$cachedToken['token'];
    }

    private static function result($ok, $retryable, $invalidToken, $message)
    {
        return [
            'ok' => $ok,
            'retryable' => $retryable,
            'invalidToken' => $invalidToken,
            'message' => $message,
        ];
    }
}
