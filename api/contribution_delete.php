<?php
require_once('../Connections/cov.php');
header('Content-Type: application/json');
mysqli_select_db($cov, $database_cov);

$contriId = $_POST['contriId'] ?? '';
if (!$contriId || !is_numeric($contriId)) {
    echo json_encode(['error' => 'Invalid or missing contribution ID.']);
    exit;
}

$stmt = $cov->prepare("DELETE FROM tbl_contributions WHERE contriId = ?");
$stmt->bind_param("i", $contriId);

if ($stmt->execute()) {
    echo json_encode(['success' => 'Contribution deleted.']);
} else {
    echo json_encode(['error' => 'Error: ' . $stmt->error]);
}
$stmt->close();
