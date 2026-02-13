<?php
require_once('../Connections/cov.php');
mysqli_select_db($cov, $database_cov);
header('Content-Type: application/json');

$id = $_POST['txtContriId'] ?? '';
$amount = floatval($_POST['Amount'] ?? 0);

if (!$id || !$amount) {
    echo json_encode(['error' => 'ID and Amount are required.']);
    exit;
}

$stmt = $cov->prepare("UPDATE tbl_specialcontributions SET contribution = ? WHERE id = ?");
$stmt->bind_param("di", $amount, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => 'Special repayment updated successfully.']);
} else {
    echo json_encode(['error' => 'Error updating repayment: ' . $stmt->error]);
}

$stmt->close();
