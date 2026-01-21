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

// Get Special Loan Interest Rate from Global Settings (ID 9)
$query_sp_rate = "SELECT value FROM tbl_globa_settings WHERE setting_id = 9";
$res_sp_rate = mysqli_query($cov, $query_sp_rate);
$row_sp_rate = mysqli_fetch_assoc($res_sp_rate);
$rate = floatval($row_sp_rate['value'] ?? 0.02);

// Calculate Interest
$interest = $amount * ($rate / 100);

if ($loanid) {
    // Update
    $stmt = $cov->prepare("UPDATE tbl_special_loan SET memberid=?, periodid=?, loanamount=?, interest=?, loan_date=? WHERE loanid=?");
    $stmt->bind_param('iiddsi', $memberid, $periodid, $amount, $interest, $loan_date, $loanid);
    if ($stmt->execute()) {
        echo json_encode(['success' => "Special Loan updated successfully!"]);
    } else {
        echo json_encode(['error' => "Database error: " . htmlspecialchars($stmt->error)]);
    }
    $stmt->close();
} else {
    // Insert
    $stmt = $cov->prepare("INSERT INTO tbl_special_loan (memberid, periodid, loanamount, interest, loan_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('iidds', $memberid, $periodid, $amount, $interest, $loan_date);
    if ($stmt->execute()) {
        echo json_encode(['success' => "Special Loan added successfully!"]);
    } else {
        echo json_encode(['error' => "Database error: " . htmlspecialchars($stmt->error)]);
    }
    $stmt->close();
}
