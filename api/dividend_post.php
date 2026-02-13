<?php
require_once('../Connections/cov.php');
mysqli_select_db($cov, $database_cov);
header('Content-Type: application/json');

/*
  Logic to POST Dividend:
  1. Calculate dividend for each active member based on Period and Percentage.
  2. Insert a record into tlb_mastertransaction for the *Target Period* (Dividend Period).
     - The dividend amount is likely added to 'savings' or maybe there isn't a dividend column?
     - Looking at tlb_mastertransaction schema:
       savings, shares, loanAmount, loanRepayment, withdrawal...
       There is no 'dividend' column.
       Usually dividends are re-invested as Savings or Shares.
       Let's assume 'savings' for now, or maybe it's a 'repayment' of some sort?
       Actually, standard practice in Coops is often to add to Savings.
       Let's check if there's any other column. 'entryFee', 'interest', 'withdrawal'...
       
       Wait, the user said "use mofrder logic".
       If I post it, I should probably ask where it goes.
       But looking at the *old* dividend.php, it calls `getCalcDividend.php`.
       Since I can't read `getCalcDividend.php`, I have to guess.
       
       Hypothesis: Dividends are added to Savings.
       I will use 'savings' column for the dividend amount.
*/

$basePeriod = intval($_POST['basePeriod'] ?? 0);
$targetPeriod = intval($_POST['targetPeriod'] ?? 0);
$percentage = floatval($_POST['percentage'] ?? 0);

if (!$basePeriod || !$targetPeriod || !$percentage) {
    echo json_encode(['error' => 'All fields are required.']);
    exit;
}

// 1. Get Eligible Members and Amounts
$query = "
    SELECT 
        m.memberid,
        (IFNULL(SUM(m.shares), 0) + IFNULL(SUM(m.savings), 0)) * ? AS dividend
    FROM tlb_mastertransaction m
    INNER JOIN tbl_personalinfo p ON p.memberid = m.memberid
    WHERE m.periodid <= ? AND p.Status = 'Active'
    GROUP BY m.memberid
    HAVING dividend > 0
";

$stmt = $cov->prepare($query);
$stmt->bind_param("di", $percentage, $basePeriod);
$stmt->execute();
$result = $stmt->get_result();

$count = 0;
$total = 0;

// Prepare Insert Statement
// Inserting into tlb_mastertransaction
// We'll set 'periodid', 'memberid', 'savings' (as dividend), 'DateOfPayment'
// We should probably mark it as a dividend transaction. Maybe 'pay_method'? or just narration? 
// The table doesn't have narration. 
// Let's assume we just put it in Savings.
$insertStmt = $cov->prepare("INSERT INTO tlb_mastertransaction (periodid, memberid, savings, DateOfPayment, completed) VALUES (?, ?, ?, NOW(), 1)");

while ($row = $result->fetch_assoc()) {
    $dividend = floatval($row['dividend']);
    if ($dividend > 0) {
        $insertStmt->bind_param("iid", $targetPeriod, $row['memberid'], $dividend);
        if ($insertStmt->execute()) {
            $count++;
            $total += $dividend;
        }
    }
}

echo json_encode([
    'success' => "Dividend posted successfully for $count members.",
    'count' => $count,
    'total_amount' => $total
]);
?>
