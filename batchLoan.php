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

$col_Batch = "-1";
if (isset($_GET['id'])) {
  $col_Batch = $_GET['id'];
}
//mysql_select_db($database_cov, $cov);
//$query_Batch = sprintf("SELECT CONCAT(tbl_personalinfo.Lname,' , ',tbl_personalinfo.Fname,' ',(ifnull(tbl_personalinfo.Mname,' '))) AS `name`, (tbl_loan.loanamount + tbl_loan.interest) as loanamount, tbl_loan.loanid, tbl_loan.periodid, tbl_loan.memberid, tbl_contributions.loan as loanrepayment FROM tbl_personalinfo INNER JOIN tbl_loan ON tbl_loan.memberid = tbl_personalinfo.patientid INNER JOIN tbl_contributions ON tbl_contributions.membersid = tbl_personalinfo.patientid WHERE tbl_loan.periodid= %s ", GetSQLValueString($col_Batch, "int"));
//$Batch = mysql_query($query_Batch, $cov) or die(mysqli_error($cov));
//$row_Batch = mysql_fetch_assoc($Batch);
//$totalRows_Batch = "-1";


if (isset($_GET['id'])) {
  $totalRows_Batch = $_GET['id'];
}

mysqli_select_db($cov,$database_cov);
$query_Batch = sprintf("SELECT CONCAT(tbl_personalinfo.Lname,' , ',tbl_personalinfo.Fname,' ',(ifnull(tbl_personalinfo.Mname,' '))) AS `name`,tbl_loan.loanamount, tbl_loan.loanid, tbl_loan.periodid, tbl_loan.memberid FROM tbl_personalinfo INNER JOIN tbl_loan ON tbl_loan.memberid = tbl_personalinfo.memberid WHERE tbl_loan.periodid= %s ", GetSQLValueString($cov,$col_Batch, "int"));
$Batch = mysqli_query($cov,$query_Batch) or die(mysqli_error($cov));
$row_Batch = mysqli_fetch_assoc($Batch);
$totalRows_Batch = mysqli_num_rows($Batch);

$colname_batchsum = "-1";
if (isset($_GET['id'])) {
  $colname_batchsum = $_GET['id'];
}
mysqli_select_db($cov,$database_cov);
$query_batchsum = sprintf("SELECT (sum( tbl_loan.loanamount)) as amount FROM tbl_loan WHERE periodId =%s", GetSQLValueString($cov,$colname_batchsum, "int"));
$batchsum = mysqli_query($cov,$query_batchsum) or die(mysqli_error($cov));
$row_batchsum = mysqli_fetch_assoc($batchsum);
$totalRows_batchsum = mysqli_num_rows($batchsum);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<table width="96%" align="center" cellpadding="4" cellspacing="0">
  <tbody>
    <script language="javascript" type="text/javascript">document.getElementById("PeriodId2").selectedIndex</script>
    <tr valign="top">
      <td class="greyBgdHeader" valign="middle" height="35"><strong>Name</strong></td>
      <td class="greyBgdHeader" valign="middle"><strong>Loan Amount</strong></td>
      <td class="greyBgdHeader" valign="middle">&nbsp;</td>
      <td valign="middle" class="greyBgdHeader">&nbsp;</td>
      <td class="greyBgdHeader" valign="middle"><strong>Surety</strong></td>
      <td class="greyBgdHeader" valign="middle"><strong>Add Guarantor</strong></td>
      <td colspan="2" class="greyBgdHeader" valign="middle"><?php if ($totalRows_Batch > 0) { // Show if recordset not empty ?>
          <input name="button" type="button" class="tableHeaderContentDarkBlue" id="button" value="Delete Selected" onclick="javascript:deleteLoan(document.forms['form2'].loanID.value,document.forms['period2'].PeriodId2.value);" />
      <?php } // Show if recordset not empty ?></td>
    </tr>
    <?php do { ?>
        <?php if ($totalRows_Batch > 0) { // Show if recordset not empty ?>
          <tr valign="top">
            <td class="greyBgd" valign="middle" height="35"><?php echo $row_Batch['name']; ?></td>
            <td class="greyBgd" valign="middle"><?php echo number_format($row_Batch['loanamount'],2,'.',','); ?></td>
            <td class="greyBgd" valign="middle">&nbsp;</td>
            <td class="greyBgd" valign="middle">&nbsp;</td>
            <td class="greyBgd" valign="middle"><?php mysqli_select_db($cov,$database_cov);
$query_suretyInfo = sprintf("SELECT tbl_personalinfo.Lname,tbl_personalinfo.Fname,tbl_personalinfo.Mname FROM tbl_surety
INNER JOIN tbl_personalinfo ON tbl_surety.surety = tbl_personalinfo.memberid WHERE loanid = %s",GetSQLValueString($cov,$row_Batch['loanid'], "int"));
$suretyInfo = mysqli_query($cov,$query_suretyInfo) or die(mysqli_error($cov));
$row_suretyInfo = mysqli_fetch_assoc($suretyInfo);
$totalRows_suretyInfo = mysqli_num_rows($suretyInfo);

if ($totalRows_suretyInfo >= 1){
$s = 1; do { ?>
                                     <?php echo $s. ' '. $row_suretyInfo['Lname'].' '.$row_suretyInfo['Fname'].' '.$row_suretyInfo['Mname'] ; ?><br>
                                     <?php $s=$s+1;} while ($row_suretyInfo = mysqli_fetch_assoc($suretyInfo));
}?></td>
            <td class="greyBgd" valign="middle"><a href="addSurety.php?loanid=<?php echo $row_Batch['loanid'] ?>&periodID=<?php echo $row_Batch['periodid'] ?>&loanCollector=<?php echo $row_Batch['memberid'] ?> "> Add Guarantor</a></td>
            <td class="greyBgd" valign="middle">&nbsp;</td>
            <td class="greyBgd" valign="middle"><form action="" method="post" name="form2" id="form2">
              <input name="loanID" type="checkbox" id="loanID" value="<?php echo $row_Batch['loanid']; ?>" />
            </form></td>
          </tr>
          <?php } // Show if recordset not empty ?>
<?php } while ($row_Batch = mysqli_fetch_assoc($Batch)); ?>
<?php if ($totalRows_batchsum > 0) { // Show if recordset not empty ?>
  <tr valign="top" align="left">
    <td colspan="8" height="3"><img src="education_files/spacer.gif" alt="" width="1" height="1" /><strong>Sum of Loan/Period = <?php echo number_format($row_batchsum['amount'] ,2,'.',','); ?></strong></td>
  </tr>
  <?php } // Show if recordset not empty ?>
  </tbody>
</table>
</body>
</html>
<?php
mysqli_free_result($Batch);

mysqli_free_result($batchsum);
?>
