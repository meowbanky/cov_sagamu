<?php
/**
 * API Endpoint: Get Overdue Loans
 * 
 * Returns list of members with overdue loans (>10 months since last loan)
 * 
 * Logic:
 * 1. Get current period: MAX(Periodid) from tbpayrollperiods
 * 2. Find members with outstanding loan balance (SUM(loanAmount) - SUM(loanRepayment) > 0)
 * 3. For each member, get MAX(periodid) from tlb_mastertransaction where loanAmount > 0
 * 4. Calculate period gap: current_period - last_loan_period
 * 5. Filter where gap > 10
 */

require_once('../Connections/cov.php');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

mysqli_select_db($cov, $database_cov);

try {
    $months = isset($_GET['months']) ? intval($_GET['months']) : 12;
    if ($months < 1) $months = 12; // validation

    // Get current period (MAX Periodid from tbpayrollperiods)
    $currentPeriodQuery = "SELECT MAX(Periodid) as current_period FROM tbpayrollperiods";
    $currentPeriodResult = mysqli_query($cov, $currentPeriodQuery);
    
    if (!$currentPeriodResult) {
        throw new Exception("Failed to get current period: " . mysqli_error($cov));
    }
    
    $currentPeriodRow = mysqli_fetch_assoc($currentPeriodResult);
    $currentPeriod = $currentPeriodRow['current_period'] ?? null;
    
    if (!$currentPeriod) {
        echo json_encode([
            'success' => false,
            'message' => 'No periods found in system',
            'data' => []
        ]);
        exit;
    }
    
    // Get current period name
    $nameQuery = "SELECT PayrollPeriod FROM tbpayrollperiods WHERE Periodid = ?";
    $nameStmt = mysqli_prepare($cov, $nameQuery);
    mysqli_stmt_bind_param($nameStmt, "i", $currentPeriod);
    mysqli_stmt_execute($nameStmt);
    $nameResult = mysqli_stmt_get_result($nameStmt);
    $nameRow = mysqli_fetch_assoc($nameResult);
    $currentPeriodName = $nameRow['PayrollPeriod'] ?? null;
    mysqli_stmt_close($nameStmt);
    
    // Get all members with outstanding loan balance
    // Step 1: Get members with balance and last loan period
    $sql = "
        SELECT 
            m.memberid,
            CONCAT(IFNULL(p.Lname, ''), ' ', IFNULL(p.Mname, ''), ' ', IFNULL(p.Fname, '')) as member_name,
            m.loan_balance,
            m.last_loan_period,
            ({$currentPeriod} - IFNULL(m.last_loan_period, 0)) as period_gap,
            pr.PayrollPeriod as last_loan_period_name
        FROM (
            SELECT 
                t.memberid,
                SUM(IFNULL(t.loanAmount, 0)) - SUM(IFNULL(t.loanRepayment, 0)) as loan_balance,
                MAX(CASE WHEN t.loanAmount > 0 THEN t.periodid ELSE NULL END) as last_loan_period
            FROM tlb_mastertransaction t
            GROUP BY t.memberid
            HAVING loan_balance > 0
        ) m
        INNER JOIN tbl_personalinfo p ON p.memberid = m.memberid
        LEFT JOIN tbpayrollperiods pr ON pr.Periodid = m.last_loan_period
        WHERE m.last_loan_period IS NOT NULL
        AND ({$currentPeriod} - m.last_loan_period) > {$months}
        ORDER BY period_gap DESC, m.memberid ASC
    ";
    
    $result = mysqli_query($cov, $sql);
    
    if (!$result) {
        throw new Exception("Failed to query overdue loans: " . mysqli_error($cov));
    }
    
    $overdueLoans = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Get last loan amount for this member and period
        $lastLoanQuery = "SELECT SUM(loanAmount) as last_loan_amount 
                         FROM tlb_mastertransaction 
                         WHERE memberid = ? AND periodid = ? AND loanAmount > 0";
        $lastLoanStmt = mysqli_prepare($cov, $lastLoanQuery);
        mysqli_stmt_bind_param($lastLoanStmt, "ii", $row['memberid'], $row['last_loan_period']);
        mysqli_stmt_execute($lastLoanStmt);
        $lastLoanResult = mysqli_stmt_get_result($lastLoanStmt);
        $lastLoanRow = mysqli_fetch_assoc($lastLoanResult);
        mysqli_stmt_close($lastLoanStmt);
        
        $overdueLoans[] = [
            'memberid' => $row['memberid'],
            'member_name' => trim($row['member_name']),
            'loan_balance' => floatval($row['loan_balance']),
            'last_loan_period' => intval($row['last_loan_period']),
            'last_loan_period_name' => $row['last_loan_period_name'],
            'last_loan_amount' => floatval($lastLoanRow['last_loan_amount'] ?? 0),
            'period_gap' => intval($row['period_gap']),
            'current_period' => intval($currentPeriod)
        ];
    }
    
    echo json_encode([
        'success' => true,
        'current_period' => intval($currentPeriod),
        'current_period_name' => $currentPeriodName,
        'total_overdue' => count($overdueLoans),
        'threshold' => $months,
        'data' => $overdueLoans
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => []
    ]);
}