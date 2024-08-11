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

mysqli_select_db($cov,$database_cov);
$query_contriution = "SELECT Sum(tlb_mastertransaction.savings)+ Sum(tlb_mastertransaction.shares) as contribution,Sum(tlb_mastertransaction.savings) as savings,Sum(tlb_mastertransaction.shares) as shares FROM tlb_mastertransaction WHERE memberid = '".$_GET['id']. "'";
$contriution = mysqli_query($cov,$query_contriution) or die(mysqli_error($cov));
$row_contriution = mysqli_fetch_assoc($contriution);
$totalRows_contriution = mysqli_num_rows($contriution);

$amount = str_replace(',','',$_GET['amount']);

$amount_balance = $row_contriution['contribution'] - $amount;
$shares_amount = $amount_balance * 0.6 ;
$shares_insert = $shares_amount -  $row_contriution['shares'];
$savings_amount = $amount_balance * 0.4 ; 
$savings_insert = $savings_amount -  $row_contriution['savings'];

if ((isset($_GET['id']))) {  $insertSQL = sprintf("INSERT INTO tlb_mastertransaction (periodid, memberid, shares,savings,withdrawal,completed) values (%s, %s, %s,%s,%s,0)",

                      GetSQLValueString($cov,$_GET['period'], "int"),
					  GetSQLValueString($cov,$_GET['id'], "int"),
					  GetSQLValueString($cov,$shares_insert, "double"),
					  GetSQLValueString($cov,$savings_insert, "double"),
					  GetSQLValueString($cov,-$amount, "double"));
 mysqli_select_db($cov,$database_cov);
  $Result1 = mysqli_query($cov,$insertSQL) or die(mysqli_error($cov));
    
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<?php  if($Result1){ echo 'Saved Successfully' ;} ?>
</body>
</html>