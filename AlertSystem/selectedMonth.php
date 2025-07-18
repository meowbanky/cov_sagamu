<?php require_once('Connections/cov.php'); ?>

<?php

$period = -1;
if (isset($_GET['period'])){
	$period = $_GET['period'];
	}

mysqli_select_db($cov,$database_cov);
$query_masterTransaction = "SELECT tbl_personalinfo.memberid,tlb_mastertransaction.transactionid,
concat(tbl_personalinfo.Lname,' , ', tbl_personalinfo.Fname,' ', ifnull( tbl_personalinfo.Mname,'')) AS namess,MobilePhone,
ifnull((Sum(tlb_mastertransaction.loanAmount)),0) AS loan,
ifnull(Sum(tlb_mastertransaction.loanRepayment),0) AS loanrepayments,(ifnull((Sum(tlb_mastertransaction.loanAmount)),0) - ifnull(Sum(tlb_mastertransaction.loanRepayment),0)) as loanBalance,
ifnull(Sum(tlb_mastertransaction.withdrawal),0) AS withrawals,
((ifnull(Sum(tlb_mastertransaction.loanRepayment),0)+ifnull(sum(tlb_mastertransaction.entryFee),0)+ifnull(sum(tlb_mastertransaction.savings),0)+ ifnull(sum(tlb_mastertransaction.shares),0)+ifnull(sum(tlb_mastertransaction.interestPaid),0))) AS total,
tbpayrollperiods.PayrollPeriod, tlb_mastertransaction.periodid,ifnull(sum(tlb_mastertransaction.entryFee),0) as entryFee,
ifnull(sum(tlb_mastertransaction.savings),0) as savings,ifnull(sum(tlb_mastertransaction.shares),0) as shares,
ifnull(sum(tlb_mastertransaction.interestPaid),0) as interestPaid,ifnull(sum(tlb_mastertransaction.interest),0) as interest
FROM tbl_personalinfo INNER JOIN tlb_mastertransaction ON tbl_personalinfo.memberid = tlb_mastertransaction.memberid
INNER JOIN tbpayrollperiods ON tbpayrollperiods.Periodid = tlb_mastertransaction.periodid
LEFT JOIN tbl_refund ON tbl_refund.membersid = tbl_personalinfo.memberid AND tbl_refund.periodid = tbpayrollperiods.Periodid
where Status = 'Active' and tlb_mastertransaction.Periodid <= '". $period ."'  GROUP BY
tbl_personalinfo.memberid order by tbl_personalinfo.memberid";

$masterTransaction = mysqli_query($cov, $query_masterTransaction) or die(mysql_error());
$row_masterTransaction = mysqli_fetch_assoc($masterTransaction);
$totalRows_masterTransaction = mysqli_num_rows($masterTransaction);

$query_payperiod = "SELECT tbpayrollperiods.Periodid, tbpayrollperiods.PayrollPeriod FROM tbpayrollperiods WHERE Periodid = ".$period;
$payperiod = mysqli_query($cov,$query_payperiod) or die(mysql_error());
$row_payperiod = mysqli_fetch_assoc($payperiod);
$totalRows_payperiod = mysqli_num_rows($payperiod);

mysqli_select_db($cov,$database_cov);
$query_grandTotal = "SELECT
tbl_personalinfo.memberid,
tlb_mastertransaction.transactionid,
concat(tbl_personalinfo.Lname,' , ', tbl_personalinfo.Fname,' ', ifnull( tbl_personalinfo.Mname,'')) AS namess,MobilePhone,
ifnull((Sum(tlb_mastertransaction.loanAmount)),0) AS loan,
ifnull(Sum(tlb_mastertransaction.loanRepayment),0) AS loanrepayments, (ifnull((Sum(tlb_mastertransaction.loanAmount)),0) - ifnull(Sum(tlb_mastertransaction.loanRepayment),0)) as loanBalance,
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
LEFT JOIN tbl_refund ON tbl_refund.membersid = tbl_personalinfo.memberid AND tbl_refund.periodid = tbpayrollperiods.Periodid where tlb_mastertransaction.Periodid <= ". $period ;
$grandTotal = mysqli_query($cov,$query_grandTotal) or die(mysql_error());
$row_grandTotal = mysqli_fetch_assoc($grandTotal);
$totalRows_grandTotal = mysqli_num_rows($grandTotal);

mysqli_select_db($cov,$database_cov);
$query_staffid = "SELECT
tbl_personalinfo.memberid,
concat(tbl_personalinfo.memberid, ' - ', tbl_personalinfo.Lname,', ', ifnull(tbl_personalinfo.Mname,''),' ',tbl_personalinfo.Fname) as namess
FROM tbl_personalinfo WHERE `Status` = 'Active'
order by memberid
";
$staffid = mysqli_query($cov,$query_staffid) or die(mysql_error());
$row_staffid = mysqli_fetch_assoc($staffid);
$totalRows_staffid = mysqli_num_rows($staffid);

//mysql_select_db($database_cov, $cov);
//$query_MaxPeriod = "SELECT tbpayrollperiods.PayrollPeriod FROM tbpayrollperiods where id = " . $row_masterTransaction['MaxPeriodID'] ;
//$MaxPeriod = mysql_query($query_MaxPeriod, $cov) or die(mysql_error());
//$row_MaxPeriod = mysql_fetch_assoc($MaxPeriod);
//$totalRows_MaxPeriod = mysql_num_rows($MaxPeriod);

//mysql_select_db($database_cov, $cov);
//$query_coopid2 = "SELECT tblemployees.CoopID,tblemployees.MobileNumber FROM tblemployees where Status = 'Active'";
//$coopid2 = mysql_query($query_coopid2, $cov) or die(mysql_error());
//$row_coopid2 = mysql_fetch_assoc($coopid2);
//$totalRows_coopid2 = mysql_num_rows($coopid2);


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<table width="99%" border="1" bordercolor="#00FF33">
              <tr>
                <th width="10%" scope="col"><span class="style8">STAFF NO</span></th>
                <th width="18%" scope="col"><span class="style5">Name</span></th>
                <th width="18%" scope="col"><span class="style5">SHARES</span></th>
                <th width="19%" scope="col"><span class="style5">SAVINGS</span></th>
                <th width="19%" scope="col"><span class="style8">LOAN BALANCE </span></th>
                <th width="30%" scope="col"><span class="style5">INTEREST BALANCE</span></th>
                <th width="30%" scope="col"><span class="style8">TEL. NO </span></th>
                <th width="30%" scope="col"><span class="style8">PERIOD</span></th>
              </tr>
             <?php do { ?> <tr>
				 <th scope="row"><span class="style5"><?php echo $row_masterTransaction['memberid']; ?></span></th>
				 <td align="left"><strong>
				   <span class="style5"><?php  echo $row_masterTransaction['namess']; ?></span>
				 </strong></td>
                  <td align="right"><span class="style5"><strong><?php  echo number_format($row_masterTransaction['shares'],2,'.',','); ?></strong></span></td>
                  <td align="right"><strong>
                    <span class="style5"><?php  echo number_format($row_masterTransaction['savings'],2,'.',','); ?></span>
                  </strong></td>
                  <td align="right"><span class="style5"><strong><?php  echo number_format($row_masterTransaction['loanBalance'],2,'.',','); ?></strong></span></td>
                  <td><strong><span class="style5">
                    <?php  echo number_format($row_masterTransaction['interest']-$row_masterTransaction['interestPaid'],2,'.',','); ?></span>
                  </strong></td>
                  <td><span class="style5"><strong><?php echo $row_masterTransaction['MobilePhone']; ?></strong></span></td>
                  <td width="100%"><span class="style5"><strong><?php echo $row_payperiod['PayrollPeriod']; ?></strong></span></td>
                 </tr><?php } while ($row_masterTransaction = mysqli_fetch_assoc($masterTransaction)); ?>
                 <tr>
                   <th scope="row"><span class="style5"><strong>GRAND TOTAL </strong></span></th>
                   <td align="right">&nbsp;</td>
                   <td align="right"><span class="style6"><?php echo number_format($row_grandTotal['shares'],2,'.',','); ?></span></td>
                   <td align="right"><span class="style6"><?php echo number_format($row_grandTotal['savings'],2,'.',','); ?></span></td> 
                   <td align="right"><span class="style6"><?php  echo number_format($row_grandTotal['loanBalance'],2,'.',','); ?></span></td>
                   <td><strong><span class="style5">
                     <?php  echo number_format($row_grandTotal['interest']-$row_grandTotal['interestPaid'],2,'.',','); ?></span>
                   </strong></td>
                   <td>&nbsp;</td>
                   <td>&nbsp;</td>
                 </tr> 
</table>
     <div align="center">
       <input name="periodsend" type="hidden" id="periodsend" value="<?php echo $period ;?>" />         
<table width="50%" border="0">
          <tr>
            <th width="25%" height="34" scope="col"><div align="right">Equality</div></th>
            <th width="26%" scope="col"><div align="left">
              <select name="equality" id="equality">
                <option value="&gt;">Equality</option>
                <option value="=">Equals</option>
                <option value="&gt;">Greater Than</option>
                <option value="&lt;">Less Than</option>
                <option value="&gt;=">Greater Than or Equals To</option>
                <option value="&lt;=">Less Than or Equals To</option>
              </select>
            </div></th>
            <th width="17%" scope="col">Staff ID </th>
            <th width="32%" scope="col"><select name="staffid" id="staffid">
              <option value="0">Member ID</option>
               <?php
do {  
?>          <option value="<?php echo $row_staffid['memberid'] ; ?>"><?php echo $row_staffid['namess']; ?></option>
   
              <?php
} while ($row_staffid = mysqli_fetch_assoc($staffid));
  $rows = mysqli_num_rows($staffid);
  if($rows > 0) {
      mysqli_data_seek($staffid, 0);
	  $row_staffid = mysqli_fetch_assoc($staffid);
  }
?>
            </select>
</th>
          </tr>
        </table></div>
              <p align="center">
                <input type="button" name="Button" onclick="parent.sendsms(document.getElementById('equality').value,document.getElementById('staffid').value,document.getElementById('periodsend').value)" value="SEND SMS" / >
              </p>
</body>
</html>