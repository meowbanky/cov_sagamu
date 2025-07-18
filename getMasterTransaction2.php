<?php
require_once('Connections/cov.php');
session_start();

if (!isset($_SESSION['UserID'])) {
    header("Location: index.php");
    exit();
}

if (!function_exists("GetSQLValueString")) {
    function GetSQLValueString($conn_vote, $theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") {
        $theValue = function_exists("mysqli_real_escape_string") ? mysqli_real_escape_string($conn_vote, $theValue) : mysqli_escape_string($conn_vote, $theValue);

        switch ($theType) {
            case "text":
                $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
                break;
            case "long":
            case "int":
                $theValue = ($theValue != "") ? intval($theValue) : "NULL";
                break;
            case "double":
                $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
                break;
            case "date":
                $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
                break;
            case "defined":
                $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
                break;
        }
        return $theValue;
    }
}

mysqli_select_db($cov,$database_cov);
$periodFrom_status = isset($_GET['periodfrom']) ? $_GET['periodfrom'] : "-1";
$periodTo_status = isset($_GET['periodTo']) ? $_GET['periodTo'] : "-1";
$id_status = isset($_GET['id']) ? $_GET['id'] : "-1";

$query_status = "SELECT
    ANY_VALUE(tbl_personalinfo.memberid) AS memberid,
    ANY_VALUE(tlb_mastertransaction.transactionid) AS transactionid,
    CONCAT(tbl_personalinfo.Lname, ' , ', tbl_personalinfo.Fname, ' ', IFNULL(tbl_personalinfo.Mname, '')) AS namess,
    ANY_VALUE(IFNULL(SUM(tlb_mastertransaction.loanAmount), 0) )AS loan,
    ANY_VALUE(IFNULL(SUM(tlb_mastertransaction.loanRepayment), 0)) AS loanrepayments,
    ANY_VALUE(IFNULL(SUM(tlb_mastertransaction.withdrawal), 0)) AS withdrawals,
    (IFNULL(SUM(tlb_mastertransaction.loanRepayment), 0) + IFNULL(SUM(tlb_mastertransaction.entryFee), 0) + IFNULL(SUM(tlb_mastertransaction.savings), 0) + 
    IFNULL(SUM(tlb_mastertransaction.shares), 0) + IFNULL(SUM(tlb_mastertransaction.interestPaid), 0)) AS total,
    tbpayrollperiods.PayrollPeriod,
    tlb_mastertransaction.periodid,
    IFNULL(SUM(tlb_mastertransaction.entryFee), 0) AS entryFee,
    IFNULL(SUM(tlb_mastertransaction.savings), 0) AS savings,
    IFNULL(SUM(tlb_mastertransaction.shares), 0) AS shares,
    IFNULL(SUM(tlb_mastertransaction.interestPaid), 0) AS interestPaid,
    IFNULL(SUM(tlb_mastertransaction.interest), 0) AS interest
FROM
    tbl_personalinfo
INNER JOIN
    tlb_mastertransaction ON tbl_personalinfo.memberid = tlb_mastertransaction.memberid
INNER JOIN
    tbpayrollperiods ON tbpayrollperiods.Periodid = tlb_mastertransaction.periodid
LEFT JOIN
    tbl_refund ON tbl_refund.membersid = tbl_personalinfo.memberid AND tbl_refund.periodid = tbpayrollperiods.Periodid
WHERE
    tbpayrollperiods.Periodid BETWEEN $periodFrom_status AND $periodTo_status ";

if ($id_status != "") {
    $query_status .= " AND tbl_personalinfo.memberid = $id_status ";
}

$query_status .= " GROUP BY tlb_mastertransaction.periodid, tbl_personalinfo.memberid";

//echo $query_status;
$status = mysqli_query($cov, $query_status) or die(mysqli_error($cov));
$row_status = mysqli_fetch_assoc($status);
$totalRows_status = mysqli_num_rows($status);

$query_totalsum = "SELECT
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
FROM
    tbl_personalinfo
INNER JOIN
    tlb_mastertransaction ON tbl_personalinfo.memberid = tlb_mastertransaction.memberid
INNER JOIN
    tbpayrollperiods ON tbpayrollperiods.Periodid = tlb_mastertransaction.periodid
LEFT JOIN
    tbl_refund ON tbl_refund.membersid = tbl_personalinfo.memberid AND tbl_refund.periodid = tbpayrollperiods.Periodid
WHERE
    tbpayrollperiods.Periodid BETWEEN $periodFrom_status AND $periodTo_status ";

if ($id_status != "") {
    $query_totalsum .= " AND tlb_mastertransaction.memberid = $id_status ";
}

$totalsum = mysqli_query($cov, $query_totalsum) or die(mysqli_error($cov));
$row_totalsum = mysqli_fetch_assoc($totalsum);
$totalRows_totalsum = mysqli_num_rows($totalsum);
?>
<input type="hidden" name="filename" id="filename" value="<?php echo $_GET['filename']; ?>">
 <div class="flex flex-row justify-end gap-2 mb-2">
    <div class="p-2 bg-blue-500 text-white font-bold rounded"><button name="exportpdf" id="exportpdf" type="button">Export PDF</button> </div>
    <div class="p-2 bg-purple-500 text-white font-bold rounded""><button name="exportexcel" id="exportexcel" type="button">Export Excel</button> </div>
</div>
<div>
<table class="table-auto border-collapse border border-slate-500 text-sm min-w-max" id="sample_1">
    <thead>
    <tr class="table_header_new bg-blue-500 text-white">
        <th  class = "border border-slate-600" scope="col"><span class="m-2 block">Select</span> <button type="button" id="deleteT" name="deleteT" class="hover:bg-red-300 text-white bg-red-500 p-2 font-bold rounded">Delete</button></th>
        <th  class = "border border-slate-600" scope="col"><strong>Coop No.</strong></th>
        <th  class = "border border-slate-600" scope="col">Period</th>
        <th  class = "border border-slate-600" scope="col">Name</th>
        <th  class = "border border-slate-600" scope="col">Entry Fee</th>
        <th  class = "border border-slate-600" scope="col">Savings</th>
        <th  class = "border border-slate-600" scope="col">Savings Balance</th>
        <th  class = "border border-slate-600" scope="col">Shares</th>
        <th  class = "border border-slate-600" scope="col">Shares Balance</th>
        <th  class = "border border-slate-600" scope="col">Loan Balance</th>
        <th  class = "border border-slate-600" scope="col">Loan Repayment</th>
        <th  class = "border border-slate-600" scope="col">Loan</th>
        <th  class = "border border-slate-600" scope="col">Interest</th>
        <th  class = "border border-slate-600" scope="col">Interest Paid</th>
        <th  class = "border border-slate-600" scope="col">Unpaid Interest</th>
        <th  class = "border border-slate-600" scope="col">
            Total
        </th>
    </tr>
    </thead>
    <tbody>
    <?php if ($totalRows_status > 0) {
        do {
            $query_balance = sprintf("SELECT
                        tbl_personalinfo.memberid,
                        ANY_VALUE(tlb_mastertransaction.transactionid) AS transactionid,
                        CONCAT(tbl_personalinfo.Lname, ' , ', tbl_personalinfo.Fname, ' ', IFNULL(tbl_personalinfo.Mname, '')) AS namess,
                        IFNULL(SUM(tlb_mastertransaction.loanAmount), 0) AS loan,
                        IFNULL(SUM(tlb_mastertransaction.loanRepayment), 0) AS loanrepayments,
                        IFNULL(SUM(tlb_mastertransaction.withdrawal), 0) AS withdrawals,
                        (IFNULL(SUM(tlb_mastertransaction.loanRepayment), 0) + IFNULL(SUM(tlb_mastertransaction.entryFee), 0) + IFNULL(SUM(tlb_mastertransaction.savings), 0) + 
                        IFNULL(SUM(tlb_mastertransaction.shares), 0) + IFNULL(SUM(tlb_mastertransaction.interestPaid), 0)) AS total,
                        ANY_VALUE(tbpayrollperiods.PayrollPeriod) AS PayrollPeriod,
                        (IFNULL(SUM(tlb_mastertransaction.loanAmount), 0) - IFNULL(SUM(tlb_mastertransaction.loanRepayment), 0)) AS loanBalance,
                        ANY_VALUE(tlb_mastertransaction.periodid) AS periodid,
                        IFNULL(SUM(tlb_mastertransaction.entryFee), 0) AS entryFee,
                        IFNULL(SUM(tlb_mastertransaction.savings), 0) AS savings,
                        IFNULL(SUM(tlb_mastertransaction.shares), 0) AS shares,
                        (IFNULL(SUM(tlb_mastertransaction.interest), 0) - IFNULL(SUM(tlb_mastertransaction.interestPaid), 0)) AS UnpaidInterest,
                        IFNULL(SUM(tlb_mastertransaction.interestPaid), 0) AS interestPaid,
                        IFNULL(SUM(tlb_mastertransaction.interest), 0) AS interest
                    FROM
                        tbl_personalinfo
                    INNER JOIN
                        tlb_mastertransaction ON tbl_personalinfo.memberid = tlb_mastertransaction.memberid
                    INNER JOIN
                        tbpayrollperiods ON tbpayrollperiods.Periodid = tlb_mastertransaction.periodid
                    LEFT JOIN
                        tbl_refund ON tbl_refund.membersid = tbl_personalinfo.memberid AND tbl_refund.periodid = tbpayrollperiods.Periodid
                    WHERE
                        tbl_personalinfo.memberid = %s AND tlb_mastertransaction.periodid <= %s
                    GROUP BY
                        memberid", GetSQLValueString($cov, $row_status['memberid'], "text"), GetSQLValueString($cov, $row_status['periodid'], "int"));

            $balance = mysqli_query($cov, $query_balance) or die(mysqli_error($cov));
            $row_balance = mysqli_fetch_assoc($balance);
            ?>
            <tr>
                <td class = "border border-slate-600"><?php if ($totalRows_status > 0) { ?><input name="memberid" type="checkbox"  value="<?php echo $row_status['memberid']; ?>,<?php echo $row_status['periodid']; ?>" checked="checked" /><?php } ?></td>
                <td class = "border border-slate-600"><?php echo $row_status['memberid']; ?></td>
                <td class = "border border-slate-600"><?php echo $row_status['PayrollPeriod']; ?></td>
                <td class = "border border-slate-600 text-left uppercase pl-2"><?php echo $row_status['namess']; ?></td>
                <td class = "border border-slate-600 text-right"><?php echo number_format($row_status['entryFee'], 2, '.', ','); ?></td>
                <td class = "border border-slate-600 text-right"><?php echo number_format($row_status['savings'], 2, '.', ','); ?></td>
                <td class = "border border-slate-600 text-right"><?php echo number_format($row_balance['savings'], 2, '.', ','); ?></td>
                <td class = "border border-slate-600 text-right"><?php echo number_format($row_status['shares'], 2, '.', ','); ?></td>
                <td class = "border border-slate-600 text-right"><?php echo number_format($row_balance['shares'], 2, '.', ','); ?></td>
                <td class = "border border-slate-600 text-rigt"><?php echo number_format($row_balance['loanBalance'], 2, '.', ','); ?></td>
                <td class = "border border-slate-600 text-right"><?php echo number_format($row_status['loanrepayments'], 2, '.', ','); ?></td>
                <td class = "border border-slate-600 text-right"><?php echo number_format($row_status['loan'], 2, '.', ','); ?></td>
                <td class = "border border-slate-600 text-right"><?php echo number_format($row_status['interest'], 2, '.', ','); ?></td>
                <td class = "border border-slate-600 text-right"><?php echo number_format($row_status['interestPaid'], 2, '.', ','); ?></td>
                <td class = "border border-slate-600 text-right"><?php echo number_format($row_balance['UnpaidInterest'], 2, '.', ','); ?></td>
                <td class = "border border-slate-600 text-right"><?php echo number_format(round($row_status['total']), 2, '.', ','); ?></td>
            </tr>
        <?php } while ($row_status = mysqli_fetch_assoc($status));
    } ?>
    <tr>
        <td>Total</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td align="right"><?php echo number_format($row_totalsum['entryFee'], 2, '.', ','); ?></td>
        <td align="right"><?php echo number_format($row_totalsum['savings'], 2, '.', ','); ?></td>
        <td>&nbsp;</td>
        <td align="right"><?php echo number_format($row_totalsum['shares'], 2, '.', ','); ?></td>
        <td>&nbsp;</td>
        <td align="right"><?php echo number_format($row_totalsum['loan'], 2, '.', ','); ?></td>
        <td align="right"><?php echo number_format($row_totalsum['loanrepayments'], 2, '.', ','); ?></td>
        <td>&nbsp;</td>
        <td align="right"><?php echo number_format($row_totalsum['interest'], 2, '.', ','); ?></td>
        <td align="right"><?php echo number_format($row_totalsum['interestPaid'], 2, '.', ','); ?></td>
        <td>&nbsp;</td>
        <td align="right"><?php echo number_format(round($row_totalsum['total']), 2, '.', ','); ?></td>
    </tr>
    </tbody>
</table>
</div>

<?php
mysqli_free_result($status);
mysqli_free_result($totalsum);
?>
