<?php require_once('Connections/hms.php'); ?>
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
mysql_select_db($database_hms, $hms);
$query_perod = sprintf("SELECT * from tbpayrollperiods where Periodid <= %s", GetSQLValueString($period_perod, "int"));
$perod = mysql_query($query_perod, $hms) or die(mysql_error());
$row_perod = mysql_fetch_assoc($perod);
$totalRows_perod = mysql_num_rows($perod);
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
                                   <th width="15%" scope="col">Contribution</th>
                                   <th width="15%" scope="col">Loan</th>
                                   <th width="15%" scope="col">Repayment Amount</th>
                                   <th width="15%" scope="col">Repayment via Bank</th>
                                   <th width="15%" scope="col">Loan Balance</th>
                                   <th width="18%" scope="col">Withdrawal</th>
                                   </tr>
                                <?php do { ?>   <tr>
                                
                                <?php 
								mysql_select_db($database_hms, $hms);
$query_status = sprintf("SELECT tbl_personalinfo.patientid, concat(tbl_personalinfo.Lname,' , ', tbl_personalinfo.Fname,' ', ifnull( tbl_personalinfo.Mname,'')) as namess, (sum(tlb_mastertransaction.Contribution)+sum(tlb_mastertransaction.withdrawal)) as Contribution, (sum(tlb_mastertransaction.loanAmount)+ sum(tlb_mastertransaction.interest)) as Loan, ((sum(tlb_mastertransaction.loanAmount)+ sum(tlb_mastertransaction.interest))- (sum(tlb_mastertransaction.loanRepayment)+ifnull(sum(tlb_mastertransaction.repayment_bank),0))) as Loanbalance, sum(tlb_mastertransaction.repayment_bank) as bank, sum(tlb_mastertransaction.withdrawal) as withdrawal, (tlb_mastertransaction.loanRepayment) as loanRepay FROM tlb_mastertransaction INNER JOIN tbl_personalinfo ON tbl_personalinfo.patientid = tlb_mastertransaction.memberid where patientid = %s AND tlb_mastertransaction.periodid <= ".$row_perod['Periodid']." GROUP BY patientid", GetSQLValueString($col_status, "text"));
$status = mysql_query($query_status, $hms) or die(mysql_error());
$row_status = mysql_fetch_assoc($status);
$totalRows_status = mysql_num_rows($status);
								
								mysql_select_db($database_hms, $hms);
$query_loan = sprintf("SELECT (ifnull(sum(loanamount),0)+ifnull(sum(interest),0)) as loanamount,(sum(tlb_mastertransaction.loanRepayment)) as loanRepay,ifnull(sum(tlb_mastertransaction.repayment_bank),0) as bank from tlb_mastertransaction WHERE memberid = %s AND tlb_mastertransaction.periodid = ".$row_perod['Periodid']."", GetSQLValueString($col_status, "text"));
$loan = mysql_query($query_loan, $hms) or die(mysql_error());
$row_loan = mysql_fetch_assoc($loan);
$totalRows_loan = mysql_num_rows($loan);
								
								
								?>
                                
                                
                                
                                   <th align="left" scope="col"><?php echo $row_status['patientid']; ?></th>
                                   <th align="left" scope="col"><?php echo $row_status['namess']; ?></th>
                                   <th align="right" scope="col"><?php echo $row_perod['PayrollPeriod']; ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_status['Contribution'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_loan['loanamount'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_loan['loanRepay'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_loan['bank'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_status['Loanbalance'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_status['withdrawal'] ,2,'.',','); ?></th>
                                   </tr> <?php } while ($row_perod = mysql_fetch_assoc($perod)); ?>
                               </table>
</body>
</html>
<?php
mysql_free_result($status);

mysql_free_result($perod);
?>
