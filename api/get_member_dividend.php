<?php
require_once('../Connections/cov.php');
mysqli_select_db($cov, $database_cov);
header('Content-Type: application/json');

$memberId = $_GET['memberId'] ?? 0;
$period = intval($_GET['period'] ?? 0);
$percentage = floatval($_GET['percentage'] ?? 0);

if (!$memberId || !$period || !$percentage) {
    echo json_encode(['error' => 'Invalid parameters.']);
    exit;
}

// Calculate Dividend for Single Member
$query = "
    SELECT 
        (IFNULL(SUM(shares), 0) + IFNULL(SUM(savings), 0)) * ? AS dividend
    FROM tlb_mastertransaction
    WHERE memberid = ? AND periodid <= ?
";

$stmt = $cov->prepare($query);
//$stmt->bind_param("dis", $percentage, $memberId, $period); // IDs can be int or string? usually string/int mixed in this DB. 
// Let's check getDividend.php... it used direct query.
// In get_member_dividend.php I used binding. memberid is likely int or string.
// Let's assume 's' for memberid to be safe as it was alphanumeric in some tables or just int.
// Looking at dividend_preview.php, I used string logic '{$row['memberid']}'.
// Let's use 's'.

$stmt->bind_param("dsi", $percentage, $memberId, $period);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$dividend = floatval($row['dividend'] ?? 0);

echo json_encode([
    'success' => true,
    'dividend' => $dividend
]);
?>
