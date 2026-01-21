<?php require_once('Connections/cov.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($conn_vote, $theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
    {
      $theValue = $theValue; // get_magic_quotes_gpc() removed

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
//$query_Batch = sprintf("SELECT CONCAT(tbl_personalinfo.Lname,' , ',tbl_personalinfo.Fname,' ',(ifnull(tbl_personalinfo.Mname,' '))) AS `name`, (tbl_special_loan.loanamount + tbl_special_loan.interest) as loanamount, tbl_special_loan.loanid, tbl_special_loan.periodid, tbl_special_loan.memberid, tbl_contributions.loan as loanrepayment FROM tbl_personalinfo INNER JOIN tbl_special_loan ON tbl_special_loan.memberid = tbl_personalinfo.patientid INNER JOIN tbl_contributions ON tbl_contributions.membersid = tbl_personalinfo.patientid WHERE tbl_special_loan.periodid= %s ", GetSQLValueString($col_Batch, "int"));
//$Batch = mysql_query($query_Batch, $cov) or die(mysqli_error($cov));
//$row_Batch = mysql_fetch_assoc($Batch);
//$totalRows_Batch = "-1";


if (isset($_GET['id'])) {
  $totalRows_Batch = $_GET['id'];
}

mysqli_select_db($cov,$database_cov);

    mysqli_select_db($cov,$database_cov);
    $query_Batch = sprintf("SELECT 
        CONCAT(p.Lname, ' , ', p.Fname, ' ', IFNULL(p.Mname, ' ')) AS `name`, 
        sl.loanamount, 
        sl.interest, 
        (sl.loanamount + sl.interest) AS total_payable,
        sl.loanid, 
        sl.periodid, 
        sl.memberid,
        (SELECT IFNULL(SUM(sc.contribution), 0) 
         FROM tbl_contributions sc 
         WHERE sc.membersid = sl.memberid AND sc.periodid = sl.periodid) AS total_repaid
    FROM tbl_personalinfo p
    INNER JOIN tbl_special_loan sl ON sl.memberid = p.memberid 
    WHERE sl.periodid = %s", GetSQLValueString($cov, $col_Batch, "int"));

    $Batch = mysqli_query($cov, $query_Batch) or die(mysqli_error($cov));
    $row_Batch = mysqli_fetch_assoc($Batch);
    $totalRows_Batch = mysqli_num_rows($Batch);
    ?>

    <tr valign="top">
      <td class="greyBgdHeader" valign="middle" height="35"><strong>Name</strong></td>
      <td class="greyBgdHeader" valign="middle"><strong>Loan Amount</strong></td>
      <td class="greyBgdHeader" valign="middle"><strong>Interest (2%)</strong></td>
      <td class="greyBgdHeader" valign="middle"><strong>Total Payable</strong></td>
      <td class="greyBgdHeader" valign="middle"><strong>Repaid</strong></td>
      <td class="greyBgdHeader" valign="middle"><strong>Balance</strong></td>
      <td class="greyBgdHeader" valign="middle"><strong>Surety</strong></td>
      <td class="greyBgdHeader" valign="middle"><strong>Add Guarantor</strong></td>
      <td colspan="2" class="greyBgdHeader" valign="middle"><?php if ($totalRows_Batch > 0) { // Show if recordset not empty ?>
          <input name="button" type="button" class="tableHeaderContentDarkBlue" id="button" value="Delete Selected" onclick="javascript:deleteLoan(document.forms['form2'].loanID.value,document.forms['period2'].PeriodId2.value);" />
      <?php } // Show if recordset not empty ?></td>
    </tr>
    <?php do { ?>
        <?php if ($totalRows_Batch > 0) { // Show if recordset not empty 
            $balance = $row_Batch['total_payable'] - $row_Batch['total_repaid'];
        ?>
          <tr valign="top">
            <td class="greyBgd" valign="middle" height="35"><?php echo $row_Batch['name']; ?></td>
            <td class="greyBgd" valign="middle"><?php echo number_format($row_Batch['loanamount'],2,'.',','); ?></td>
            <td class="greyBgd" valign="middle"><?php echo number_format($row_Batch['interest'],2,'.',','); ?></td>
            <td class="greyBgd" valign="middle"><?php echo number_format($row_Batch['total_payable'],2,'.',','); ?></td>
            <td class="greyBgd" valign="middle"><?php echo number_format($row_Batch['total_repaid'],2,'.',','); ?></td>
            <td class="greyBgd" valign="middle" style="font-weight:bold; color: <?= $balance > 0 ? 'red' : 'green' ?>"><?php echo number_format($balance,2,'.',','); ?></td>
            
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
