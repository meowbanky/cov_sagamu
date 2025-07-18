<?php
require_once('Connections/cov.php');
header('Content-Type: application/json'); // AJAX expects JSON

session_start();
if (!isset($_SESSION['UserID'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

if (!isset($_POST['transactionIds']) || !is_array($_POST['transactionIds'])) {
    echo json_encode(['success' => false, 'error' => 'No transactions provided']);
    exit;
}

$success = true;
$errors = [];
foreach ($_POST['transactionIds'] as $transactionid) {
    $info_array = explode(",", $transactionid);
    if (count($info_array) !== 2) continue;
    $memberid = (int)$info_array[0];
    $periodid = (int)$info_array[1];
    $pay_method = 0;

    mysqli_select_db($cov, $database_cov);
    // 1. Delete from tlb_mastertransaction
    $stmt = $cov->prepare("DELETE FROM tlb_mastertransaction WHERE periodid=? AND memberid=? AND pay_method=?");
    $stmt->bind_param("iii", $periodid, $memberid, $pay_method);
    if (!$stmt->execute()) $errors[] = "Mastertransaction: " . $stmt->error;
    $stmt->close();

    // 2. Delete from tbl_loan
    $stmt = $cov->prepare("DELETE FROM tbl_loan WHERE periodid=? AND memberid=?");
    $stmt->bind_param("ii", $periodid, $memberid);
    if (!$stmt->execute()) $errors[] = "Loan: " . $stmt->error;
    $stmt->close();

    // 3. Delete from tbl_refund
    $stmt = $cov->prepare("DELETE FROM tbl_refund WHERE periodid=? AND membersid=?");
    $stmt->bind_param("ii", $periodid, $memberid);
    if (!$stmt->execute()) $errors[] = "Refund: " . $stmt->error;
    $stmt->close();

    // 4. Delete from tbl_entryfees
    $stmt = $cov->prepare("DELETE FROM tbl_entryfees WHERE periodid=? AND memberid=?");
    $stmt->bind_param("ii", $periodid, $memberid);
    if (!$stmt->execute()) $errors[] = "Entryfees: " . $stmt->error;
    $stmt->close();
}

if (empty($errors)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $errors]);
}
exit;
?>
