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

$id_loan = "-1";
if (isset($_GET['memberid'])) {
  $id_loan = $_GET['memberid'];
}
mysql_select_db($database_hms, $hms);
$query_loan = sprintf("SELECT tlb_mastertransaction.loanAmount, tlb_mastertransaction.memberid, tlb_mastertransaction.periodid, tlb_mastertransaction.loanRepayment FROM tlb_mastertransaction WHERE memberid = %s", GetSQLValueString($id_loan, "int"));
$loan = mysql_query($query_loan, $hms) or die(mysql_error());
$row_loan = mysql_fetch_assoc($loan);
$totalRows_loan = mysql_num_rows($loan);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<table width="58%" border="1">
  <tr>
    <th width="24%" scope="col">&nbsp;</th>
    <th width="12%" scope="col">&nbsp;</th>
    <th width="21%" scope="col">&nbsp;</th>
    <th width="12%" scope="col">&nbsp;</th>
    <th width="31%" scope="col">&nbsp;</th>
  </tr>
  <?php $l=0;$r=0;$b=0; do {$l = $row_loan['loanAmount'];$r=$row_loan['loanRepayment']; ?>
    <tr>
      <td><?php echo $l;?></td>
      <td><?php //echo $row_loan['memberid']; ?></td>
      <td><?php //echo $row_loan['loanAmount']; ?></td>
      <td><?php echo $r; ?></td>
      <td>&nbsp;</td>
    </tr>
    <?php } while ($row_loan = mysql_fetch_assoc($loan)); ?>
</table>
</body>
</html>
<?php
mysql_free_result($loan);
?>
