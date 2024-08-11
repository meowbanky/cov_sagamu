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

$col_Batch = "-1";
if (isset($_GET['id'])) {
  $col_Batch = $_GET['id'];
}
mysql_select_db($database_hms, $hms);
$query_Batch = sprintf("SELECT tbl_commodity.memberId, tbl_commodity.itemsid, tbl_commodity.commodityId, tbl_commodity.amount, tbl_commodity.monthlyRepayment, tbl_commodity.commodity_batchid, tbl_commodity.periodId, CONCAT(tbl_personalinfo.Lname,' , ',tbl_personalinfo.Fname,' ',(ifnull(tbl_personalinfo.Mname,' '))) AS 'name', tbl_createcommodity.items FROM tbl_commodity LEFT JOIN tbl_personalinfo ON tbl_personalinfo.patientid = tbl_commodity.memberId INNER JOIN tbl_createcommodity ON tbl_createcommodity.itemsid = tbl_commodity.itemsid WHERE tbl_commodity.periodId = %s ", GetSQLValueString($col_Batch, "int"));
$Batch = mysql_query($query_Batch, $hms) or die(mysql_error());
$row_Batch = mysql_fetch_assoc($Batch);
$totalRows_Batch = mysql_num_rows($Batch);

$colname_batchsum = "-1";
if (isset($_GET['id'])) {
  $colname_batchsum = $_GET['id'];
}
mysql_select_db($database_hms, $hms);
$query_batchsum = sprintf("SELECT sum( tbl_commodity.amount) as amount FROM tbl_commodity WHERE periodId =%s", GetSQLValueString($colname_batchsum, "int"));
$batchsum = mysql_query($query_batchsum, $hms) or die(mysql_error());
$row_batchsum = mysql_fetch_assoc($batchsum);
$totalRows_batchsum = mysql_num_rows($batchsum);
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
      <td class="greyBgdHeader" valign="middle"><strong>Item</strong></td>
      <td class="greyBgdHeader" valign="middle"><strong>Price</strong></td>
      <td valign="middle" class="greyBgdHeader"><div align="right"><strong>Monthly Repayment</strong></div></td>
      <td class="greyBgdHeader" valign="middle">&nbsp;</td>
      <td class="greyBgdHeader" valign="middle">&nbsp;</td>
      <td colspan="2" class="greyBgdHeader" valign="middle"><input name="button" type="button" class="tableHeaderContentDarkBlue" id="button" value="Delete Selected" onclick="javascript:deleteCommodityItem(document.forms['form2'].commodityID.value,document.forms['period2'].PeriodId2.value);" /></td>
    </tr>
    <?php do { ?>
      <tr valign="top">
        <td class="greyBgd" valign="middle" height="35"><?php echo $row_Batch['name']; ?></td>
        <td class="greyBgd" valign="middle"><?php echo $row_Batch['items']; ?></td>
        <td class="greyBgd" valign="middle"><?php echo number_format($row_Batch['amount'] ,2,'.',','); ?></td>
        <td class="greyBgd" valign="middle"><div align="right"><?php echo number_format($row_Batch['monthlyRepayment'] ,2,'.',','); ?></div></td>
        <td class="greyBgd" valign="middle">&nbsp;</td>
        <td class="greyBgd" valign="middle">&nbsp;</td>
        <td class="greyBgd" valign="middle">&nbsp;</td>
        <td class="greyBgd" valign="middle"><form action="" method="post" name="form2" id="form2">
          <input name="commodityID" type="checkbox" id="commodityID" value="<?php echo $row_Batch['commodityId']; ?>" />
        </form></td>
      </tr>
      <?php } while ($row_Batch = mysql_fetch_assoc($Batch)); ?>
<tr valign="top" align="left">
      <td colspan="8" height="3"><img src="education_files/spacer.gif" alt="" width="1" height="1" /><strong>Sum of Commodity/Period = <?php echo number_format($row_batchsum['amount'] ,2,'.',','); ?></strong></td>
    </tr>
  </tbody>
</table>
</body>
</html>
<?php
mysql_free_result($Batch);

mysql_free_result($batchsum);
?>
