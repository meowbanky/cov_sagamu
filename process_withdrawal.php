<?php
session_start();
header('Content-Type: application/json');
require_once('Connections/cov.php');

// Helper: respond and exit
function respond($arr) {
    echo json_encode($arr);
    exit;
}

// Validate input
$periodId = isset($_POST['PeriodId']) ? intval($_POST['PeriodId']) : 0;
$memberId = isset($_POST['txtCoopid']) ? intval($_POST['txtCoopid']) : 0;
$amount = isset($_POST['Amount']) ? floatval(str_replace(',', '', $_POST['Amount'])) : 0;
$userId = isset($_SESSION['UserID']) ? intval($_SESSION['UserID']) : 0;

if (!$periodId || !$memberId || !$amount || $amount <= 0) {
    respond(['error' => 'Invalid input.']);
}
if (!$userId) {
    respond(['error' => 'Session expired. Please log in again.']);
}

// Get total available (savings + shares)
$query = "SELECT SUM(savings) AS total_savings, SUM(shares) AS total_shares FROM tlb_mastertransaction WHERE memberid = ?";
$stmt = $cov->prepare($query);
$stmt->bind_param('i', $memberId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_savings = isset($row['total_savings']) ? floatval($row['total_savings']) : 0.00;
$total_shares = isset($row['total_shares']) ? floatval($row['total_shares']) : 0.00;
$available = $total_savings + $total_shares;
$stmt->close();

if ($amount > $available) {
    respond(['error' => 'Withdrawal amount exceeds available contribution.']);
}

// Calculate withdrawal split
$savings_withdraw = -($amount * 0.6);
$shares_withdraw = -($amount * 0.4);

// Insert withdrawal as negative savings and shares in one row
$insert = $cov->prepare("INSERT INTO tlb_mastertransaction (memberid, periodid, savings, shares, DateOfPayment, withdrawal) VALUES (?, ?, ?, ?, NOW(), ?)");
$insert->bind_param('iiddd', $memberId, $periodId, $savings_withdraw, $shares_withdraw, $amount);
$ok = $insert->execute();
if ($ok) {
    respond(['success' => 'Withdrawal successful.']);
} else {
    respond(['error' => 'Database error: ' . $insert->error]);
}
$insert->close(); 