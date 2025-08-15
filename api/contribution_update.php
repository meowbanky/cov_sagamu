<?php
require_once('../Connections/cov.php');
header('Content-Type: application/json');
mysqli_select_db($cov, $database_cov);
$memberid = $_POST['txtCoopid'] ?? '';
$periodid = $_POST['PeriodId'] ?? '';
$amount = floatval($_POST['Amount'] ?? 0);
$special = floatval($_POST['specialsavings'] ?? 0);
$contriId = intval($_POST['txtContriId'] ?? 0);
if (!$memberid || !$periodid || !$amount) {
    echo json_encode(['error'=>'All fields required.']); exit;
}
$stmt = $cov->prepare("UPDATE tbl_contributions SET contribution=?, special_savings=? WHERE contriId=?");
$stmt->bind_param("ddi", $amount, $special, $contriId);
if ($stmt->execute()) {
    echo json_encode(['success'=>'Contribution updated.']);
} else {
    echo json_encode(['error'=>'Error: '.$stmt->error]);
}
$stmt->close();