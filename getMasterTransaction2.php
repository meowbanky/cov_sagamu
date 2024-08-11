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
//$_GET['id'] = 102;

//$periodFrom_status = 4;

$periodFrom_status = "-1";
if (isset($_GET['periodfrom'])) {
  $periodFrom_status = $_GET['periodfrom'];
}
//$periodTo_status = "4";
if (isset($_GET['periodTo'])) {
  $periodTo_status = $_GET['periodTo'];
}
$id_status = "-1";
if (isset($_GET['id'])) {
  $id_status = $_GET['id'];
}

if ($_GET['id']==""){
	mysql_select_db($database_cov, $cov);
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
LEFT JOIN tbl_refund ON tbl_refund.membersid = tbl_personalinfo.memberid AND tbl_refund.periodid = tbpayrollperiods.Periodid
WHERE tbpayrollperiods.Periodid BETWEEN %s AND %s GROUP BY tlb_mastertransaction.periodid,tbl_personalinfo.memberid", 
GetSQLValueString($periodFrom_status, "int"),GetSQLValueString($periodTo_status, "int"));	
	}else{
mysql_select_db($database_cov, $cov);
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
LEFT JOIN tbl_refund ON tbl_refund.membersid = tbl_personalinfo.memberid AND tbl_refund.periodid = tbpayrollperiods.Periodid
WHERE tbpayrollperiods.Periodid BETWEEN %s AND %s AND tbl_personalinfo.memberid = %s GROUP BY tlb_mastertransaction.periodid,tbl_personalinfo.memberid", 
GetSQLValueString($periodFrom_status, "int"),GetSQLValueString($periodTo_status, "int"),GetSQLValueString($id_status, "text"));
	}
$status = mysql_query($query_status, $cov) or die(mysql_error());
$row_status = mysql_fetch_assoc($status);
$totalRows_status = mysql_num_rows($status);



if ($_GET['id']==""){
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
WHERE tbpayrollperiods.Periodid BETWEEN %s AND %s ", 
GetSQLValueString($periodFrom_status, "int"),GetSQLValueString($periodTo_status, "int"));	
	}else{
mysql_select_db($database_cov, $cov);
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
WHERE tbpayrollperiods.Periodid BETWEEN %s AND %s AND tlb_mastertransaction.memberid = %s ", 
GetSQLValueString($periodFrom_status, "int"),GetSQLValueString($periodTo_status, "int"),GetSQLValueString($id_status, "text"));
	}
$totalsum = mysql_query($query_totalsum, $cov) or die(mysql_error());
$row_totalsum = mysql_fetch_assoc($totalsum);
$totalRows_totalsum = mysql_num_rows($totalsum);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script src="table/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="table/datatables.min.css"/>
 
<script type="text/javascript" src="table/pdfmake.min.js"></script>
<script type="text/javascript" src="table/vfs_fonts.js"></script>
<script type="text/javascript" src="table/datatables.min.js"></script>



<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body><table width="100%" border="1" cellpadding="1" cellspacing="0" id="table_id" class="display">
                               <thead>
                                 <tr class="table_header_new">
                                   <th colspan="14" scope="col">&nbsp;</th>
                                   <th scope="col"><div class="top-panel">
      <div class="btn-group">
        <button type="button" class="btn btn-primary btn-lg dropdown-toggle" data-toggle="dropdown">Export to <span class="caret"></span></button>
        <ul class="dropdown-menu" role="menu">
          <li><a onclick="exportAll('csv');" href="javascript://">CSV</a></li>
          <li><a onclick="exportAll('txt');" href="javascript://">TXT</a></li>
          <li><a onclick="exportAll('xls');" href="javascript://">XLS</a></li>
</ul>
      </div>
  	</div></th>
                                   <th scope="col"><input name="button" type="button" class="formbutton" id="button" onClick="deletetrans()" value="Delete"></th>
                                 </tr>
                                 <tr class="table_header_new">
                                   <th width="37%" scope="col">Select</th>
                                   <th width="37%" scope="col"><strong>Member's Id</strong></th>
                                   <th width="25%" scope="col">Period</th>
                                   <th width="25%" scope="col">Name</th>
                                   <th width="25%" scope="col">Entry Fee</th>
                                   <th width="25%" scope="col">Savings</th>
                                   <th width="25%" scope="col">Savings Balance</th>
                                   <th width="25%" scope="col">Shares</th>
                                   <th width="25%" scope="col">Shares Balance</th>
                                   <th width="25%" scope="col">Loan Balance</th>
                                   <th width="25%" scope="col">Loan Repayment</th>
                                   <th width="25%" scope="col">Loan</th>
                                   <th width="25%" scope="col">Interest</th>
                                   <th width="25%" scope="col">Interest Paid</th>
                                   <th width="25%" scope="col">Unpaid Interest</th>
                                   <th width="25%" scope="col">Total</th>
                                   </tr>
                                   </thead>
                                   <tbody>
                                <?php do { $query_balance = sprintf("SELECT
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
ifnull(sum(tlb_mastertransaction.shares),0) as shares,(ifnull(sum(tlb_mastertransaction.interest),0) - ifnull(sum(tlb_mastertransaction.interestPaid),0)) as UnpaidInterest,
ifnull(sum(tlb_mastertransaction.interestPaid),0) as interestPaid,ifnull(sum(tlb_mastertransaction.interest),0) as interest
FROM
tbl_personalinfo
INNER JOIN tlb_mastertransaction ON tbl_personalinfo.memberid = tlb_mastertransaction.memberid
INNER JOIN tbpayrollperiods ON tbpayrollperiods.Periodid = tlb_mastertransaction.periodid
LEFT JOIN tbl_refund ON tbl_refund.membersid = tbl_personalinfo.memberid AND tbl_refund.periodid = tbpayrollperiods.Periodid
where tbl_personalinfo.memberid = %s AND tlb_mastertransaction.periodid <= %s GROUP BY memberid", GetSQLValueString($row_status['memberid'], "text"),GetSQLValueString($row_status['periodid'], "int"));

$balance = mysql_query($query_balance, $cov) or die(mysql_error());
$row_balance = mysql_fetch_assoc($balance);
$totalRows_balance = mysql_num_rows($balance);



?>   <tr>
                                  <th align="left" scope="col"><?php if ($totalRows_status > 0) { ?> <input name="memberid" type="checkbox" id="memberid" value="<?php echo $row_status['memberid']; ?>,<?php echo $row_status['periodid']; ?>" checked="checked" /> <?php }?>
                                 </th>
                                   <th align="left" scope="col"><?php echo $row_status['memberid']; ?></th>
                                   <th align="left" scope="col"><?php echo $row_status['PayrollPeriod']; ?></th>
                                   <th align="left" scope="col"><?php echo $row_status['namess']; ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_status['entryFee'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_status['savings'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_balance['savings'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_status['shares'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_balance['shares'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_balance['loanBalance'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_status['loanrepayments'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_status['loan'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_status['interest'],2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_status['interestPaid'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_balance['UnpaidInterest'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo (number_format(round($row_status['total']) ,2,'.',',')); ?></th>
                                   </tr> <?php } while ($row_status = mysql_fetch_assoc($status)); ?>
                                    <tr>
                                      <th align="left" scope="col"></th>
                                      <th align="left" scope="col">Total</th>
                                      <th align="left" scope="col">&nbsp;</th>
                                      <th align="left" scope="col">&nbsp;</th>
                                      <th align="right" scope="col"><?php echo number_format($row_totalsum['entryFee'] ,2,'.',','); ?></th>
                                      <th align="right" scope="col"><?php echo number_format($row_totalsum['savings'] ,2,'.',','); ?></th>
                                      <th align="right" scope="col">&nbsp;</th>
                                      <th align="right" scope="col"><?php echo number_format($row_totalsum['shares'] ,2,'.',','); ?></th>
                                      <th align="right" scope="col">&nbsp;</th>
                                      <th align="right" scope="col"><?php echo number_format($row_totalsum['loan'] ,2,'.',','); ?></th>
                                      <th align="right" scope="col"><?php echo number_format($row_totalsum['loanrepayments'] ,2,'.',','); ?></th>
                                      <th align="right" scope="col">&nbsp;</th>
                                      <th align="right" scope="col"><?php echo number_format($row_totalsum['interest'],2,'.',','); ?></th>
                                      <th align="right" scope="col"><?php echo number_format($row_totalsum['interestPaid'],2,'.',','); ?></th>
                                      <th align="right" scope="col">&nbsp;</th>
                                      <th align="right" scope="col"><?php echo (number_format((round($row_totalsum['total'])),2,'.',',')); ?></th>
                                    </tr> 
                                 </tbody>
                               </table>
                               
                               
                               

                               <script language="javascript">
                               $(document).ready( function () {$('#table_id').DataTable();
} );
                               
                               
                               </script>
</body>
</html>
<?php
mysql_free_result($status);
?>
