<?php require_once('Connections/hms.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{


  global $cov;
  $theValue = function_exists("mysqli_real_escape_string") ? mysqli_real_escape_string($cov, $theValue) : mysqli_escape_string($cov, $theValue);

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

$col_state = "-1";
if (isset($_GET['Country'])) {
  $col_state = $_GET['Country'];
}
mysqli_select_db($cov, $database_cov);
$query_state = sprintf("SELECT sjx_gen_countrystate.Country, sjx_gen_countrystate.`State` FROM sjx_gen_countrystate WHERE sjx_gen_countrystate.Country = %s ORDER BY sjx_gen_countrystate.`State` asc", GetSQLValueString($col_state, "text"));
$state = mysqli_query($cov, $query_state) or die(mysqli_error($cov));
$row_state = mysqli_fetch_assoc($state);
$totalRows_state = mysqli_num_rows($state);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<span class="greyBgd">
<select name="StateOfOrigin" class="innerBox" id="StateOfOrigin" title="<?php echo $row_state['State']; ?>">
  <option value="N/A" selected="selected">N/A</option>
  <?php
do {  
?>
  <option value="<?php echo $row_state['State']?>"><?php echo $row_state['State']?></option>
  <?php
} while ($row_state = mysqli_fetch_assoc($state));
  $rows = mysqli_num_rows($state);
  if($rows > 0) {
      mysqli_data_seek($state, 0);
	  $row_state = mysqli_fetch_assoc($state);
  }
?>
</select>
</span>
</body>
</html>
<?php
mysqli_free_result($state);
?>
