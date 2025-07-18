<?php require_once('Connections/cov.php'); ?>
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


        mysql_select_db($database_cov, $cov);
        
        $insertSQL = sprintf("INSERT INTO tbl_accounting (session_id,type, post_to, post_date,period,details,amount) VALUES(%s,%s,%s,%s,%s,%s,%s)",
                       GetSQLValueString($_SESSION['SESS_INVOICE'], "text"),      
                       GetSQLValueString($_POST['transact_type'], "int"),
                       GetSQLValueString($_POST['post_account'], "text"),
					   GetSQLValueString($_POST['transact_date'], "date"),
                       GetSQLValueString($_POST['period'], "int"),      
                       GetSQLValueString($_POST['details'], "text"),
                       GetSQLValueString($_POST['amount'], "float"));

mysql_select_db($database_cov, $cov);
  $Result1 = mysql_query($insertSQL, $cov) or die(mysql_error());
if($Result1){
    
    echo '1';
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	mysql_select_db($database_cov, $cov);
$query_Checkcoopid = sprintf("SELECT * FROM tblaccountno WHERE tblaccountno.COOPNO = %s", GetSQLValueString($_POST['txtCoopid'], "text"));
$Checkcoopid = mysql_query($query_Checkcoopid, $cov) or die(mysql_error());
$row_Checkcoopid = mysql_fetch_assoc($Checkcoopid);
$totalRows_Checkcoopid = mysql_num_rows($Checkcoopid);

if ($totalRows_Checkcoopid > 0) {
	
	
  $updateSQL = sprintf("UPDATE tblaccountno SET Bank=%s, AccountNo=%s WHERE COOPNO=%s",
                       GetSQLValueString($_POST['txtBank'], "text"),
                       GetSQLValueString($_POST['txtAccountNo'], "text"),
                       GetSQLValueString($_POST['txtCoopid'], "text"));
}elseif ($totalRows_Checkcoopid == 0){
	$updateSQL = sprintf("INSERT INTO tblaccountno (Bank, AccountNo, coopno) VALUES(%s,%s,%s)",
                       GetSQLValueString($_POST['txtBank'], "text"),
                       GetSQLValueString($_POST['txtAccountNo'], "text"),
					   GetSQLValueString($_POST['txtCoopid'], "text"));
}

  mysql_select_db($database_cov, $cov);
  $Result1 = mysql_query($updateSQL, $cov) or die(mysql_error());
}
?>