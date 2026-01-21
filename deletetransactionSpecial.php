<?php require_once('Connections/cov.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($conn_vote, $theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
    {
      $theValue = $theValue; // get_magic_quotes_gpc() removed

      $theValue = mysqli_real_escape_string($conn_vote, $theValue);

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

if ((isset($_GET['periodid']))) {
  $deleteSQL = sprintf("DELETE FROM tlb_mastertransactionspecial WHERE periodid=%s AND memberid=%s",
                      GetSQLValueString($cov,$_GET['periodid'], "int"),
					  GetSQLValueString($cov,$_GET['memberid'], "int"));
					  
  mysqli_select_db($cov,$database_cov);
  $Result1 = mysqli_query($cov,$deleteSQL) or die(mysqli_error($cov));
  
   $deleteSQL_loan = sprintf("DELETE FROM tbl_special_loan WHERE periodid=%s AND memberid=%s",
                      GetSQLValueString($cov,$_GET['periodid'], "int"),
					  GetSQLValueString($cov,$_GET['memberid'], "int"));
					  
  mysqli_select_db($cov,$database_cov);
  $Result1 = mysqli_query($cov,$deleteSQL_loan) or die(mysqli_error($cov));
  
$deleteSQL_refund = sprintf("DELETE FROM tbl_refund WHERE periodid=%s AND membersid=%s",
                      GetSQLValueString($cov,$_GET['periodid'], "int"),
					  GetSQLValueString($cov,$_GET['memberid'], "int"));
					  
  mysqli_select_db($cov,$database_cov);
  $Result1 = mysqli_query($cov,$deleteSQL_refund) or die(mysqli_error($cov));
  
  }


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
</body>
</html>