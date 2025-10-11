<?php
header('Content-Type: application/json');
require_once('../Connections/cov.php');

mysqli_select_db($cov, $database_cov);

$memberid = intval($_POST['memberid'] ?? 0);

if ($memberid <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid member ID']);
    exit;
}

// Check if member has special savings
$query = "SELECT * FROM tbl_special_savings WHERE memberid = ? AND status = 'active'";
$stmt = mysqli_prepare($cov, $query);
mysqli_stmt_bind_param($stmt, "i", $memberid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode([
        'success' => true,
        'hasSpecialSavings' => true,
        'data' => [
            'id' => $row['id'],
            'memberid' => $row['memberid'],
            'special_savings_amount' => $row['special_savings_amount'],
            'notes' => $row['notes'],
            'date_added' => $row['date_added']
        ]
    ]);
} else {
    echo json_encode([
        'success' => true,
        'hasSpecialSavings' => false,
        'data' => null
    ]);
}

mysqli_stmt_close($stmt);
?>
