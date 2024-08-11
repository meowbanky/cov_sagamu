<?php
require_once('Connections/cov.php');
session_start();

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
                $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
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

if (!isset($_SESSION['period'])) {
    $_SESSION['period'] = -1;
}

$col_contributions = "-1";
if (isset($_GET['id'])) {
    $col_contributions = $_GET['id'];
}

$period = $_SESSION['period'];
mysqli_select_db($cov, $database_cov);

$query_contributions = sprintf("SELECT SUM(tbl_contributions.contribution) AS total, SUM(tbl_contributions.special_savings) AS special_savings, 
                            sum(tbl_contributions.loan) AS loan, tbl_contributions.membersid 
                            FROM tbl_contributions WHERE tbl_contributions.membersid = %s AND periodid = %s GROUP BY tbl_contributions.membersid, tbl_contributions.periodid",
                            GetSQLValueString($cov, $col_contributions, "text"),
                            GetSQLValueString($cov, $period, "text")
                            );

$contributions = mysqli_query($cov, $query_contributions) or die(mysqli_error($cov));
$row_contributions = mysqli_fetch_assoc($contributions);
$totalRows_contributions = mysqli_num_rows($contributions);

$query_grandTotal = sprintf("SELECT (SUM(tbl_contributions.contribution) + SUM(tbl_contributions.special_savings)) AS total
    FROM tbl_contributions 
    WHERE periodid = %s",
    GetSQLValueString($cov, $period, "text")
);

$grand_total = mysqli_query($cov, $query_grandTotal) or die(mysqli_error($cov));
$row_grand_total = mysqli_fetch_assoc($grand_total);
$totalRows_grand_total = mysqli_num_rows($grand_total);

$col_balances = "-1";
if (isset($_GET['id'])) {
    $col_balances = $_GET['id'];
}

$query_balances = sprintf("SELECT (SUM(tlb_mastertransaction.loanAmount) - SUM(tlb_mastertransaction.loanRepayment)) AS loanbalance 
    FROM tlb_mastertransaction 
    WHERE memberid = %s 
    GROUP BY tlb_mastertransaction.memberid",
    GetSQLValueString($cov, $col_balances, "text")
);

$balances = mysqli_query($cov, $query_balances) or die(mysqli_error($cov));
$row_balances = mysqli_fetch_assoc($balances);
$totalRows_balances = mysqli_num_rows($balances);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Untitled Document</title>
    <script language="javascript">
        function number_format(number, decimals, dec_point, thousands_sep) {
            number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
            var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                s = '',
                toFixedFix = function (n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        }
    </script>
</head>
<body>
<table width="97%" align="center" cellpadding="4" cellspacing="0">
    <tbody>
    <?php if ($totalRows_contributions > 0) { // Show if recordset not empty ?>
        <tr valign="top" align="left">
            <td class="greyBgd" valign="middle" align="right" height="35">Contribution</td>
            <td class="greyBgd" valign="middle" align="left"><strong><?php echo number_format($row_contributions['total'], 2, '.', ','); ?></strong></td>
        </tr>
        <tr valign="top" align="left">
            <td class="greyBgd" valign="middle" align="right" height="35">Special Savings</td>
            <td class="greyBgd" valign="middle" align="left"><strong><?php echo number_format($row_contributions['special_savings'], 2, '.', ','); ?></strong></td>
        </tr>
        <tr valign="top" align="left">
            <td class="greyBgd" valign="middle" align="right" height="35">Loan Balance:</td>
            <td class="greyBgd" valign="middle" align="left"><strong><?php echo number_format($row_balances['loanbalance'], 2, '.', ','); ?>
                    <input name="memberid" type="hidden" id="memberid" value="<?php echo $row_contributions['membersid']; ?>" />
                </strong></td>
        </tr>
        <tr valign="top" align="left">
            <td class="greyBgd" valign="middle" align="right" height="35"><strong>Grand Total:</strong></td>
            <td class="greyBgd" valign="middle" align="left"><strong><?php echo number_format($row_grand_total['total'], 2, '.', ','); ?></strong></td>
        </tr>
    <?php } // Show if recordset not empty ?>
    </tbody>
</table>
</body>
</html>
<?php
mysqli_free_result($contributions);
mysqli_free_result($balances);
?>
