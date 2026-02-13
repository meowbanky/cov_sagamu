<?php
require_once('../Connections/cov.php');
mysqli_select_db($cov, $database_cov);
header('Content-Type: application/json');

$memberid = $_POST['txtCoopid'] ?? '';
$periodid = $_POST['PeriodId'] ?? '';
$amount = floatval($_POST['Amount'] ?? 0);

if (!$memberid || !$periodid || !$amount) {
    echo json_encode(['error' => 'All fields required (Member, Period, Amount).']);
    exit;
}

$stmt = $cov->prepare("INSERT INTO tbl_specialcontributions (membersid, periodid, contribution) VALUES (?, ?, ?)");
$stmt->bind_param("sid", $memberid, $periodid, $amount);

if ($stmt->execute()) {
    echo json_encode(['success' => 'Special repayment saved successfully.']);
} else {
    echo json_encode(['error' => 'Error saving repayment: ' . $stmt->error]);
}

$stmt->close();
