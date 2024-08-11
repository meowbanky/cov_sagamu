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

if ((isset($_GET['id']))) {
  $insertSQL = sprintf("INSERT INTO tbl_mastertransaction_delete (SELECT * FROM tlb_mastertransaction WHERE memberid = %s)",
                      GetSQLValueString($cov,$_GET['id'], "int"));
 mysqli_select_db($cov,$database_cov);
  $Result1 = mysqli_query($cov,$insertSQL ) or die(mysqli_error($cov));
  
  $deleteSQL = sprintf("delete from tlb_mastertransaction WHERE memberid = %s ",
                      GetSQLValueString($cov,$_GET['id'], "int"));
 	mysqli_select_db($cov,$database_cov);
  $Result2 = mysqli_query($cov,$deleteSQL ) or die(mysqli_error($cov));
  
  
  $deleteSQL2 = sprintf("delete from tbl_contributions WHERE membersid = %s ",
                      GetSQLValueString($cov,$_GET['id'], "int"));
 mysqli_select_db($cov,$database_cov);
  $Result3 = mysqli_query($cov,$deleteSQL2 ) or die(mysqli_error($cov));
  
  
  
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