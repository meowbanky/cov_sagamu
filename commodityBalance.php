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

$col_commodityBalance = "-1";
if (isset($_GET['id'])) {
  $col_commodityBalance = $_GET['id'];
}
mysql_select_db($database_hms, $hms);
$query_commodityBalance = sprintf("SELECT tlb_mastertransaction.memberid, (sum(tlb_mastertransaction.commodityAmount)- sum(tlb_mastertransaction.commodityRepayment)) as 'balance' FROM tlb_mastertransaction WHERE memberid = %s", GetSQLValueString($col_commodityBalance, "int"));
$commodityBalance = mysql_query($query_commodityBalance, $hms) or die(mysql_error());
$row_commodityBalance = mysql_fetch_assoc($commodityBalance);
$totalRows_commodityBalance = mysql_num_rows($commodityBalance);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<input name="txtCommodityBalance" type="text" class="innerBox" id="txtCommodityBalance" value="<?php echo number_format($row_commodityBalance['balance'],2,'.',','); ?>" readonly="readonly" />
</body>
</html>
<?php
mysql_free_result($commodityBalance);
?>
