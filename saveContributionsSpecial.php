<?php require_once('Connections/cov.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($conn_vote, $theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
    {
      $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_GET["id"])) && ($_GET["id"] != "-1")) {
	$Amount = str_replace(",","",$_GET['Amount']);
	$periodset = $_GET["periodset"];
	
mysqli_select_db($cov,$database_cov);
$query_checkContribution = sprintf("SELECT tbl_specialcontributions.membersid, tbl_specialcontributions.periodid FROM tbl_specialcontributions WHERE membersid=%s and periodid = %s" ,
                        GetSQLValueString($cov,$_GET['id'], "text"),
						GetSQLValueString($cov,$periodset, "int"));
$checkContribution = mysqli_query($cov,$query_checkContribution) or die(mysqli_error($cov));

$row_checkContribution = mysqli_fetch_assoc($checkContribution);
$totalRows_checkContribution = mysqli_num_rows($checkContribution);	
  
 // if ($totalRows_checkContribution > 0){
 // 
//  $updateSQL = sprintf("UPDATE tbl_specialcontributions SET contribution=%s WHERE membersid=%s and periodid = %s",
//                       GetSQLValueString($cov,($Amount, "double"),
//                        GetSQLValueString($cov,($_GET['id'], "text"),
//						GetSQLValueString($cov,($periodset, "int"));
//  mysqli_select_db($cov,$database_cov);
//  $Result1 = mysqli_query($cov,$updateSQL) or die(mysqli_error($cov));
 // }else{
	  
	  $updateSQL = sprintf("INSERT INTO tbl_specialcontributions (contribution, membersid, periodid) VALUES(%s, %s, %s)",
                       GetSQLValueString($cov,$Amount, "double"),
                        GetSQLValueString($cov,$_GET['id'], "text"),
						GetSQLValueString($cov,$periodset, "int"));
  mysqli_select_db($cov,$database_cov);
  $Result1 = mysqli_query($cov,$updateSQL) or die(mysqli_error($cov));
	  
//	  }
  
  
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
<?php
//mysqli_free_result($checkContribution);

//mysqli_free_result($contributions);
?>
