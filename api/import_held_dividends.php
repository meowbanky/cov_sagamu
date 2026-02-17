<?php
require_once('../Connections/cov.php');
mysqli_select_db($cov, $database_cov);
header('Content-Type: application/json');

/*
  API: Import Held Dividends to Contribution (Loan Repayment)
  Inputs:
    - sourcePeriodId (The dividend year)
    - targetPeriodId (The contribution month to post to)
*/

$sourcePeriodId = intval($_POST['sourcePeriodId'] ?? 0);
$targetPeriodId = intval($_POST['targetPeriodId'] ?? 0);

if (!$sourcePeriodId || !$targetPeriodId) {
    echo json_encode(['error' => 'Source and Target periods are required.']);
    exit;
}

// 1. Fetch Pending Dividends
$query = "SELECT id, member_id, amount FROM tbl_overdue_dividend_held WHERE source_period_id = ? AND status = 'pending'";
$stmt = $cov->prepare($query);
$stmt->bind_param("i", $sourcePeriodId);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}
$stmt->close();

if (empty($items)) {
    echo json_encode(['error' => 'No pending dividends found for selected year.']);
    exit;
}

$destination = $_POST['destination'] ?? 'special_repayment';
$validDestinations = ['contribution', 'special_repayment'];

if (!in_array($destination, $validDestinations)) {
    echo json_encode(['error' => 'Invalid destination selected.']);
    exit;
}

// 2. Insert into Target Table and Update Holding Status
$successCount = 0;
$failCount = 0;

if ($destination === 'contribution') {
    // Regular Contribution (tbl_contributions)
    // Assuming 'contribution' column for amount. 
    // Note: tbl_contributions has (membersid, periodid, contribution, special_savings)
    $insertStmt = $cov->prepare("INSERT INTO tbl_contributions (membersid, periodid, contribution, special_savings) VALUES (?, ?, ?, 0)");
} else {
    // Special Loan Repayment (tbl_specialcontributions)
    $insertStmt = $cov->prepare("INSERT INTO tbl_specialcontributions (membersid, periodid, contribution) VALUES (?, ?, ?)");
}

$updateStmt = $cov->prepare("UPDATE tbl_overdue_dividend_held SET status = 'imported', target_period_id = ?, updated_at = NOW() WHERE id = ?");

foreach ($items as $item) {
    $memberId = $item['member_id'];
    $amount = $item['amount'];
    $heldId = $item['id'];
    
    // Insert
    if ($destination === 'contribution') {
         $insertStmt->bind_param("iid", $memberId, $targetPeriodId, $amount);
    } else {
         $insertStmt->bind_param("iid", $memberId, $targetPeriodId, $amount); // member_id is int? checked previously usage seems consistent
    }

    if ($insertStmt->execute()) {
        // Mark as Imported
        $updateStmt->bind_param("ii", $targetPeriodId, $heldId);
        $updateStmt->execute();
        $successCount++;
    } else {
        $failCount++;
    }
}

echo json_encode([
    'success' => "Successfully imported $successCount records.",
    'count' => $successCount,
    'failed' => $failCount
]);
?>
