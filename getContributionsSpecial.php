<?php require_once('Connections/cov.php'); ?>
<?php
session_start();

if (!function_exists("GetSQLValueString")) {
    function GetSQLValueString($cov, $theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
    {
        $theValue = mysqli_real_escape_string($cov, $theValue);

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

if (!isset($_SESSION['period'])){
	$_SESSION['period'] = -1;
}

$col_contributions = "-1";
if (isset($_GET['id'])) {
  $col_contributions = $_GET['id'];
}

mysqli_select_db($cov, $database_cov);

// Contribution Query
$query_contributions = sprintf("SELECT sum(tbl_specialcontributions.contribution) as total, tbl_specialcontributions.loan, tbl_specialcontributions.membersid  FROM tbl_specialcontributions WHERE tbl_specialcontributions.membersid = %s and periodid = %s group by membersid, periodid ", 
    GetSQLValueString($cov, $col_contributions, "text"),
    GetSQLValueString($cov, $_SESSION['period'], "text"));
$contributions = mysqli_query($cov, $query_contributions) or die(mysqli_error($cov));
$row_contributions = mysqli_fetch_assoc($contributions);
$totalRows_contributions = mysqli_num_rows($contributions);

// Grand Total Query
$query_grandTotal = sprintf("SELECT (sum(tbl_specialcontributions.contribution)) as total FROM tbl_specialcontributions WHERE periodid = %s",
    GetSQLValueString($cov, $_SESSION['period'], "text"));
$grand_total = mysqli_query($cov, $query_grandTotal) or die(mysqli_error($cov));
$row_grand_total = mysqli_fetch_assoc($grand_total);

// Balances Query
$col_balances = $col_contributions; // Reuse escaping
$query_balances = sprintf("SELECT ((sum(tlb_mastertransaction.loanAmount)) - sum(tlb_mastertransaction.loanRepayment)) as loanbalance FROM tlb_mastertransaction WHERE memberid = %s ", 
    GetSQLValueString($cov, $col_balances, "text"));
$balances = mysqli_query($cov, $query_balances) or die(mysqli_error($cov));
$row_balances = mysqli_fetch_assoc($balances);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Contributions</title>
</head>
<body>
<table width="97%" align="center" cellpadding="4" cellspacing="0">
  <tbody>
  <?php if ($totalRows_contributions > 0) { ?>
    <tr valign="top" align="left">
      <td class="greyBgd" valign="middle" align="right" height="35">Contribution</td>
        <td class="greyBgd" valign="middle" align="left"><strong><?php echo number_format($row_contributions['total'] ,2,'.',','); ?></strong></td>
    </tr>
    <tr valign="top" align="left">
      <td class="greyBgd" valign="middle" align="right" height="35">Loan Balance:</td>
      <td class="greyBgd" valign="middle" align="left"><strong><?php echo number_format($row_balances['loanbalance'] ,2,'.',','); ?>
        <input name="memberid" type="hidden" id="memberid" value="<?php echo $row_contributions['membersid']; ?>" />
      </strong></td>
    </tr>
    <tr valign="top" align="left">
      <td class="greyBgd" valign="middle" align="right" height="35"><strong>Grand Total:</strong></td>
      <td class="greyBgd" valign="middle" align="left"><strong><?php echo number_format($row_grand_total['total'] ,2,'.',','); ?></strong></td>
    </tr>
    <?php } ?>
  </tbody>
</table>
</body>
</html>
<?php
mysqli_free_result($contributions);
mysqli_free_result($grand_total);
mysqli_free_result($balances);
?>
