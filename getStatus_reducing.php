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
if (isset($_GET['period1'])) {
  $col_period = $_GET['period1'];
}



$period_perod = "-1";
if (isset($_GET['period1'])) {
  $period_perod = $_GET['period1'];
}
mysqli_select_db($cov,$database_cov);
$query_perod = sprintf("SELECT * from tbpayrollperiods where Periodid <= %s", GetSQLValueString($cov,$period_perod, "int"));
$perod = mysqli_query($cov,$query_perod) or die(mysql_error());
$row_perod = mysqli_fetch_assoc($perod);
$totalRows_perod = mysqli_num_rows($perod);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
</head>

<body><table width="100%" border="1" class="greyBgdHeader">
                                 <tr class="table_header_new">
                                   <th width="14%" scope="col"><strong>Staff ID</strong></th>
                                   <th width="23%" scope="col">Name</th>
                                   <th width="15%" scope="col">Month</th>
                                   <th width="15%" scope="col">Entry Fee</th>
                                   <th width="15%" scope="col">Shares</th>
                                   <th width="15%" scope="col">Savings</th>
                                   <th width="15%" scope="col">Loan</th>
                                   <th width="15%" scope="col">Loan Repayment</th>
                                   <th width="15%" scope="col">Loan Balance</th>
                                   <th width="15%" scope="col">Interest</th>
                                   <th width="15%" scope="col">Interest Paid</th>
                                   <th width="15%" scope="col">Unpaid Interest</th>
                                   </tr>
                                <?php do { ?>   <tr>
                                
                                <?php 
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
LEFT JOIN tbl_refund ON tbl_refund.membersid = tbl_personalinfo.memberid AND tbl_refund.periodid = tbpayrollperiods.Periodid where tbl_personalinfo.memberid = %s AND tlb_mastertransaction.periodid <= ".$row_perod['Periodid']." GROUP BY tbl_personalinfo.memberid", GetSQLValueString($cov,$col_status, "text"));
$status = mysqli_query($cov,$query_status) or die(mysql_error());
$row_status = mysqli_fetch_assoc($status);
$totalRows_status = mysqli_num_rows($status);
								
mysqli_select_db($cov,$database_cov);
$query_loan = sprintf("SELECT (ifnull(sum(loanamount),0)) as loanamount,(sum(tlb_mastertransaction.loanRepayment)) as loanRepay,ifnull(sum(tlb_mastertransaction.repayment_bank),0) as bank from tlb_mastertransaction WHERE memberid = %s AND tlb_mastertransaction.periodid = ".$row_perod['Periodid']."", GetSQLValueString($cov,$col_status, "text"));
$loan = mysqli_query($cov,$query_loan) or die(mysql_error());
$row_loan = mysqli_fetch_assoc($loan);
$totalRows_loan = mysqli_num_rows($loan);
								
		if($totalRows_status > 0){						
								?>
                                
                                
                                
                                   <th align="left" scope="col"><?php echo $row_status['memberid']; ?></th>
                                   <th align="left" scope="col"><?php echo $row_status['namess']; ?></th>
                                   <th align="right" scope="col"><?php echo $row_perod['PayrollPeriod']; ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_status['entryFee'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_status['shares'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_status['savings'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_loan['loanamount'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_loan['loanRepay'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_status['loan']-$row_status['loanrepayments'],2); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_status['interest'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_status['interestPaid'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_status['interestPaid']-$row_status['interest'] ,2,'.',','); ?></th>
                                   </tr> <?php }
                                   } while ($row_perod = mysqli_fetch_assoc($perod)); ?>
                               </table>
</body>
</html>
<?php
mysqli_free_result($status);

mysqli_free_result($perod);
?>
