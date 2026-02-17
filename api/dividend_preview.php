<?php
require_once('../Connections/cov.php');
mysqli_select_db($cov, $database_cov);
header('Content-Type: application/json');

$period = intval($_GET['period'] ?? 0);
$percentage = floatval($_GET['percentage'] ?? 0);

if (!$period || !$percentage) {
    echo json_encode(['error' => 'Period and percentage are required.']);
    exit;
}

// Calculate Dividend
// Formula: (Total Shares + Total Savings) * Percentage
// Restricted to Active members up to the selected Period
$query = "
    SELECT 
        m.memberid,
        CONCAT(IFNULL(p.Lname,''), ' ', IFNULL(p.Mname,''), ' ', IFNULL(p.Fname,'')) AS name,
        IFNULL(SUM(m.shares), 0) + IFNULL(SUM(m.savings), 0) AS total_holdings,
        (IFNULL(SUM(m.shares), 0) + IFNULL(SUM(m.savings), 0)) * ? AS dividend,
        a.AccountNo,
        b.bank,
        b.bankcode
    FROM tlb_mastertransaction m
    INNER JOIN tbl_personalinfo p ON p.memberid = m.memberid
    LEFT JOIN tblaccountno a ON a.COOPNO = p.memberid
    LEFT JOIN tblbankcode b ON b.bankcode = a.bank_code
    WHERE m.periodid <= ? AND p.Status = 'Active'
    GROUP BY m.memberid
    HAVING dividend > 0
    ORDER BY memberid ASC
";

$stmt = $cov->prepare($query);
$calcPercentage = $percentage / 100; // Assuming input is percentage like 5 for 5%
// Wait, getDividend.php used the raw percentage directly in SQL multiplication. 
// "((Sum...Shares)+Sum...savings))) * %s" 
// If user enters 0.05 for 5%, then it's direct. If they enter 5, it's 500%. 
// Let's assume user enters the decimal value as per the old code which used direct multiplication.
// Actually, let's check the old code again.
// Old code: ... * %s AS dividend ... GetSQLValueString($cov,$percentage, "double")
// So it uses whatever value is passed. I will assume it is a decimal (e.g., 0.05).
// BUT to be safe and user friendly, I should probably handle it as percentage (e.g., 5 means 5%).
// However, sticking to old logic for now: Direct multiplication.
// Let's look at getDividend.php again. It just used $percentage.
// I will use direct value for now but add a comment in UI.

$stmt->bind_param("di", $percentage, $period);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
$totalHoldings = 0;
$totalDividend = 0;

while ($row = $result->fetch_assoc()) {
    $row['dividend'] = floatval($row['dividend']);
    $row['total_holdings'] = floatval($row['total_holdings']);
    
    // Check for overdue status
    // Logic adapted from get_overdue_loans.php
    // 1. Get Loan Balance
    $balanceQuery = "SELECT SUM(loanAmount) - SUM(loanRepayment) as balance FROM tlb_mastertransaction WHERE memberid = '{$row['memberid']}'";
    $balanceResult = mysqli_query($cov, $balanceQuery);
    $balanceRow = mysqli_fetch_assoc($balanceResult);
    $loanBalance = floatval($balanceRow['balance'] ?? 0);

    $isOverdue = false;
    if ($loanBalance > 0) {
        // 2. Get Last Loan Period
        $lastLoanQuery = "SELECT MAX(periodid) as last_period FROM tlb_mastertransaction WHERE memberid = '{$row['memberid']}' AND loanAmount > 0";
        $lastLoanResult = mysqli_query($cov, $lastLoanQuery);
        $lastLoanRow = mysqli_fetch_assoc($lastLoanResult);
        $lastLoanPeriod = intval($lastLoanRow['last_period'] ?? 0);

        // 3. Get Current System Period (Max Period)
        // We can optimize this by fetching it once outside the loop, but for now safe inside.
        // Actually, let's fetch it once outside.
        if (!isset($currentSystemPeriod)) {
             $pQuery = "SELECT MAX(Periodid) as cur FROM tbpayrollperiods";
             $pResult = mysqli_query($cov, $pQuery);
             $pRow = mysqli_fetch_assoc($pResult);
             $currentSystemPeriod = intval($pRow['cur'] ?? 0);
        }

        if ($lastLoanPeriod > 0 && ($currentSystemPeriod - $lastLoanPeriod) > 12) {
            $isOverdue = true;
        }
    }

    $row['is_overdue'] = $isOverdue;
    $row['loan_balance'] = $loanBalance;

    $totalHoldings += $row['total_holdings'];

    $totalDividend += $row['dividend'];
    
    $data[] = $row;
}

echo json_encode([
    'data' => $data,
    'totalHoldings' => $totalHoldings,
    'totalDividend' => $totalDividend
]);
?>
