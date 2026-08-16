<?php
// utils/MobileAuth.php
//
// Shared bearer-token authentication for the mobile API.
//
// The identity ALWAYS comes from the signed token, never from the request
// body or query string. Endpoints that take a member id from the request are
// trivially abusable by changing one number.

require_once __DIR__ . '/JWTHandler.php';

class MobileAuth
{
    /**
     * Emits the standard CORS headers and short-circuits preflight requests.
     */
    public static function applyCors($methods = 'POST, OPTIONS')
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: ' . $methods);
        header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400');
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    /**
     * Returns the authenticated member's coop id, or throws with a 401.
     *
     * @throws Exception
     */
    public static function requireMemberId()
    {
        $header = self::authorizationHeader();

        if (!$header || !preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            throw new Exception('Authentication required', 401);
        }

        $jwt = new JWTHandler();
        $payload = $jwt->validateToken($matches[1]);

        if (!$payload || empty($payload['user_id'])) {
            throw new Exception('Invalid or expired session', 401);
        }

        return (string) $payload['user_id'];
    }

    /**
     * apache_request_headers() is unavailable under php-fpm/CGI, so fall back
     * to reconstructing the header from $_SERVER.
     */
    private static function authorizationHeader()
    {
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            foreach ($headers as $name => $value) {
                if (strcasecmp($name, 'Authorization') === 0) {
                    return $value;
                }
            }
        }

        foreach (['HTTP_AUTHORIZATION', 'REDIRECT_HTTP_AUTHORIZATION'] as $key) {
            if (!empty($_SERVER[$key])) {
                return $_SERVER[$key];
            }
        }

        return '';
    }

    /**
     * Decodes a JSON request body into an associative array.
     *
     * @throws Exception
     */
    public static function jsonBody()
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            throw new Exception('Request body must be a JSON object', 400);
        }

        return $data;
    }

    public static function fail(Exception $e)
    {
        $code = $e->getCode();
        http_response_code(($code >= 400 && $code < 600) ? $code : 500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
        ]);
    }
}
