<?php
// cov_update/searchStaff.php
//
// Autocomplete source for the member self-update portal.
//
// SECURITY HISTORY: this endpoint was unauthenticated, SQL-injectable
// ($_GET['term'] concatenated into the query), and returned every member's
// phone, email and BANK ACCOUNT NUMBER. An empty term dumped the whole
// register (~380 records) to anyone.
//
// It still cannot require a login (the portal has none), so it is constrained
// instead: parameterized, prefix-matched, a minimum term length, a small cap,
// rate limited, and it returns ONLY the member id and name — never contact or
// bank details. Those are keyed by member id at update time behind an OTP.

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../auth_api/utils/RateLimiter.php';

if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();
}

header('Content-Type: application/json; charset=UTF-8');
ini_set('display_errors', '0');

const MIN_TERM_LENGTH = 3;
const MAX_TERM_LENGTH = 60;
const MAX_RESULTS = 8;
const RL_HITS = 30;
const RL_WINDOW = 600;

try {
    $term = isset($_GET['term']) ? trim($_GET['term']) : '';

    if (mb_strlen($term) < MIN_TERM_LENGTH || mb_strlen($term) > MAX_TERM_LENGTH) {
        echo json_encode([]);
        exit();
    }

    $db = new PDO(
        'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'],
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD']
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    RateLimiter::enforce($db, 'cov_update_search', RL_HITS, RL_WINDOW);

    // Escape LIKE wildcards so "%" cannot match everything.
    $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term);
    $prefix = $escaped . '%';

    $sql = "SELECT memberid, Fname, ifnull(Mname,'') AS Mname, Lname
              FROM tbl_personalinfo
             WHERE deleted_at IS NULL
               AND ( memberid LIKE :p1 ESCAPE '\\\\'
                  OR Fname    LIKE :p2 ESCAPE '\\\\'
                  OR Lname    LIKE :p3 ESCAPE '\\\\'
                  OR CONCAT(Fname,' ',Lname) LIKE :p4 ESCAPE '\\\\' )
             ORDER BY memberid ASC
             LIMIT " . MAX_RESULTS;

    $stmt = $db->prepare($sql);
    foreach (['p1', 'p2', 'p3', 'p4'] as $ph) {
        $stmt->bindValue(':' . $ph, $prefix);
    }
    $stmt->execute();

    $out = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $name = trim($row['Fname'] . ' ' . $row['Mname'] . ' ' . $row['Lname']);
        $out[] = [
            // Identity + name only. Names are inherently exposed by a
            // search-by-name step and are needed to prefill the form. Phone,
            // email, account number and bank are deliberately withheld until
            // the member proves ownership with an OTP.
            'id' => $row['memberid'],
            'value' => $row['memberid'],
            'label' => $name,
            'fname' => $row['Fname'],
            'mname' => $row['Mname'],
            'lname' => $row['Lname'],
        ];
    }

    echo json_encode($out);
} catch (Exception $e) {
    error_log('cov_update/searchStaff: ' . $e->getMessage());
    echo json_encode([]);
}
