<?php
require_once('Connections/cov.php');
session_start();

if (!isset($_SESSION['UserID'])) {
    header("Location: index.php");
    exit();
}

function getIntOrDefault($input, $default = -1) {
    if (isset($input) && is_numeric($input)) {
        return (int)$input;
    }
    return $default;
}
mysqli_select_db($cov, $database_cov);

// Use strict integer values
$periodFrom_status = getIntOrDefault($_GET['periodfrom'] ?? null, -1);
$periodTo_status   = getIntOrDefault($_GET['periodTo'] ?? null, -1);
$id_status         = getIntOrDefault($_GET['id'] ?? null, -1);

// PREPARED STATEMENT FOR STATUS QUERY
$statusQuery = "
SELECT 
    tlb_mastertransaction.memberid,
    tbpayrollperiods.Periodid,
    CONCAT(tbl_personalinfo.Lname, ' , ', tbl_personalinfo.Fname, ' ', IFNULL(tbl_personalinfo.Mname, '')) AS namess,
    tbpayrollperiods.PayrollPeriod,
    SUM(tlb_mastertransaction.entryFee) as entryFee,
    SUM(tlb_mastertransaction.savings) as savingsAmount,
    SUM(tlb_mastertransaction.shares) as sharesAmount,
    SUM(tlb_mastertransaction.interestPaid) as InterestPaid,
    SUM(tlb_mastertransaction.interest) as interest,
    SUM(tlb_mastertransaction.loanAmount) as loan,
    SUM(tlb_mastertransaction.loanRepayment) as loanRepayment,
    (
        SELECT SUM(m2.interest) - SUM(m2.interestPaid)
        FROM tlb_mastertransaction m2
        WHERE m2.memberid = tlb_mastertransaction.memberid
        AND m2.periodid <= tlb_mastertransaction.periodid
    ) as interestBalance,
    (
        SELECT SUM(m2.loanAmount) - SUM(m2.loanRepayment)
        FROM tlb_mastertransaction m2
        WHERE m2.memberid = tlb_mastertransaction.memberid
        AND m2.periodid <= tlb_mastertransaction.periodid
    ) as loanBalance,
    (
        SELECT SUM(m2.savings)
        FROM tlb_mastertransaction m2
        WHERE m2.memberid = tlb_mastertransaction.memberid
        AND m2.periodid <= tlb_mastertransaction.periodid
    ) as savingsBalance,
    (
        SELECT SUM(m2.shares)
        FROM tlb_mastertransaction m2
        WHERE m2.memberid = tlb_mastertransaction.memberid
        AND m2.periodid <= tlb_mastertransaction.periodid
    ) as sharesBalance,
    SUM(
        tlb_mastertransaction.entryFee + 
        tlb_mastertransaction.savings + 
        tlb_mastertransaction.shares + 
        tlb_mastertransaction.interestPaid + 
        tlb_mastertransaction.loanRepayment + 
        tlb_mastertransaction.repayment_bank
    ) as total
FROM tlb_mastertransaction
INNER JOIN tbl_personalinfo ON tlb_mastertransaction.memberid = tbl_personalinfo.memberid
LEFT JOIN tbpayrollperiods ON tlb_mastertransaction.periodid = tbpayrollperiods.Periodid
WHERE tlb_mastertransaction.periodid BETWEEN ? AND ?
";
$params = [$periodFrom_status, $periodTo_status];
$types  = "ii";
if ($id_status !== -1) {
    $statusQuery .= " AND tbl_personalinfo.memberid = ? ";
    $params[] = $id_status;
    $types .= "i";
}
$statusQuery .= " GROUP BY tbpayrollperiods.Periodid, tlb_mastertransaction.memberid ORDER BY tbpayrollperiods.Periodid DESC";

$statusStmt = $cov->prepare($statusQuery);
$statusStmt->bind_param($types, ...$params);
$statusStmt->execute();
$status = $statusStmt->get_result();

// PREPARED STATEMENT FOR TOTAL SUM QUERY
$totalQuery = "
SELECT
    ANY_VALUE(tbl_personalinfo.memberid) AS memberid,
    ANY_VALUE(tlb_mastertransaction.transactionid) AS transactionid,
    IFNULL(SUM(tlb_mastertransaction.loanAmount), 0) AS loan,
    IFNULL(SUM(tlb_mastertransaction.loanRepayment), 0) AS loanrepayments,
    IFNULL(SUM(tlb_mastertransaction.withdrawal), 0) AS withdrawals,
    (IFNULL(SUM(tlb_mastertransaction.loanRepayment), 0) + IFNULL(SUM(tlb_mastertransaction.entryFee), 0) + IFNULL(SUM(tlb_mastertransaction.savings), 0) + 
    IFNULL(SUM(tlb_mastertransaction.shares), 0) + IFNULL(SUM(tlb_mastertransaction.interestPaid), 0)) AS total,
    ANY_VALUE(tbpayrollperiods.PayrollPeriod) AS PayrollPeriod,
    ANY_VALUE(tlb_mastertransaction.periodid) AS periodid,
    IFNULL(SUM(tlb_mastertransaction.entryFee), 0) AS entryFee,
    IFNULL(SUM(tlb_mastertransaction.savings), 0) AS savings,
    IFNULL(SUM(tlb_mastertransaction.shares), 0) AS shares,
    IFNULL(SUM(tlb_mastertransaction.interestPaid), 0) AS interestPaid,
    IFNULL(SUM(tlb_mastertransaction.interest), 0) AS interest
FROM tbl_personalinfo
INNER JOIN tlb_mastertransaction ON tbl_personalinfo.memberid = tlb_mastertransaction.memberid
INNER JOIN tbpayrollperiods ON tbpayrollperiods.Periodid = tlb_mastertransaction.periodid
LEFT JOIN tbl_refund ON tbl_refund.membersid = tbl_personalinfo.memberid AND tbl_refund.periodid = tbpayrollperiods.Periodid
WHERE tbpayrollperiods.Periodid BETWEEN ? AND ?
";
$totalParams = [$periodFrom_status, $periodTo_status];
$totalTypes = "ii";
if ($id_status !== -1) {
    $totalQuery .= " AND tlb_mastertransaction.memberid = ? ";
    $totalParams[] = $id_status;
    $totalTypes .= "i";
}

$totalStmt = $cov->prepare($totalQuery);
$totalStmt->bind_param($totalTypes, ...$totalParams);
$totalStmt->execute();
$totalsum = $totalStmt->get_result();
$row_totalsum = $totalsum->fetch_assoc();
?>

<!-- Card-View CSS for Mobile -->
<style>
@media (max-width: 640px) {
  .overflow-x-auto, .rounded-lg, .shadow, .border, .bg-white {
    border-radius: 0 !important;
    box-shadow: none !important;
    border: none !important;
    background: none !important;
  }
  table, thead, tbody, th, td, tr {
    display: block !important;
    width: 100% !important;
  }
  thead {
    display: none !important;
  }
  tr {
    margin-bottom: 1.25rem !important;
    background: #fff !important;
    border-radius: 0.75rem !important;
    box-shadow: 0 2px 12px rgba(0,0,0,0.07) !important;
    padding: 0.8rem 0.3rem !important;
  }
  td {
    border: none !important;
    border-bottom: 1px solid #f1f5f9 !important;
    position: relative !important;
    padding-left: 22% !important;  /* was 50%, reduce for less gap */
    min-height: 36px !important;
    font-size: 0.97rem !important;
    background: none !important;
    box-shadow: none !important;
    word-break: break-word;
  }
  td:last-child {
    border-bottom: none !important;
  }
  td:before {
    position: absolute !important;
    left: 0.65rem !important;
    top: 0;
    font-weight: 600 !important;
    color: #64748b !important;
    content: attr(data-label) !important;
    width: 28% !important;   /* was 46%, reduce for tighter label */
    white-space: normal !important;
    font-size: 0.94em !important;
    padding-right: 0.5em;
  }
}

</style>

<input type="hidden" name="filename" id="filename" value="<?php echo htmlspecialchars($_GET['filename'] ?? ''); ?>">

<!-- Action Buttons -->
<div class="flex flex-wrap justify-end gap-2 mb-4">
    <button name="exportpdf" id="exportpdf" type="button"
        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition font-semibold">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v8m0 0-3-3m3 3 3-3m6 3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        Export PDF
    </button>
    <button name="exportexcel" id="exportexcel" type="button"
        class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg shadow hover:bg-purple-700 transition font-semibold">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 17v1a3 3 0 01-3 3H7a3 3 0 01-3-3V7a3 3 0 013-3h1" /><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8m-8 4h8m2-10v4a2 2 0 002 2h4" /></svg>
        Export Excel
    </button>
</div>

<!-- Table Responsive Wrapper -->
<div class="overflow-x-auto rounded-lg shadow border border-slate-200 bg-white">
    <table class="min-w-[1200px] w-full text-sm" id="sample_1">
        <thead class="bg-blue-800 text-white sticky top-0 z-10">
            <tr>
                <th class="py-3 px-2 text-left font-semibold">
                    <span>Select</span>
                    <button type="button" id="deleteT" name="deleteT"
                        class="ml-2 inline-block px-2 py-1 bg-red-600 hover:bg-red-700 text-xs font-bold rounded transition text-white">
                        Delete
                    </button>
                </th>
                <th class="py-3 px-2 text-left font-semibold">Coop No.</th>
                <th class="py-3 px-2 text-left font-semibold">Period</th>
                <th class="py-3 px-2 text-left font-semibold">Name</th>
                <th class="py-3 px-2  font-semibold">Entry Fee</th>
                <th class="py-3 px-2  font-semibold">Savings</th>
                <th class="py-3 px-2  font-semibold">Savings Bal.</th>
                <th class="py-3 px-2  font-semibold">Shares</th>
                <th class="py-3 px-2  font-semibold">Shares Bal.</th>
                <th class="py-3 px-2  font-semibold">Loan</th>
                <th class="py-3 px-2  font-semibold">Loan Repayment</th>
                <th class="py-3 px-2  font-semibold">Loan Bal.</th>
                <th class="py-3 px-2  font-semibold">Interest</th>
                <th class="py-3 px-2  font-semibold">Interest Paid</th>
                <th class="py-3 px-2  font-semibold">Unpaid Interest</th>
                <th class="py-3 px-2  font-semibold">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $rowCount = 0; while ($row_status = $status->fetch_assoc()) { $rowCount++; ?>
            <tr class="<?= $rowCount % 2 === 0 ? 'bg-blue-50' : 'bg-white'; ?> hover:bg-blue-100 transition">
                <td data-label="Select" class="py-2 px-2 ">
                    <input name="memberid" type="checkbox"
                        value="<?= htmlspecialchars($row_status['memberid']) . ',' . htmlspecialchars($row_status['Periodid']) ?>" checked>
                </td>
                <td data-label="Coop No." class="py-2 px-2 "><?= htmlspecialchars($row_status['memberid']); ?></td>
                <td data-label="Period" class="py-2 px-2 "><?= htmlspecialchars($row_status['PayrollPeriod']); ?></td>
                <td data-label="Name" class="py-2 px-2  font-semibold uppercase"><?= htmlspecialchars($row_status['namess']); ?></td>
                <td data-label="Entry Fee" class="py-2 px-2  "><?= number_format($row_status['entryFee'] ?? 0, 2, '.', ','); ?></td>
                <td data-label="Savings" class="py-2 px-2  "><?= number_format($row_status['savingsAmount'] ?? 0, 2, '.', ','); ?></td>
                <td data-label="Savings Bal." class="py-2 px-2  "><?= number_format($row_status['savingsBalance'] ?? 0, 2, '.', ','); ?></td>
                <td data-label="Shares" class="py-2 px-2  "><?= isset($row_status['sharesAmount']) ? number_format($row_status['sharesAmount'], 2, '.', ',') : '0.00'; ?></td>
                <td data-label="Shares Bal." class="py-2 px-2  "><?= number_format($row_status['sharesBalance'] ?? 0, 2, '.', ','); ?></td>
                <td data-label="Loan" class="py-2 px-2  "><?= number_format($row_status['loan'] ?? 0, 2, '.', ','); ?></td>
                <td data-label="Loan Repayment" class="py-2 px-2  "><?= number_format($row_status['loanRepayment'] ?? 0, 2, '.', ','); ?></td>
                <td data-label="Loan Bal." class="py-2 px-2  "><?= number_format($row_status['loanBalance'] ?? 0, 2, '.', ','); ?></td>
                <td data-label="Interest" class="py-2 px-2  "><?= number_format($row_status['interest'] ?? 0, 2, '.', ','); ?></td>
                <td data-label="Interest Paid" class="py-2 px-2  "><?= number_format($row_status['InterestPaid'] ?? 0, 2, '.', ','); ?></td>
                <td data-label="Unpaid Interest" class="py-2 px-2  "><?= number_format($row_status['interestBalance'] ?? 0, 2, '.', ','); ?></td>
                <td data-label="Total" class="py-2 px-2  "><?= number_format(round($row_status['total'] ?? 0), 2, '.', ','); ?></td>
            </tr>
            <?php } ?>
            <!-- Totals Row (Show as single card or row) -->
            <tr class="bg-gray-200 font-bold text-base border-t border-gray-300">
                <td class="py-3 px-2 text-blue-700" data-label="Total">Total</td>
                <td colspan="3"></td>
                <td class="py-3 px-2 " data-label="Entry Fee"><?= number_format($row_totalsum['entryFee'] ?? 0, 2, '.', ','); ?></td>
                <td class="py-3 px-2 " data-label="Savings"><?= number_format($row_totalsum['savings'] ?? 0, 2, '.', ','); ?></td>
                <td></td>
                <td class="py-3 px-2 " data-label="Shares"><?= number_format($row_totalsum['shares'] ?? 0, 2, '.', ','); ?></td>
                <td></td>
                <td class="py-3 px-2 " data-label="Loan"><?= number_format($row_totalsum['loan'] ?? 0, 2, '.', ','); ?></td>
                <td class="py-3 px-2 " data-label="Loan Repayment"><?= number_format($row_totalsum['loanrepayments'] ?? 0, 2, '.', ','); ?></td>
                <td></td>
                <td class="py-3 px-2 " data-label="Interest"><?= number_format($row_totalsum['interest'] ?? 0, 2, '.', ','); ?></td>
                <td class="py-3 px-2 " data-label="Interest Paid"><?= number_format($row_totalsum['interestPaid'] ?? 0, 2, '.', ','); ?></td>
                <td></td>
                <td class="py-3 px-2 " data-label="Total"><?= number_format(round($row_totalsum['total'] ?? 0), 2, '.', ','); ?></td>
            </tr>
        </tbody>
    </table>
</div>
<?php
$statusStmt->close();
$totalStmt->close();
?>
