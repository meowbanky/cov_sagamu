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

if ((isset($_GET["MM_insert"])) && ($_GET["MM_insert"] == "form1")&& ($_GET["pid"] != "")) {
  $insertSQL = sprintf("INSERT INTO tbl_presentingcomplain (visitId,patientId,dateOfCapture,capturedBy,complain, historyOfPresentingComplain) VALUES (".$_GET['visitid'].",".$_GET['mrn'].",Now(),".$_SESSION['UserID'].",%s, %s)",
                       GetSQLValueString($_POST['precomplain'], "text"),
                       GetSQLValueString($_POST['historyOfPrecomplain'], "text"));

  mysql_select_db($database_hms, $hms);
  $Result1 = mysql_query($insertSQL, $hms) or die(mysql_error());
}



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<div id="saveSuccess">Saved Successfully</div>
</body>
</html>