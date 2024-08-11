<?php require_once('Connections/cov.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

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

$period_status = "-1";
if (isset($_GET['periodid'])) {
  $period_status = $_GET['periodid'];
}
mysql_select_db($database_cov, $cov);
$query_status = sprintf("SELECT tbl_personalinfo.patientid, concat(tbl_personalinfo.Lname,' , ', tbl_personalinfo.Fname,' ', ifnull( tbl_personalinfo.Mname,'')) AS namess, (sum(tlb_mastertransaction.withdrawal)) AS Contribution, (sum(tlb_mastertransaction.loanAmount)+ sum(tlb_mastertransaction.interest)) AS Loan, ((sum(tlb_mastertransaction.loanAmount)+ sum(tlb_mastertransaction.interest))- (sum(tlb_mastertransaction.loanRepayment)+ifnull(sum(tlb_mastertransaction.repayment_bank),0))) as Loanbalance, Sum(tlb_mastertransaction.withdrawal) AS withdrawal, tlb_mastertransaction.loanRepayment AS loanRepay,sum(tlb_mastertransaction.repayment_bank) as bank FROM tlb_mastertransaction INNER JOIN tbl_personalinfo ON tbl_personalinfo.patientid = tlb_mastertransaction.memberid where tlb_mastertransaction.periodid <= %s GROUP BY patientid order by tbl_personalinfo.ordered_id", GetSQLValueString($period_status, "text"));
$status = mysql_query($query_status, $cov) or die(mysql_error());
$row_status = mysql_fetch_assoc($status);
$totalRows_status = mysql_num_rows($status);

mysql_select_db($database_cov, $cov);
$query_Period = sprintf("SELECT tbpayrollperiods.PayrollPeriod, tbpayrollperiods.Periodid FROM tbpayrollperiods where tbpayrollperiods.Periodid = %s", GetSQLValueString($period_status, "int"));
$Period = mysql_query($query_Period, $cov) or die(mysql_error());
$row_Period = mysql_fetch_assoc($Period);
$totalRows_Period = mysql_num_rows($Period);

?>


<table width="100%" border="1" class="greyBgdHeader">
  <tr class="table_header_new">
    <th scope="col">Name</th>
    <th scope="col">Coop No.</th>
    <th scope="col">Period</th>
    <th scope="col">Contribution</th>
    <th scope="col">Shares</th>
    <th scope="col">Savings</th>
    <th scope="col">Loan Repayment</th>
    <th scope="col">Repayment via Bank</th>
    <th scope="col">Loan</th>
    <th scope="col">Loan Balance</th>
  </tr>
  <?php do { ?>
  
  <?php 
  mysql_select_db($database_cov, $cov);
$query_monthTransaction = sprintf("SELECT sum(tlb_mastertransaction.shares) as shares,sum(tlb_mastertransaction.savings) as savings, tlb_mastertransaction.loanRepayment ,(sum(tlb_mastertransaction.loanAmount)) AS Loan , (tlb_mastertransaction.shares + tlb_mastertransaction.loanRepayment +sum(tlb_mastertransaction.savings)+ ifnull(sum(tlb_mastertransaction.interestpaid),0)) as totalContri,ifnull(sum(tlb_mastertransaction.interest),0) as interest,ifnull(sum(tlb_mastertransaction.interestpaid),0) as interestPaid FROM tlb_mastertransaction WHERE tlb_mastertransaction.periodid = %s AND tlb_mastertransaction.memberid = %s", GetSQLValueString($period_status, "int"),GetSQLValueString($row_status['patientid'], "text"));
$monthTransaction = mysql_query($query_monthTransaction, $cov) or die(mysql_error());
$row_monthTransaction = mysql_fetch_assoc($monthTransaction);
$totalRows_monthTransaction = mysql_num_rows($monthTransaction);
  
  ?>
  
    <tr>
      <td><?php echo $row_status['namess']; ?></td>
      <td><?php echo $row_status['patientid']; ?></td>
      <td><?php echo $row_Period['PayrollPeriod']; ?></td>
      <td><?php echo number_format( $row_monthTransaction['totalContri'],2) ; ?></td>
      <td><?php  echo number_format($row_monthTransaction['shares'] ,2); ?></td>
      <td><?php  echo number_format($row_monthTransaction['savings'] ,2); ?></td>
      <td><?php echo number_format( $row_monthTransaction['loanRepayment'],2) ;?></td>
      <td><?php echo number_format( $row_monthTransaction['bank'],2) ;?></td>
      <td><?php echo number_format( $row_monthTransaction['Loan'],2) ;?></td>
      <td><?php echo number_format($row_status['Loanbalance'],2); ?></td>
    </tr>
    <?php } while ($row_status = mysql_fetch_assoc($status)); ?>
</table>


<?php
mysql_free_result($status);

mysql_free_result($Period);

mysql_free_result($monthTransaction);
?>
