<?php
require_once('Connections/cov.php');
header('Content-Type: application/json');

if (!isset($_SESSION['UserID'])) {
    // You might want to enforce session check here, 
    // but looking at loanBalance.php it didn't seem to enforce it strictly or it was minimal.
    // We'll add it for security if sessions are available.
    session_start();
}

$memberId = -1;
if (isset($_GET['id'])) {
    $memberId = intval($_GET['id']);
}

if ($memberId <= 0) {
    echo json_encode(['balance' => 0, 'interestBalance' => 0, 'error' => 'Invalid Member ID']);
    exit;
}

mysqli_select_db($cov, $database_cov);

// Logic copied from loanBalance.php but cleaner
$query = sprintf("SELECT 
    ((sum(ifnull(loanAmount,0))) - (sum(ifnull(loanRepayment,0)))) as balance,
    ((sum(ifnull(interestCal,0))) - (sum(ifnull(interestPaid,0)))) as interestBalance 
    FROM tlb_mastertransaction 
    WHERE memberid = %d", $memberId);

$result = mysqli_query($cov, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $balance = $row['balance'] ?? 0;
    $interestBalance = $row['interestBalance'] ?? 0;
    
    echo json_encode([
        'balance' => floatval($balance),
        'interestBalance' => floatval($interestBalance)
    ]);
} else {
    echo json_encode(['balance' => 0, 'interestBalance' => 0, 'error' => mysqli_error($cov)]);
}
?>
