<?php
require_once('../Connections/cov.php');
mysqli_select_db($cov, $database_cov);
header('Content-Type: application/json');

/*
  API: Save Overdue Dividend to Holding
  Inputs: 
    - basePeriod (Calculation Base)
    - percentage
    - members (JSON array of memberIds)
*/

$basePeriod = intval($_POST['basePeriod'] ?? 0);
$percentage = floatval($_POST['percentage'] ?? 0);
$membersJson = $_POST['members'] ?? '[]';
$memberIds = json_decode($membersJson, true);

if (!$basePeriod || !$percentage || empty($memberIds)) {
    echo json_encode(['error' => 'Invalid input parameters.']);
    exit;
}

// 1. Verify Dividend Amounts Server-Side
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

// 2. Insert into Holding Table
$successCount = 0;
$failCount = 0;

$insertStmt = $cov->prepare("
    INSERT INTO tbl_overdue_dividend_held (member_id, source_period_id, amount, status) 
    VALUES (?, ?, ?, 'pending')
    ON DUPLICATE KEY UPDATE amount = VALUES(amount), status = 'pending', updated_at = NOW()
");

// Note: ON DUPLICATE KEY UPDATE assumes we might have a unique constraint or just want to update if logic allows.
// However, the table definition I gave user doesn't have a unique constraint on (member_id, source_period_id).
// Let's assume user might run this multiple times for the same period. 
// Ideally we should verify if a pending record exists and update it, or delete pending and re-insert.
// Let's check for existing pending record first to be safe or just insert. 
// Actually, simple INSERT is safer if we don't have unique keys yet. 
// But to prevent duplicates, let's check.

$checkStmt = $cov->prepare("SELECT id FROM tbl_overdue_dividend_held WHERE member_id = ? AND source_period_id = ? AND status = 'pending'");

// Re-preparing insert for pure insert
$insertStmt = $cov->prepare("INSERT INTO tbl_overdue_dividend_held (member_id, source_period_id, amount, status) VALUES (?, ?, ?, 'pending')");
$updateStmt = $cov->prepare("UPDATE tbl_overdue_dividend_held SET amount = ?, updated_at = NOW() WHERE id = ?");

foreach ($memberIds as $memberId) { // memberIds is just array of strings [MEM001, MEM002]
    if (!isset($verifiedDividends[$memberId])) {
        $failCount++;
        continue;
    }
    
    $amount = $verifiedDividends[$memberId];

    // Check existing
    $checkStmt->bind_param("si", $memberId, $basePeriod);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkRow = $checkResult->fetch_assoc()) {
        // Update existing pending
        $updateStmt->bind_param("di", $amount, $checkRow['id']);
        if ($updateStmt->execute()) $successCount++;
        else $failCount++;
    } else {
        // Insert new
        $insertStmt->bind_param("sid", $memberId, $basePeriod, $amount);
        if ($insertStmt->execute()) $successCount++;
        else $failCount++;
    }
}

echo json_encode([
    'success' => "Successfully saved $successCount dividends to holding.",
    'count' => $successCount,
    'failed' => $failCount
]);
?>
