<?php require_once('Connections/alertsystem.php'); ?>

<?php
mysql_select_db($database_alertsystem, $alertsystem);
$query_masterTransaction = "SELECT
tbl_personalinfo.patientid,
concat(tbl_personalinfo.Lname,' , ', tbl_personalinfo.Fname,' ', ifnull( tbl_personalinfo.Mname,'')) AS namess,
(Sum(tlb_mastertransaction.Contribution)+ Sum(tlb_mastertransaction.withdrawal)) AS Contribution,
tlb_mastertransaction.loanRepayment,
(sum(tlb_mastertransaction.loanAmount)+ sum(tlb_mastertransaction.interest)) AS Loan,
((sum(tlb_mastertransaction.loanAmount)+ sum(tlb_mastertransaction.interest))- sum(tlb_mastertransaction.loanRepayment)) AS Loanbalance,
Sum(tlb_mastertransaction.withdrawal) AS withdrawal,
tbl_personalinfo.MobilePhone,
Max(tlb_mastertransaction.periodid),
tbpayrollperiods.PayrollPeriod
FROM
tlb_mastertransaction
INNER JOIN tbl_personalinfo ON tbl_personalinfo.patientid = tlb_mastertransaction.memberid
INNER JOIN tbpayrollperiods ON tbpayrollperiods.Periodid = tlb_mastertransaction.periodid
where Status = 'Active'
GROUP BY
tbl_personalinfo.patientid
";

$masterTransaction = mysql_query($query_masterTransaction, $alertsystem) or die(mysql_error());
$row_masterTransaction = mysql_fetch_assoc($masterTransaction);
$totalRows_masterTransaction = mysql_num_rows($masterTransaction);

$query_payperiod = "SELECT tbpayrollperiods.Periodid, tbpayrollperiods.PayrollPeriod FROM tbpayrollperiods WHERE Periodid = ".$row_masterTransaction['Max(tlb_mastertransaction.periodid)'];
$payperiod = mysql_query($query_payperiod, $alertsystem) or die(mysql_error());
$row_payperiod = mysql_fetch_assoc($payperiod);
$totalRows_payperiod = mysql_num_rows($payperiod);

mysql_select_db($database_alertsystem, $alertsystem);
$query_grandTotal = "SELECT Sum(tlb_mastertransaction.Contribution) as contri, ((sum(tlb_mastertransaction.loanAmount)+ sum(tlb_mastertransaction.interest))- (sum(tlb_mastertransaction.loanRepayment))) as loanbalance FROM tlb_mastertransaction ";
$grandTotal = mysql_query($query_grandTotal, $alertsystem) or die(mysql_error());
$row_grandTotal = mysql_fetch_assoc($grandTotal);
$totalRows_grandTotal = mysql_num_rows($grandTotal);

mysql_select_db($database_alertsystem, $alertsystem);
$query_staffid = "SELECT tbl_personalinfo.patientid FROM tbl_personalinfo order by patientid";
$staffid = mysql_query($query_staffid, $alertsystem) or die(mysql_error());
$row_staffid = mysql_fetch_assoc($staffid);
$totalRows_staffid = mysql_num_rows($staffid);

//mysql_select_db($database_alertsystem, $alertsystem);
//$query_MaxPeriod = "SELECT tbpayrollperiods.PayrollPeriod FROM tbpayrollperiods where id = " . $row_masterTransaction['MaxPeriodID'] ;
//$MaxPeriod = mysql_query($query_MaxPeriod, $alertsystem) or die(mysql_error());
//$row_MaxPeriod = mysql_fetch_assoc($MaxPeriod);
//$totalRows_MaxPeriod = mysql_num_rows($MaxPeriod);

//mysql_select_db($database_alertsystem, $alertsystem);
//$query_coopid2 = "SELECT tblemployees.CoopID,tblemployees.MobileNumber FROM tblemployees where Status = 'Active'";
//$coopid2 = mysql_query($query_coopid2, $alertsystem) or die(mysql_error());
//$row_coopid2 = mysql_fetch_assoc($coopid2);
//$totalRows_coopid2 = mysql_num_rows($coopid2);


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!-- DW6 -->
<head>
<!-- Copyright 2005 Macromedia, Inc. All rights reserved. -->
<title>..:OOUTH MHWUN SMS ALERT:..</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" href="mm_health_nutr.css" type="text/css" />
<script language="JavaScript" type="text/javascript">
//--------------- LOCALIZEABLE GLOBALS ---------------
var d=new Date();
var monthname=new Array("January","February","March","April","May","June","July","August","September","October","November","December");
//Ensure correct for language. English is "January 1, 2004"
var TODAY = monthname[d.getMonth()] + " " + d.getDate() + ", " + d.getFullYear();
//---------------   END LOCALIZEABLE   ---------------
</script>
<style type="text/css">
<!--
.style5 {font-size: 14px; color: #000000; }
.style6 {color: #000000; font-weight: bold; font-size: 14px; }
.style8 {color: #000000; font-size: 15px; }
-->
</style>
</head>
<body bgcolor="#F4FFE4">
<form id="form1" name="form1" method="post" action= "sendsms.php">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr bgcolor="#D5EDB3">
    <td colspan="2" rowspan="2"><img src="mm_health_photo.jpg" alt="Header image" width="382" height="101" border="0" /></td>
    <td width="652" height="50" id="logo" valign="bottom" align="center" nowrap="nowrap">MHWUNWEL-OOUTH SMS ALERT SYSTEM </td>
    <td width="64">&nbsp;</td>
  </tr>

  <tr bgcolor="#D5EDB3">
    <td height="51" id="tagline" valign="top" align="center">..sms system </td>
	<td width="64">&nbsp;</td>
  </tr>

  <tr>
    <td colspan="4" bgcolor="#5C743D"><img src="mm_spacer.gif" alt="" width="1" height="2" border="0" /></td>
  </tr>

  <tr>
    <td colspan="4" bgcolor="#99CC66" background="mm_dashed_line.gif"><img src="mm_dashed_line.gif" alt="line decor" width="4" height="3" border="0" /></td>
  </tr>

  <tr bgcolor="#99CC66">
  <td>&nbsp;</td>
  	<td colspan="3" id="dateformat" height="20"><a href="javascript:;">HOME</a>&nbsp;&nbsp;::&nbsp;&nbsp;<script language="JavaScript" type="text/javascript">
      document.write(TODAY);	</script>	</td>
  </tr>

  <tr>
    <td colspan="4" bgcolor="#99CC66" background="mm_dashed_line.gif"><img src="mm_dashed_line.gif" alt="line decor" width="4" height="3" border="0" /></td>
  </tr>

  <tr>
    <td colspan="4" bgcolor="#5C743D"><img src="mm_spacer.gif" alt="" width="1" height="2" border="0" /></td>
  </tr>
 <tr>
    <td width="40">&nbsp;</td>
    <td colspan="2" valign="top">&nbsp;<br />
    &nbsp;<br />
   <table border="0" cellspacing="0" cellpadding="2" width="993">
        <tr>
          <td width="989" class="pageName">Master Transaction Listing - Month of <?php echo $row_payperiod['PayrollPeriod']; ?></td>
        </tr>
        <tr>
          <td class="bodyText"><p>&nbsp;</p>		  
            <table width="99%" border="1" bordercolor="#00FF33">
              <tr>
                <th width="10%" scope="col"><span class="style8">STAFF NO</span></th>
                <th width="18%" scope="col"><span class="style8">WELFARE CONTRIBUTION</span></th>
                <th width="19%" scope="col"><span class="style8">LOAN BALANCE </span></th>
                <th width="30%" scope="col"><span class="style8">TEL. NO </span></th>
                <th width="30%" scope="col"><span class="style8">PERIOD</span></th>
              </tr>
             <?php do { ?> <tr>
				 <th scope="row"><span class="style5"><?php echo $row_masterTransaction['patientid']; ?></span></th>
                  <td align="right"><span class="style5"><strong><?php  echo number_format($row_masterTransaction['Contribution'],2,'.',','); ?></strong></span></td>
                  <td align="right"><span class="style5"><strong><?php  echo number_format($row_masterTransaction['Loanbalance'],2,'.',','); ?></strong></span></td>
                  <td><span class="style5"><strong><?php echo $row_masterTransaction['MobilePhone']; ?></strong></span></td>
                  <td width="100%"><span class="style5"><strong><?php echo $row_payperiod['PayrollPeriod']; ?></strong></span></td>
                 </tr><?php } while ($row_masterTransaction = mysql_fetch_assoc($masterTransaction)); ?>
                 <tr>
                   <th scope="row"><span class="style5"><strong>GRAND TOTAL </strong></span></th>
                   <td align="right"><span class="style6"><?php echo number_format($row_grandTotal['contri'],2,'.',','); ?></span></td> 
                   <td align="right"><span class="style6"><?php  echo number_format($row_grandTotal['loanbalance'],2,'.',','); ?></span></td>
                   <td>&nbsp;</td>
                   <td>&nbsp;</td>
                 </tr> 
              </table></td>
        </tr>
      </table>
	   
	      
	   
<div align="center"><br />
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
            <th width="17%" scope="col">Coop ID </th>
            <th width="32%" scope="col"><select name="staffid" id="staffid">
              <option value="0">Member ID</option>
               <?php
do {  
?>          <option value="<?php echo $row_staffid['patientid'] ; ?>"><?php echo $row_staffid['patientid']; ?></option>
   
              <?php
} while ($row_staffid = mysql_fetch_assoc($staffid));
  $rows = mysql_num_rows($staffid);
  if($rows > 0) {
      mysql_data_seek($staffid, 0);
	  $row_staffid = mysql_fetch_assoc($staffid);
  }
?>
            </select>
</th>
          </tr>
        </table>
	     
<input type="submit" name="Submit" value="SEND SMS" />
	    <br />	
        </div></td>
    <td width="64">&nbsp;</td>
  </tr>

 <tr>
    <td width="40">&nbsp;</td>
   
	<td width="64">&nbsp;</td>
  </tr>
</table>

</form>
</body>
</html>
<?php
mysql_free_result($masterTransaction);

mysql_free_result($grandTotal);

mysql_free_result($staffid);

//mysql_free_result($MaxPeriod);
?>
