<?php
require_once('Connections/cov.php');
header('Content-Type: application/json');

mysqli_select_db($cov, $database_cov);

$memberid = intval($_POST['txtCoopid'] ?? 0);
$periodid = intval($_POST['PeriodId'] ?? 0);
$amount = floatval(str_replace(",", "", $_POST['txtAmountGranted'] ?? ''));
$loan_date = $_POST['loan_date'] ?? '';
$loanid = isset($_POST['edit_loanid']) ? intval($_POST['edit_loanid']) : 0;

if (!$periodid) { echo json_encode(['error' => "Period required."]); exit; }
if (!$memberid) { echo json_encode(['error' => "Member No required."]); exit; }
if (!$amount) { echo json_encode(['error' => "Amount required."]); exit; }
if (!$loan_date) { echo json_encode(['error' => "Loan date required."]); exit; }

if ($loanid) {
    // Update
    $stmt = $cov->prepare("UPDATE tbl_loan SET memberid=?, periodid=?, loanamount=?, loan_date=? WHERE loanid=?");
    $stmt->bind_param('iidsi', $memberid, $periodid, $amount, $loan_date, $loanid);
    if ($stmt->execute()) {
        echo json_encode(['success' => "Loan updated successfully!"]);
    } else {
        echo json_encode(['error' => "Database error: " . htmlspecialchars($stmt->error)]);
    }
    $stmt->close();
} else {
    // Insert
    $stmt = $cov->prepare("INSERT INTO tbl_loan (memberid, periodid, loanamount, loan_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('iids', $memberid, $periodid, $amount, $loan_date);
    if ($stmt->execute()) {
        echo json_encode(['success' => "Loan added successfully!"]);
    } else {
        echo json_encode(['error' => "Database error: " . htmlspecialchars($stmt->error)]);
    }
    $stmt->close();
}
