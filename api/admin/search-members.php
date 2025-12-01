<?php
/**
 * Search Members API
 * Used for manual check-in member selection
 * Searches members by name or memberid
 */

session_start();
require_once('../../Connections/cov.php');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Check authentication
if (!isset($_SESSION['UserID'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

mysqli_select_db($cov, $database_cov);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
    
    $searchTerm = trim($_GET['q'] ?? '');
    
    if (strlen($searchTerm) < 2) {
        echo json_encode([
            'success' => true,
            'data' => []
        ]);
        exit;
    }
    
    $searchPattern = '%' . $searchTerm . '%';
    
    $query = "SELECT 
        memberid,
        Lname,
        Fname,
        Mname,
        CONCAT(IFNULL(Lname, ''), ', ', IFNULL(Fname, ''), ' ', IFNULL(Mname, '')) as full_name,
        EmailAddress
    FROM tbl_personalinfo
    WHERE (memberid LIKE ? 
        OR Lname LIKE ? 
        OR Fname LIKE ? 
        OR Mname LIKE ?
        OR CONCAT(IFNULL(Lname, ''), ', ', IFNULL(Fname, ''), ' ', IFNULL(Mname, '')) LIKE ?)
    ORDER BY Lname, Fname
    LIMIT 20";
    
    $stmt = mysqli_prepare($cov, $query);
    mysqli_stmt_bind_param($stmt, "sssss", 
        $searchPattern, $searchPattern, $searchPattern, $searchPattern, $searchPattern);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $members = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $members[] = [
            'memberid' => intval($row['memberid']),
            'coop_id' => intval($row['memberid']), // For compatibility
            'full_name' => trim($row['full_name']),
            'lname' => $row['Lname'],
            'fname' => $row['Fname'],
            'mname' => $row['Mname'],
            'email' => $row['EmailAddress']
        ];
    }
    mysqli_stmt_close($stmt);
    
    echo json_encode([
        'success' => true,
        'data' => $members
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

