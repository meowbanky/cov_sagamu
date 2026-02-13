<?php
require_once('../Connections/cov.php');
mysqli_select_db($cov, $database_cov);
header('Content-Type: application/json');

$id = $_POST['contriId'] ?? '';

if (!$id) {
    echo json_encode(['error' => 'ID is required.']);
    exit;
}

$stmt = $cov->prepare("DELETE FROM tbl_specialcontributions WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => 'Special repayment deleted successfully.']);
} else {
    echo json_encode(['error' => 'Error deleting repayment: ' . $stmt->error]);
}

$stmt->close();
