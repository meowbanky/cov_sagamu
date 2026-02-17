<?php
require_once('../Connections/cov.php');
mysqli_select_db($cov, $database_cov);
header('Content-Type: application/json');

/*
  API: Post Dividend as Contribution/Loan Repayment
  Inputs: 
    - basePeriod (Calculation Base)
    - targetPeriod (Where to post)
    - percentage (For verification calculation)
    - members (JSON array of {memberId, destination})
*/

$basePeriod = intval($_POST['basePeriod'] ?? 0);
$targetPeriod = intval($_POST['targetPeriod'] ?? 0);
$percentage = floatval($_POST['percentage'] ?? 0);
$membersJson = $_POST['members'] ?? '[]';
$selectedMembers = json_decode($membersJson, true);

if (!$basePeriod || !$targetPeriod || !$percentage || empty($selectedMembers)) {
    echo json_encode(['error' => 'Invalid input parameters.']);
    exit;
}

// 1. Fetch ALL eligible members and their calculated dividend to verify amounts server-side
// This prevents frontend tampering with amounts
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

$verifiedDividends = [];
while ($row = $result->fetch_assoc()) {
    $verifiedDividends[$row['memberid']] = floatval($row['dividend']);
}
$stmt->close();

// 2. Process Selected Members
$successCount = 0;
$failCount = 0;
$totalSavings = 0;
$totalLoanRepayment = 0;

$stmtSavings = $cov->prepare("INSERT INTO tbl_contributions (membersid, periodid, contribution, special_savings) VALUES (?, ?, ?, 0)");
$stmtLoan = $cov->prepare("INSERT INTO tbl_specialcontributions (membersid, periodid, contribution) VALUES (?, ?, ?)");

foreach ($selectedMembers as $selection) {
    $memberId = $selection['memberId'];
    $destination = $selection['destination']; // 'savings' or 'loan'
    
    // Verify member exists in our calculation and has a dividend
    if (!isset($verifiedDividends[$memberId])) {
        $failCount++;
        continue; // Skip invalid/tampered members
    }
    
    $amount = $verifiedDividends[$memberId];
    
    if ($destination === 'savings') {
        // Insert into tbl_contributions
        // Note: We are setting special_savings to 0 for now as dividend usually goes to main contribution or just added as a lump sum.
        // User request: "dividend would be used as par of their loan repayment" -> executed via 'loan' option.
        // Default is contribution.
        $stmtSavings->bind_param("iid", $memberId, $targetPeriod, $amount);
        if ($stmtSavings->execute()) {
            $successCount++;
            $totalSavings += $amount;
        } else {
            $failCount++;
        }
    } elseif ($destination === 'loan') {
        // Insert into tbl_specialcontributions (Used for Special Loan Repayment in editContributions context)
        $stmtLoan->bind_param("iid", $memberId, $targetPeriod, $amount);
        if ($stmtLoan->execute()) {
            $successCount++;
            $totalLoanRepayment += $amount;
        } else {
            $failCount++;
        }
    }
}

$stmtSavings->close();
$stmtLoan->close();

$details = "Processed: $successCount, Failed: $failCount. <br>Savings: ₦" . number_format($totalSavings, 2) . "<br>Loan Repayment: ₦" . number_format($totalLoanRepayment, 2);

echo json_encode([
    'success' => "Successfully posted records for $successCount members.",
    'details' => $details,
    'count' => $successCount
]);
?>
