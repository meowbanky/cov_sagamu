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

//if ((isset($_POST["ButtonSearch"])) && ($_POST["ButtonSearch"] == "Search")) {
mysql_select_db($database_hms, $hms);
$query_SearchResult = "SELECT tbl_personalinfo.patientid, CONCAT(sfxname,' ',tbl_personalinfo.Lname,' ,', tbl_personalinfo.Fname,' ',ifnull(tbl_personalinfo.Mname,' ')) AS 'PatientName', tbl_personalinfo.DOB, tbl_personalinfo.MobilePhone FROM tbl_personalinfo WHERE tbl_personalinfo.patientid ='".$_GET['SearchMRN']."'";
$SearchResult = mysql_query($query_SearchResult, $hms) or die(mysql_error());
$row_SearchResult = mysql_fetch_assoc($SearchResult);
$totalRows_SearchResult = mysql_num_rows($SearchResult);

//}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body><div id="UploadSearchResult">
  <?php if ($totalRows_SearchResult > 0) {?> 
  <p align="center" class="errorBox">
<input name="mrnExist2" type="checkbox" id="mrnExist2" value="test2" checked="checked" />
Records Already Existing!!!
<script language="javascript">
		//document.getElementById("mrnExist").checked = "True";		
</script></p> 
  <?php }  ?>

</div>
</body>
</html>
<?php
mysql_free_result($SearchResult);
?>
