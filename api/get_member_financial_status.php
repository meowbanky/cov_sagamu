<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['UserID'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once('../Connections/cov.php');

$memberid = intval($_GET['memberid'] ?? 0);

if ($memberid <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid Member ID']);
    exit;
}

mysqli_select_db($cov, $database_cov);

$response = [
    'success' => true,
    'total_savings' => 0,
    'loan_balance' => 0,
    'outstanding_interest' => 0,
    'special_loan_balance' => 0,
    'special_outstanding_interest' => 0,
    'dev_levy_balance' => 0
];

// 1. Get Total Savings (Using savings and shares from tlb_mastertransaction)
$querySavings = "SELECT SUM(savings + shares) as total_savings 
                 FROM tlb_mastertransaction 
                 WHERE memberid = ?";
$stmtSavings = mysqli_prepare($cov, $querySavings);
mysqli_stmt_bind_param($stmtSavings, "i", $memberid);
mysqli_stmt_execute($stmtSavings);
$resSavings = mysqli_stmt_get_result($stmtSavings);
if ($row = mysqli_fetch_assoc($resSavings)) {
    $response['total_savings'] = floatval($row['total_savings'] ?? 0);
}
mysqli_stmt_close($stmtSavings);

// 2. Get Regular Loan Balance, Interest, and Dev Levy
$queryLoan = "SELECT 
                (SUM(loanAmount) - SUM(loanRepayment)) as loan_balance,
                (SUM(interest) - SUM(interestPaid)) as outstanding_interest,
                (SUM(dev_fee) - SUM(dev_fee_paid)) as dev_levy_balance
              FROM tlb_mastertransaction 
              WHERE memberid = ?";
$stmtLoan = mysqli_prepare($cov, $queryLoan);
mysqli_stmt_bind_param($stmtLoan, "i", $memberid);
mysqli_stmt_execute($stmtLoan);
$resLoan = mysqli_stmt_get_result($stmtLoan);
if ($row = mysqli_fetch_assoc($resLoan)) {
    $response['loan_balance'] = floatval($row['loan_balance'] ?? 0);
    $response['outstanding_interest'] = floatval($row['outstanding_interest'] ?? 0);
    $response['dev_levy_balance'] = floatval($row['dev_levy_balance'] ?? 0);
}
mysqli_stmt_close($stmtLoan);

// 3. Get Special Loan Balance and Interest
$querySpecialLoan = "SELECT 
                        (SUM(specialLoanAmount) - SUM(specialLoanRepayment)) as special_loan_balance,
                        (SUM(specialInterest) - SUM(specialInterestPaid)) as special_outstanding_interest
                     FROM tlb_mastertransaction 
                     WHERE memberid = ?";
$stmtSpecialLoan = mysqli_prepare($cov, $querySpecialLoan);

if ($stmtSpecialLoan) {
    mysqli_stmt_bind_param($stmtSpecialLoan, "i", $memberid);
    mysqli_stmt_execute($stmtSpecialLoan);
    $resSpecialLoan = mysqli_stmt_get_result($stmtSpecialLoan);
    if ($row = mysqli_fetch_assoc($resSpecialLoan)) {
        $response['special_loan_balance'] = floatval($row['special_loan_balance'] ?? 0);
        $response['special_outstanding_interest'] = floatval($row['special_outstanding_interest'] ?? 0);
    }
    mysqli_stmt_close($stmtSpecialLoan);
}


echo json_encode($response);
?>
