<?php require_once('Connections/hms.php'); ?>
<?php
session_start();
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_GET["loanID"])) && ($_GET["loanID"] != "-1")) {
	
	
	if($_GET['saveType'] == 2){
  $updateSQL = sprintf("UPDATE tbl_bank_schedule SET cheque_no=%s WHERE loanid=%s",
                       GetSQLValueString($_GET['chequeno'], "text"),
                       GetSQLValueString($_GET['loanID'], "int"));
	}else if($_GET['saveType'] == 1){
		$updateSQL = sprintf("UPDATE tbl_bank_schedule SET date_on_cheque=%s WHERE loanid=%s",
                       GetSQLValueString($_GET['chequeno'], "text"),
                       GetSQLValueString($_GET['loanID'], "int"));
	
	}else if($_GET['saveType'] == 3){
		$updateSQL = sprintf("UPDATE tbl_bank_schedule SET name=%s WHERE loanid=%s",
                       GetSQLValueString($_GET['chequeno'], "text"),
                       GetSQLValueString($_GET['loanID'], "int"));
	
	}else if($_GET['saveType'] == 4){
		$amount = $_GET['chequeno'];
		$amount = str_replace(',','',$amount);
		
		$updateSQL = sprintf("UPDATE tbl_bank_schedule SET loanamount=%s WHERE loanid=%s",
                       GetSQLValueString($amount, "float"),
                       GetSQLValueString($_GET['loanID'], "int"));
	
	}else if($_GET['saveType'] == 5){
				
		$updateSQL = sprintf("DELETE FROM tbl_bank_schedule WHERE loanid=%s",
                      GetSQLValueString($_GET['loanID'], "int"));
	
	}
				mysql_select_db($database_hms, $hms);
  				$Result1 = mysql_query($updateSQL, $hms) or die(mysql_error());
}

if(isset($_GET['add'])){
$insertRow = "INSERT INTO tbl_bank_schedule (periodid) values (".$_SESSION['period'].")";
					   
				mysql_select_db($database_hms, $hms);
  				$Result1 = mysql_query($insertRow, $hms) or die(mysql_error());
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
//mysql_free_result($Result1);
?>
