<?php
require_once('../Connections/cov.php');
mysqli_select_db($cov, $database_cov);
header('Content-Type: application/json');
$memberid = $_POST['txtCoopid'] ?? '';
$periodid = $_POST['PeriodId'] ?? '';
$amount = floatval($_POST['Amount'] ?? 0);
$special = floatval($_POST['specialsavings'] ?? 0);
if (!$memberid || !$periodid || !$amount) {
    echo json_encode(['error'=>'All fields required.']); exit;
}
$stmt = $cov->prepare("INSERT INTO tbl_contributions (membersid, periodid, contribution, special_savings) VALUES (?,?,?,?)");
$stmt->bind_param("iidd", $memberid, $periodid, $amount, $special);
if ($stmt->execute()) {
    echo json_encode(['success'=>'Contribution saved.']);
} else {
    echo json_encode(['error'=>'Error: '.$stmt->error]);
}
$stmt->close();
