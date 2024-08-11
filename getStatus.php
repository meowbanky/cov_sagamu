<?php require_once('Connections/cov.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($conn_vote, $theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
    {
     
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

$col_status = "-1";
if (isset($_GET['id'])) {
  $col_status = $_GET['id'];
}

$col_period = "-1";
if (isset($_GET['period'])) {
  $col_period = $_GET['period'];
}

//mysql_select_db($database_cov, $cov);
//$query_status = sprintf("SELECT tbl_personalinfo.patientid, concat(tbl_personalinfo.Lname,' , ', tbl_personalinfo.Fname,' ', ifnull( tbl_personalinfo.Mname,'')) as namess, (sum(tlb_mastertransaction.Contribution)+sum(tlb_mastertransaction.withdrawal)) as Contribution, (sum(tlb_mastertransaction.loanAmount)+ sum(tlb_mastertransaction.interest)) as Loan, ((sum(tlb_mastertransaction.loanAmount)+ sum(tlb_mastertransaction.interest))- (sum(tlb_mastertransaction.loanRepayment)+ifnull(sum(tlb_mastertransaction.repayment_bank),0))) as Loanbalance, sum(tlb_mastertransaction.withdrawal) as withdrawal FROM tlb_mastertransaction INNER JOIN tbl_personalinfo ON tbl_personalinfo.patientid = tlb_mastertransaction.memberid where patientid = %s AND tlb_mastertransaction.periodid <= %s GROUP BY patientid", GetSQLValueString($col_status, "text"),GetSQLValueString($col_period, "int"));
//$status = mysql_query($query_status, $cov) or die(mysql_error());
//$row_status = mysql_fetch_assoc($status);
//$totalRows_status = mysql_num_rows($status);


mysqli_select_db($cov,$database_cov);
$query_status = sprintf("SELECT
tbl_personalinfo.memberid,
tlb_mastertransaction.transactionid,
concat(tbl_personalinfo.Lname,' , ', tbl_personalinfo.Fname,' ', ifnull( tbl_personalinfo.Mname,'')) AS namess,
ifnull((Sum(tlb_mastertransaction.loanAmount)),0) AS loan,
ifnull(Sum(tlb_mastertransaction.loanRepayment),0) AS loanrepayments,
ifnull(Sum(tlb_mastertransaction.withdrawal),0) AS withrawals,
((ifnull(Sum(tlb_mastertransaction.loanRepayment),0)+ifnull(sum(tlb_mastertransaction.entryFee),0)+ifnull(sum(tlb_mastertransaction.savings),0)+
ifnull(sum(tlb_mastertransaction.shares),0)+ifnull(sum(tlb_mastertransaction.interestPaid),0))) AS total,
tbpayrollperiods.PayrollPeriod,(ifnull((Sum(tlb_mastertransaction.loanAmount)),0) - ifnull(Sum(tlb_mastertransaction.loanRepayment),0)) as loanBalance,
tlb_mastertransaction.periodid,
ifnull(sum(tlb_mastertransaction.entryFee),0) as entryFee,
ifnull(sum(tlb_mastertransaction.savings),0) as savings,
ifnull(sum(tlb_mastertransaction.shares),0) as shares,
ifnull(sum(tlb_mastertransaction.interestPaid),0) as interestPaid,ifnull(sum(tlb_mastertransaction.interest),0) as interest
FROM
tbl_personalinfo
INNER JOIN tlb_mastertransaction ON tbl_personalinfo.memberid = tlb_mastertransaction.memberid
INNER JOIN tbpayrollperiods ON tbpayrollperiods.Periodid = tlb_mastertransaction.periodid
LEFT JOIN tbl_refund ON tbl_refund.membersid = tbl_personalinfo.memberid AND tbl_refund.periodid = tbpayrollperiods.Periodid
where tbl_personalinfo.memberid = %s AND tlb_mastertransaction.periodid <= %s GROUP BY memberid", GetSQLValueString($cov,$col_status, "text"),GetSQLValueString($cov,$col_period, "int"));
$status = mysqli_query($cov,$query_status) or die(mysql_error());
$row_status = mysqli_fetch_assoc($status);
$totalRows_status = mysqli_num_rows($status);

$query_totalsum = sprintf("SELECT
tbl_personalinfo.memberid,
tlb_mastertransaction.transactionid,
concat(tbl_personalinfo.Lname,' , ', tbl_personalinfo.Fname,' ', ifnull( tbl_personalinfo.Mname,'')) AS namess,
ifnull((Sum(tlb_mastertransaction.loanAmount)),0) AS loan,
ifnull(Sum(tlb_mastertransaction.loanRepayment),0) AS loanrepayments,
ifnull(Sum(tlb_mastertransaction.withdrawal),0) AS withrawals,
((ifnull(Sum(tlb_mastertransaction.loanRepayment),0)+ifnull(sum(tlb_mastertransaction.entryFee),0)+ifnull(sum(tlb_mastertransaction.savings),0)+
ifnull(sum(tlb_mastertransaction.shares),0)+ifnull(sum(tlb_mastertransaction.interestPaid),0))) AS total,
tbpayrollperiods.PayrollPeriod,
tlb_mastertransaction.periodid,
ifnull(sum(tlb_mastertransaction.entryFee),0) as entryFee,
ifnull(sum(tlb_mastertransaction.savings),0) as savings,
ifnull(sum(tlb_mastertransaction.shares),0) as shares,
ifnull(sum(tlb_mastertransaction.interestPaid),0) as interestPaid,ifnull(sum(tlb_mastertransaction.interest),0) as interest
FROM
tbl_personalinfo
INNER JOIN tlb_mastertransaction ON tbl_personalinfo.memberid = tlb_mastertransaction.memberid
INNER JOIN tbpayrollperiods ON tbpayrollperiods.Periodid = tlb_mastertransaction.periodid
LEFT JOIN tbl_refund ON tbl_refund.membersid = tbl_personalinfo.memberid AND tbl_refund.periodid = tbpayrollperiods.Periodid
where tbl_personalinfo.memberid = %s AND tlb_mastertransaction.periodid <= %s GROUP BY memberid", GetSQLValueString($cov,$col_status, "text"),GetSQLValueString($cov,$col_period, "int"));

$totalsum = mysqli_query($cov,$query_totalsum) or die(mysql_error());
$row_totalsum = mysqli_fetch_assoc($totalsum);
$totalRows_totalsum = mysqli_num_rows($totalsum);


mysqli_select_db($cov,$database_cov);
$query_period = sprintf("SELECT tbpayrollperiods.PayrollPeriod, tbpayrollperiods.Periodid FROM tbpayrollperiods WHERE Periodid = %s",GetSQLValueString($cov,$col_period, "int"));
$period = mysqli_query($cov,$query_period) or die(mysql_error());
$row_period = mysqli_fetch_assoc($period);
$totalRows_period = mysqli_num_rows($period);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body><table width="100%" border="1" cellpadding="1" cellspacing="0" class="greyBgdHeader">
                                 <tr class="table_header_new">
                                   <th width="37%" scope="col"><strong>Member's Id</strong></th>
                                   <th width="25%" scope="col">Period</th>
                                   <th width="25%" scope="col">Name</th>
                                   <th width="25%" scope="col">Savings</th>
                                   <th width="25%" scope="col">Shares</th>
                                   <th width="25%" scope="col">Withdrawal</th>
                                   <th width="25%" scope="col">Loan</th>
                                   <th width="25%" scope="col">Loan Repayment</th>
                                   <th width="25%" scope="col">Loan Balance</th>
                                   <th width="25%" scope="col">Interest</th>
                                   <th width="25%" scope="col">Interest Paid</th>
                                   <th width="25%" scope="col">Unpaid Interest</th>
                                   </tr>
                                <?php if($totalRows_status > 0 ) { do { ?>   <tr>
                                  <th align="left" scope="col"><?php echo $row_status['memberid']; ?></th>
                                   <th align="left" scope="col"><?php echo $row_period['PayrollPeriod']; ?></th>
                                   <th align="left" scope="col"><?php echo $row_status['namess']; ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_status['savings'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_status['shares'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_status['withrawals'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_status['loan'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_status['loanrepayments'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_status['loanBalance'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_status['interest'],2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_status['interestPaid'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format(($row_status['interest'])-($row_status['interestPaid']),2); ?></th>
                                   </tr> <?php } while ($row_status = mysqli_fetch_assoc($status));} ?>
                                    <tr>
                                      <th align="left" scope="col">Total</th>
                                      <th align="left" scope="col">&nbsp;</th>
                                      <th align="left" scope="col">&nbsp;</th>
                                      <th align="right" scope="col"><?php echo number_format($row_totalsum['savings'] ,2,'.',','); ?></th>
                                      <th align="right" scope="col"><?php echo number_format($row_totalsum['shares'] ,2,'.',','); ?></th>
                                      <th align="right" scope="col">&nbsp;</th>
                                      <th align="right" scope="col"><?php echo number_format($row_totalsum['loan'] ,2,'.',','); ?></th>
                                      <th align="right" scope="col"><?php echo number_format($row_totalsum['loanrepayments'] ,2,'.',','); ?></th>
                                      <th align="right" scope="col">&nbsp;</th>
                                      <th align="right" scope="col"><?php echo number_format($row_totalsum['interest'],2,'.',','); ?></th>
                                      <th align="right" scope="col"><?php echo number_format($row_totalsum['interestPaid'],2,'.',','); ?></th>
                                      <th align="right" scope="col"><?php echo number_format(($row_totalsum['interest'])-($row_totalsum['interestPaid']),2); ?></th>
                                    </tr> 
                                 
                               </table>
</body>
</html>
<?php
mysqli_free_result($status);
?>
