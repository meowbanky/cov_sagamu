<?php require_once('../../Connections/hms.php'); ?>
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO tbl_precomplain (presentingcomplain) VALUES (%s)",
                       GetSQLValueString($_POST['textarea'], "text"));

  mysql_select_db($database_hms, $hms);
  $Result1 = mysql_query($insertSQL, $hms) or die(mysql_error());
}

mysql_select_db($database_hms, $hms);
$query_Recordset1 = "SELECT * FROM tbl_precomplain";
$Recordset1 = mysql_query($query_Recordset1, $hms) or die(mysql_error());
$row_Recordset1 = mysql_fetch_assoc($Recordset1);
$totalRows_Recordset1 = mysql_num_rows($Recordset1);
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>jQuery TE | Downloaded Demo | v.1.4.0</title>

<link type="text/css" rel="stylesheet" href="demo.css">
<link type="text/css" rel="stylesheet" href="../jquery-te-1.4.0.css">

<script type="text/javascript" src="../jquery.min.js" charset="utf-8"></script>
<script type="text/javascript" src="../jquery-te-1.4.0.min.js" charset="utf-8"></script>
<script language="javascript">
function info(){
alert(document.getElementById("textarea").value);
}
</script>

</head>

<body>



<!------------------------------------------------------------ jQUERY TEXT EDITOR ------------------------------------------------------------>
<form name="form1" method="POST" action="<?php echo $editFormAction; ?>">
  <p>
    <textarea name="textarea" cols="5" class="jqte-test" id="textarea"></textarea>
    <textarea name="textarea2" cols="5" class="jqte-test" id="textarea"></textarea>
    <input name="Submit" type="submit" class="testbutton" id="button" value="Submit">
    <input type="hidden" name="MM_insert" value="form1">
  </p>
</form><p>
<script>
	$('.jqte-test').jqte();
	
	// settings of status
	var jqteStatus = true;
	$(".status").click(function()
	{
		jqteStatus = jqteStatus ? false : true;
		$('.jqte-test').jqte({"status" : jqteStatus})
	});
</script>

<!------------------------------------------------------------ jQUERY TEXT EDITOR ------------------------------------------------------------>




<?php do { ?>
  <?php echo $row_Recordset1['presentingcomplain']; ?>
  <?php } while ($row_Recordset1 = mysql_fetch_assoc($Recordset1)); ?>
</body>
</html>
<?php
mysql_free_result($Recordset1);
?>
