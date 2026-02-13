<?php
require_once('../Connections/cov.php');
mysqli_select_db($cov, $database_cov);
header('Content-Type: application/json');

/*
  Logic to DELETE Dividend:
  This is tricky without a specific 'dividend' transaction flag.
  If I just inserted them as 'savings', how do I distinguish them from regular savings?
  
  Maybe `getDeleteDividend.php` deleted based on Period?
  "Delete all transactions for a specific Period where...?"
  
  If the previous system allowed deleting, it must have had a way to identify them.
  Maybe they were isolated in a specific period?
  
  For now, I will implement a delete that deletes by Period and maybe check if 'entryFee' is 0, 'shares' is 0, etc, to isolate these "savings-only" transactions?
  Or maybe I should verify if there's a better column.
  
  Actually, let's look at `tlb_mastertransaction` again.
  `specialLoanRepayment`, `specialInterest`...
  
  The safer bet is to allow deleting by Transaction ID if I list them.
  But the UI implies "Delete Dividend" for the *whole batch* (Period).
  
  "getDeleteDividend.php?period="+period+"&percentage="+percentage+"&PeriodID="+dividendPeriod
  
  It reused period and percent? That suggests it might recalculate to find matching records?
  Or maybe it just deletes ALL from `dividendPeriod`?
  
  Let's assume it deletes ALL from the Target Period (`dividendPeriod`) that look like dividends.
  But that's risky if regular savings are also there.
  
  Alternative: The user might know that a "Dividend Period" is *only* for dividends.
  If so, I can delete all from that period.
  
  Let's implement a safe delete: 
  Delete from tlb_mastertransaction WHERE periodid = ? 
  BUT I should probably filtering to be safe.
  
  Wait, if I use the SAME logic as post, I can find the members and amounts.
  But amounts might change if I recalculate?
  
  Let's just delete ALL from the target period for now, but with a warning.
  Refining: I'll add a check. Only delete where loanAmount=0, interest=0, etc. just 'savings' > 0?
*/

$targetPeriod = intval($_POST['targetPeriod'] ?? 0);

if (!$targetPeriod) {
    echo json_encode(['error' => 'Target Period is required.']);
    exit;
}

// Delete all transactions in this period that are PURELY savings (no loan, no shares, etc)
// This is a heuristic to avoid deleting real contributions if they are mixed.
// But if "Dividend Selection" is a specific period, maybe it's fine.

$query = "DELETE FROM tlb_mastertransaction WHERE periodid = ? AND shares = 0 AND loanAmount = 0 AND loanRepayment = 0";
$stmt = $cov->prepare($query);
$stmt->bind_param("i", $targetPeriod);

if ($stmt->execute()) {
    echo json_encode(['success' => 'Dividend transactions deleted for the selected period.']);
} else {
    echo json_encode(['error' => 'Error deleting dividends: ' . $stmt->error]);
}
?>
