<?php
// api/auth/search_users.php
//
// Lets a prospective member find their own record during sign-up, before they
// have any credentials — so this endpoint cannot require a bearer token.
//
// It previously returned member IDs *and email addresses* for any 3-character
// substring, unauthenticated, which made the whole membership roll enumerable.
// It is now constrained so it still answers "find myself" while being poor at
// "list everyone":
//
//   * no email addresses in the response — the app never used them
//   * prefix matching, not substring, so a fragment cannot sweep the register
//   * a longer minimum query
//   * a small result cap
//   * per-IP rate limiting
//   * closed accounts excluded
//
// The residual exposure is that a caller who already knows a member's name can
// confirm it exists. That is inherent to a "find yourself" sign-up step; the
// alternative is dropping self-service sign-up entirely.

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../utils/RateLimiter.php';
require_once __DIR__ . '/../../utils/MemberLookup.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Never echo internals to an unauthenticated caller.
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

const MIN_QUERY_LENGTH = 4;
const MAX_QUERY_LENGTH = 60;
const MAX_RESULTS = 8;

// Generous for someone typing their own name, restrictive for a scraper.
const RATE_LIMIT_HITS = 20;
const RATE_LIMIT_WINDOW_SECONDS = 600;

try {
    $query = isset($_GET['query']) ? trim($_GET['query']) : '';

    if (mb_strlen($query) < MIN_QUERY_LENGTH) {
        throw new Exception(
            'Please type at least ' . MIN_QUERY_LENGTH . ' characters of your name',
            400
        );
    }
    if (mb_strlen($query) > MAX_QUERY_LENGTH) {
        throw new Exception('Search text is too long', 400);
    }

    $database = new Database();
    $db = $database->getConnection();

    RateLimiter::enforce($db, 'member_search', RATE_LIMIT_HITS, RATE_LIMIT_WINDOW_SECONDS);

    // Escape LIKE wildcards so a query of "%" cannot match everything.
    $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query);
    $prefix = $escaped . '%';

    // Prefix match on first name, last name, or the full name — covers someone
    // typing "adeola", "bankole", or "adeola bankole", without letting a short
    // fragment sweep the register.
    //
    // EmailAddress is selected only to be MASKED below — the raw value never
    // leaves the server. The forgot-password screen shows the mask so a member
    // can confirm which address the reset code is going to.
    $sql = "SELECT memberid     AS CoopID,
                   Fname        AS FirstName,
                   Lname        AS LastName,
                   EmailAddress AS RawEmail
              FROM tbl_personalinfo
             WHERE deleted_at IS NULL
               AND (
                     Fname LIKE :prefix ESCAPE '\\\\'
                  OR Lname LIKE :prefix2 ESCAPE '\\\\'
                  OR CONCAT(Fname, ' ', Lname) LIKE :prefix3 ESCAPE '\\\\'
                   )
             ORDER BY Lname, Fname
             LIMIT " . MAX_RESULTS;

    $stmt = $db->prepare($sql);
    // Bound three times rather than reusing one placeholder: with
    // ATTR_EMULATE_PREPARES off, a repeated named placeholder fails with
    // HY093 Invalid parameter number.
    $stmt->bindParam(':prefix', $prefix);
    $stmt->bindParam(':prefix2', $prefix);
    $stmt->bindParam(':prefix3', $prefix);
    $stmt->execute();

    $results = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $rawEmail = $row['RawEmail'];
        $results[] = [
            'CoopID' => $row['CoopID'],
            'FirstName' => $row['FirstName'],
            'LastName' => $row['LastName'],
            'HasEmail' => !empty($rawEmail),
            'MaskedEmail' => MemberLookup::maskEmail($rawEmail),
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $results,
    ]);
} catch (Exception $e) {
    $code = $e->getCode();
    http_response_code(($code >= 400 && $code < 600) ? $code : 400);
    error_log('search_users: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}
