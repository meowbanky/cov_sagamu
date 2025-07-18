<?php require_once('Connections/cov.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($conn_vote, $theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
    {
      $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

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
	mysqli_select_db($cov, $database_cov);
$query_status = sprintf("SELECT
tbl_personalinfo.memberid,
tlb_mastertransactionspecial.transactionid,
concat(tbl_personalinfo.Lname,' , ', tbl_personalinfo.Fname,' ', ifnull( tbl_personalinfo.Mname,'')) AS namess,
ifnull((Sum(tlb_mastertransactionspecial.loanAmount)),0) AS loan,
ifnull(Sum(tlb_mastertransactionspecial.loanRepayment),0) AS loanrepayments,
ifnull(Sum(tlb_mastertransactionspecial.withdrawal),0) AS withrawals,
((ifnull(Sum(tlb_mastertransactionspecial.loanRepayment),0)+ifnull(sum(tlb_mastertransactionspecial.entryFee),0)+ifnull(sum(tlb_mastertransactionspecial.savings),0)+
ifnull(sum(tlb_mastertransactionspecial.shares),0)+ifnull(sum(tlb_mastertransactionspecial.interestPaid),0))) AS total,
tbpayrollperiods.PayrollPeriod,
tlb_mastertransactionspecial.periodid,
ifnull(sum(tlb_mastertransactionspecial.entryFee),0) as entryFee,
ifnull(sum(tlb_mastertransactionspecial.savings),0) as savings,
ifnull(sum(tlb_mastertransactionspecial.shares),0) as shares,
ifnull(sum(tlb_mastertransactionspecial.interestPaid),0) as interestPaid,ifnull(sum(tlb_mastertransactionspecial.interest),0) as interest
FROM
tbl_personalinfo
INNER JOIN tlb_mastertransactionspecial ON tbl_personalinfo.memberid = tlb_mastertransactionspecial.memberid
INNER JOIN tbpayrollperiods ON tbpayrollperiods.Periodid = tlb_mastertransactionspecial.periodid
LEFT JOIN tbl_refund ON tbl_refund.membersid = tbl_personalinfo.memberid AND tbl_refund.periodid = tbpayrollperiods.Periodid
WHERE tbpayrollperiods.Periodid BETWEEN %s AND %s GROUP BY tlb_mastertransactionspecial.periodid,tbl_personalinfo.memberid", 
GetSQLValueString($cov,$periodFrom_status, "int"),GetSQLValueString($cov,$periodTo_status, "int"));	
	}else{
mysqli_select_db($cov, $database_cov);
$query_status = sprintf("SELECT
tbl_personalinfo.memberid,
tlb_mastertransactionspecial.transactionid,
concat(tbl_personalinfo.Lname,' , ', tbl_personalinfo.Fname,' ', ifnull( tbl_personalinfo.Mname,'')) AS namess,
ifnull((Sum(tlb_mastertransactionspecial.loanAmount)),0) AS loan,
ifnull(Sum(tlb_mastertransactionspecial.loanRepayment),0) AS loanrepayments,
ifnull(Sum(tlb_mastertransactionspecial.withdrawal),0) AS withrawals,
((ifnull(Sum(tlb_mastertransactionspecial.loanRepayment),0)+ifnull(sum(tlb_mastertransactionspecial.entryFee),0)+ifnull(sum(tlb_mastertransactionspecial.savings),0)+
ifnull(sum(tlb_mastertransactionspecial.shares),0)+ifnull(sum(tlb_mastertransactionspecial.interestPaid),0))) AS total,
tbpayrollperiods.PayrollPeriod,
tlb_mastertransactionspecial.periodid,
ifnull(sum(tlb_mastertransactionspecial.entryFee),0) as entryFee,
ifnull(sum(tlb_mastertransactionspecial.savings),0) as savings,
ifnull(sum(tlb_mastertransactionspecial.shares),0) as shares,
ifnull(sum(tlb_mastertransactionspecial.interestPaid),0) as interestPaid,ifnull(sum(tlb_mastertransactionspecial.interest),0) as interest
FROM
tbl_personalinfo
INNER JOIN tlb_mastertransactionspecial ON tbl_personalinfo.memberid = tlb_mastertransactionspecial.memberid
INNER JOIN tbpayrollperiods ON tbpayrollperiods.Periodid = tlb_mastertransactionspecial.periodid
LEFT JOIN tbl_refund ON tbl_refund.membersid = tbl_personalinfo.memberid AND tbl_refund.periodid = tbpayrollperiods.Periodid
WHERE tbpayrollperiods.Periodid BETWEEN %s AND %s AND tbl_personalinfo.memberid = %s GROUP BY tlb_mastertransactionspecial.periodid,tbl_personalinfo.memberid", 
GetSQLValueString($cov,$periodFrom_status, "int"),GetSQLValueString($cov,$periodTo_status, "int"),GetSQLValueString($cov,$id_status, "text"));
	}
$status = mysqli_query($cov,$query_status) or die(mysql_error());
$row_status = mysqli_fetch_assoc($status);
$totalRows_status = mysqli_num_rows($status);



if ($_GET['id']==""){
$query_totalsum = sprintf("SELECT
tbl_personalinfo.memberid,
tlb_mastertransactionspecial.transactionid,
concat(tbl_personalinfo.Lname,' , ', tbl_personalinfo.Fname,' ', ifnull( tbl_personalinfo.Mname,'')) AS namess,
ifnull((Sum(tlb_mastertransactionspecial.loanAmount)),0) AS loan,
ifnull(Sum(tlb_mastertransactionspecial.loanRepayment),0) AS loanrepayments,
ifnull(Sum(tlb_mastertransactionspecial.withdrawal),0) AS withrawals,
((ifnull(Sum(tlb_mastertransactionspecial.loanRepayment),0)+ifnull(sum(tlb_mastertransactionspecial.entryFee),0)+ifnull(sum(tlb_mastertransactionspecial.savings),0)+
ifnull(sum(tlb_mastertransactionspecial.shares),0)+ifnull(sum(tlb_mastertransactionspecial.interestPaid),0))) AS total,
tbpayrollperiods.PayrollPeriod,
tlb_mastertransactionspecial.periodid,
ifnull(sum(tlb_mastertransactionspecial.entryFee),0) as entryFee,
ifnull(sum(tlb_mastertransactionspecial.savings),0) as savings,
ifnull(sum(tlb_mastertransactionspecial.shares),0) as shares,
ifnull(sum(tlb_mastertransactionspecial.interestPaid),0) as interestPaid,ifnull(sum(tlb_mastertransactionspecial.interest),0) as interest
FROM
tbl_personalinfo
INNER JOIN tlb_mastertransactionspecial ON tbl_personalinfo.memberid = tlb_mastertransactionspecial.memberid
INNER JOIN tbpayrollperiods ON tbpayrollperiods.Periodid = tlb_mastertransactionspecial.periodid
LEFT JOIN tbl_refund ON tbl_refund.membersid = tbl_personalinfo.memberid AND tbl_refund.periodid = tbpayrollperiods.Periodid
WHERE tbpayrollperiods.Periodid BETWEEN %s AND %s ", 
GetSQLValueString($cov,$periodFrom_status, "int"),GetSQLValueString($cov,$periodTo_status, "int"));	
	}else{
mysqli_select_db($cov, $database_cov);
$query_totalsum = sprintf("SELECT
tbl_personalinfo.memberid,
tlb_mastertransactionspecial.transactionid,
concat(tbl_personalinfo.Lname,' , ', tbl_personalinfo.Fname,' ', ifnull( tbl_personalinfo.Mname,'')) AS namess,
ifnull((Sum(tlb_mastertransactionspecial.loanAmount)),0) AS loan,
ifnull(Sum(tlb_mastertransactionspecial.loanRepayment),0) AS loanrepayments,
ifnull(Sum(tlb_mastertransactionspecial.withdrawal),0) AS withrawals,
((ifnull(Sum(tlb_mastertransactionspecial.loanRepayment),0)+ifnull(sum(tlb_mastertransactionspecial.entryFee),0)+ifnull(sum(tlb_mastertransactionspecial.savings),0)+
ifnull(sum(tlb_mastertransactionspecial.shares),0)+ifnull(sum(tlb_mastertransactionspecial.interestPaid),0))) AS total,
tbpayrollperiods.PayrollPeriod,
tlb_mastertransactionspecial.periodid,
ifnull(sum(tlb_mastertransactionspecial.entryFee),0) as entryFee,
ifnull(sum(tlb_mastertransactionspecial.savings),0) as savings,
ifnull(sum(tlb_mastertransactionspecial.shares),0) as shares,
ifnull(sum(tlb_mastertransactionspecial.interestPaid),0) as interestPaid,ifnull(sum(tlb_mastertransactionspecial.interest),0) as interest
FROM
tbl_personalinfo
INNER JOIN tlb_mastertransactionspecial ON tbl_personalinfo.memberid = tlb_mastertransactionspecial.memberid
INNER JOIN tbpayrollperiods ON tbpayrollperiods.Periodid = tlb_mastertransactionspecial.periodid
LEFT JOIN tbl_refund ON tbl_refund.membersid = tbl_personalinfo.memberid AND tbl_refund.periodid = tbpayrollperiods.Periodid
WHERE tbpayrollperiods.Periodid BETWEEN %s AND %s AND tlb_mastertransactionspecial.memberid = %s ", 
GetSQLValueString($cov,$periodFrom_status, "int"),GetSQLValueString($cov,$periodTo_status, "int"),GetSQLValueString($cov,$id_status, "text"));
	}
$totalsum = mysqli_query($cov,$query_totalsum) or die(mysql_error());
$row_totalsum = mysqli_fetch_assoc($totalsum);
$totalRows_totalsum = mysqli_num_rows($totalsum);

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

<body>
   
      
    <table class="table table-striped table-bordered table-hover table-checkable order-column" id="table_id">
                               <thead>
                                 
                                 <tr class="table_header_new">
                                   <th width="37%" scope="col">Select <input name="button" type="button" class="formbutton" id="button" onClick="deletetrans()" value="Delete"></th>
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
                                   <th width="25%" scope="col">Total  <div class="btn-group">
        <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown">Export to <span class="caret"></span></button>
        <ul class="dropdown-menu" role="menu">
          <li><a onclick="window.print();">Print</a></li>
          <li><a onclick="exportAll('csv');" href="javascript://">CSV</a></li>
          <li><a onclick="exportAll('txt');" href="javascript://">TXT</a></li>
          <li><a onclick="exportAll('xls');" href="javascript://">XLS</a></li>
</ul>
      </div></th>
                                   </tr>
                                   </thead>
                                   <tbody>
                                <?php do { $query_balance = sprintf("SELECT
tbl_personalinfo.memberid,
tlb_mastertransactionspecial.transactionid,
concat(tbl_personalinfo.Lname,' , ', tbl_personalinfo.Fname,' ', ifnull( tbl_personalinfo.Mname,'')) AS namess,
ifnull((Sum(tlb_mastertransactionspecial.loanAmount)),0) AS loan,
ifnull(Sum(tlb_mastertransactionspecial.loanRepayment),0) AS loanrepayments,
ifnull(Sum(tlb_mastertransactionspecial.withdrawal),0) AS withrawals,
((ifnull(Sum(tlb_mastertransactionspecial.loanRepayment),0)+ifnull(sum(tlb_mastertransactionspecial.entryFee),0)+ifnull(sum(tlb_mastertransactionspecial.savings),0)+
ifnull(sum(tlb_mastertransactionspecial.shares),0)+ifnull(sum(tlb_mastertransactionspecial.interestPaid),0))) AS total,
tbpayrollperiods.PayrollPeriod,(ifnull((Sum(tlb_mastertransactionspecial.loanAmount)),0) - ifnull(Sum(tlb_mastertransactionspecial.loanRepayment),0)) as loanBalance,
tlb_mastertransactionspecial.periodid,
ifnull(sum(tlb_mastertransactionspecial.entryFee),0) as entryFee,
ifnull(sum(tlb_mastertransactionspecial.savings),0) as savings,
ifnull(sum(tlb_mastertransactionspecial.shares),0) as shares,(ifnull(sum(tlb_mastertransactionspecial.interest),0) - ifnull(sum(tlb_mastertransactionspecial.interestPaid),0)) as UnpaidInterest,
ifnull(sum(tlb_mastertransactionspecial.interestPaid),0) as interestPaid,ifnull(sum(tlb_mastertransactionspecial.interest),0) as interest
FROM
tbl_personalinfo
INNER JOIN tlb_mastertransactionspecial ON tbl_personalinfo.memberid = tlb_mastertransactionspecial.memberid
INNER JOIN tbpayrollperiods ON tbpayrollperiods.Periodid = tlb_mastertransactionspecial.periodid
LEFT JOIN tbl_refund ON tbl_refund.membersid = tbl_personalinfo.memberid AND tbl_refund.periodid = tbpayrollperiods.Periodid
where tbl_personalinfo.memberid = %s AND tlb_mastertransactionspecial.periodid <= %s GROUP BY memberid", GetSQLValueString($cov,$row_status['memberid'], "text"),GetSQLValueString($cov,$row_status['periodid'], "int"));

$balance = mysqli_query($cov,$query_balance) or die(mysql_error());
$row_balance = mysqli_fetch_assoc($balance);
$totalRows_balance = mysqli_num_rows($balance);



?>   <tr>
                                  <td align="left" scope="col"><?php if ($totalRows_status > 0) { ?> <input name="memberid" type="checkbox" id="memberid" value="<?php echo $row_status['memberid']; ?>,<?php echo $row_status['periodid']; ?>" checked="checked" /> <?php }?>
                                 </td>
                                   <td align="left" scope="col"><?php echo $row_status['memberid']; ?></td>
                                   <td align="left" scope="col"><?php echo $row_status['PayrollPeriod']; ?></td>
                                   <td align="left" scope="col"><?php echo $row_status['namess']; ?></td>
                                   <td align="right" scope="col"><?php echo number_format($row_status['entryFee'] ,2,'.',','); ?></td>
                                   <td align="right" scope="col"><?php echo number_format($row_status['savings'] ,2,'.',','); ?></td>
                                   <td align="right" scope="col"><?php echo number_format($row_balance['savings'] ,2,'.',','); ?></td>
                                   <td align="right" scope="col"><?php echo number_format($row_status['shares'] ,2,'.',','); ?></td>
                                   <td align="right" scope="col"><?php echo number_format($row_balance['shares'] ,2,'.',','); ?></td>
                                   <td align="right" scope="col"><?php echo number_format($row_balance['loanBalance'] ,2,'.',','); ?></td>
                                   <td align="right" scope="col"><?php echo number_format($row_status['loanrepayments'] ,2,'.',','); ?></td>
                                   <td align="right" scope="col"><?php echo number_format($row_status['loan'] ,2,'.',','); ?></td>
                                   <td align="right" scope="col"><?php echo number_format($row_status['interest'],2,'.',','); ?></td>
                                   <td align="right" scope="col"><?php echo number_format($row_status['interestPaid'] ,2,'.',','); ?></td>
                                   <td align="right" scope="col"><?php echo number_format($row_balance['UnpaidInterest'] ,2,'.',','); ?></td>
                                   <td align="right" scope="col"><?php echo (number_format(round($row_status['total']) ,2,'.',',')); ?></td>
                                   </tr> <?php } while ($row_status = mysqli_fetch_assoc($status)); ?>
                                    <tr>
                                      <td align="left" scope="col"></td>
                                      <td align="left" scope="col">Total</td>
                                      <td align="left" scope="col">&nbsp;</td>
                                      <td align="left" scope="col">&nbsp;</td>
                                      <td align="right" scope="col"><?php echo number_format($row_totalsum['entryFee'] ,2,'.',','); ?></td>
                                      <td align="right" scope="col"><?php echo number_format($row_totalsum['savings'] ,2,'.',','); ?></td>
                                      <td align="right" scope="col">&nbsp;</td>
                                      <td align="right" scope="col"><?php echo number_format($row_totalsum['shares'] ,2,'.',','); ?></td>
                                      <td align="right" scope="col">&nbsp;</td>
                                      <td align="right" scope="col"><?php echo number_format($row_totalsum['loan'] ,2,'.',','); ?></td>
                                      <td align="right" scope="col"><?php echo number_format($row_totalsum['loanrepayments'] ,2,'.',','); ?></td>
                                      <td align="right" scope="col">&nbsp;</td>
                                      <td align="right" scope="col"><?php echo number_format($row_totalsum['interest'],2,'.',','); ?></td>
                                      <td align="right" scope="col"><?php echo number_format($row_totalsum['interestPaid'],2,'.',','); ?></td>
                                      <td align="right" scope="col">&nbsp;</td>
                                      <td align="right" scope="col"><?php echo (number_format((round($row_totalsum['total'])),2,'.',',')); ?></td>
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
mysqli_free_result($status);
?>
