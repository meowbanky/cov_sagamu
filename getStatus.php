<?php
require_once('Connections/cov.php');
mysqli_select_db($cov, $database_cov);

// Get member id and period from query string
$col_status = isset($_GET['id']) ? $_GET['id'] : "-1";
$col_period = isset($_GET['period']) ? $_GET['period'] : "-1";

// Validate inputs
$col_status = preg_replace('/\D/', '', $col_status); // Only numbers
$col_period = preg_replace('/\D/', '', $col_period); // Only numbers

// Prepare query (status)
$query = "
SELECT
    pi.memberid,
    CONCAT(pi.Lname,' , ', pi.Fname,' ', IFNULL(pi.Mname,'')) AS namess,
    IFNULL(SUM(mt.loanAmount),0) AS loan,
    IFNULL(SUM(mt.loanRepayment),0) AS loanrepayments,
    IFNULL(SUM(mt.withdrawal),0) AS withdrawals,
    (
        IFNULL(SUM(mt.loanRepayment),0) +
        IFNULL(SUM(mt.entryFee),0) +
        IFNULL(SUM(mt.savings),0) +
        IFNULL(SUM(mt.shares),0) +
        IFNULL(SUM(mt.interestPaid),0)
    ) AS total,
    pp.PayrollPeriod,
    (IFNULL(SUM(mt.loanAmount),0) - IFNULL(SUM(mt.loanRepayment),0)) AS loanBalance,
    IFNULL(SUM(mt.entryFee),0) AS entryFee,
    IFNULL(SUM(mt.savings),0) AS savings,
    IFNULL(SUM(mt.shares),0) AS shares,
    IFNULL(SUM(mt.interestPaid),0) AS interestPaid,
    IFNULL(SUM(mt.interest),0) AS interest
FROM tbl_personalinfo pi
INNER JOIN tlb_mastertransaction mt ON pi.memberid = mt.memberid
INNER JOIN tbpayrollperiods pp ON pp.Periodid = mt.periodid
LEFT JOIN tbl_refund tr ON tr.membersid = pi.memberid AND tr.periodid = pp.Periodid
WHERE pi.memberid = ? AND mt.periodid <= ?
GROUP BY pi.memberid
LIMIT 1
";

// Prepare and execute
$stmt = $cov->prepare($query);
$stmt->bind_param("ii", $col_status, $col_period);
$stmt->execute();
$result = $stmt->get_result();
$row_status = $result->fetch_assoc();

// Get Payroll Period string
$periodLabel = '';
if ($col_period && $col_period > 0) {
    $stmt2 = $cov->prepare("SELECT PayrollPeriod FROM tbpayrollperiods WHERE Periodid = ?");
    $stmt2->bind_param("i", $col_period);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    $periodLabel = ($periodRow = $res2->fetch_assoc()) ? $periodRow['PayrollPeriod'] : '';
    $stmt2->close();
}

// Field function for pretty card display
function field($label, $value, $align='right', $currency=false) {
    $formatted = $currency ? number_format(floatval($value),2,'.',',') : htmlspecialchars($value ?? '-');
    return "
    <div class='flex justify-between items-center py-2 border-b last:border-b-0'>
        <div class='text-gray-500 font-medium'>$label</div>
        <div class='text-gray-900 font-bold text-$align'>" . ($currency ? '₦ ' : '') . "$formatted</div>
    </div>";
}
?>

<div class="w-full max-w-lg mx-auto bg-gradient-to-tr from-blue-50 to-white border border-blue-100 rounded-2xl shadow-lg p-6 mt-3">
<?php if ($row_status): ?>
    <div class="mb-2">
      <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Member</div>
      <div class="text-lg font-extrabold text-blue-900"><?= htmlspecialchars($row_status['namess']) ?></div>
      <div class="flex gap-2 items-center text-gray-600 text-sm mb-2">
        <span class="font-semibold">ID:</span> <?= htmlspecialchars($row_status['memberid']) ?>
        <span class="inline-block px-2 py-1 bg-blue-100 text-blue-700 rounded ml-4"><?= htmlspecialchars($periodLabel) ?></span>
      </div>
    </div>
    <div class="divide-y divide-blue-50">
        <?= field("Savings", $row_status['savings'], 'right', true) ?>
        <?= field("Shares", $row_status['shares'], 'right', true) ?>
        <?= field("Withdrawals", $row_status['withdrawals'], 'right', true) ?>
        <?= field("Loan", $row_status['loan'], 'right', true) ?>
        <?= field("Loan Repayment", $row_status['loanrepayments'], 'right', true) ?>
        <?= field("Loan Balance", $row_status['loanBalance'], 'right', true) ?>
        <?= field("Interest", $row_status['interest'], 'right', true) ?>
        <?= field("Interest Paid", $row_status['interestPaid'], 'right', true) ?>
        <?= field("Unpaid Interest", floatval($row_status['interest']) - floatval($row_status['interestPaid']), 'right', true) ?>
    </div>
<?php else: ?>
    <div class="text-center text-gray-500 py-12">
        <svg class="mx-auto h-12 w-12 text-blue-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 48 48"><circle cx="24" cy="24" r="22" stroke-width="3" class="opacity-30"/><path stroke-width="3" stroke-linecap="round" stroke-linejoin="round" d="M16 24l6 6 10-10"/></svg>
        <div class="text-xl font-semibold text-blue-900 mb-2">No Status Found</div>
        <div class="text-gray-400">No records available for this member and period.</div>
    </div>
<?php endif; ?>
</div>
<?php
$stmt->close();
?>
