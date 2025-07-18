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
$query_SearchResult = "SELECT tbl_personalinfo.patientid, CONCAT(sfxname,' ',tbl_personalinfo.Lname,' ,', tbl_personalinfo.Fname,' ',ifnull(tbl_personalinfo.Mname,' ')) AS 'PatientName', tbl_personalinfo.DOB, tbl_personalinfo.MobilePhone FROM tbl_personalinfo WHERE tbl_personalinfo.patientid like '%".$_GET['SearchMRN']."%' OR tbl_personalinfo.Fname like '%".$_GET['SearchMRN']."%' OR tbl_personalinfo.Lname like '%".$_GET['SearchMRN']."%' OR tbl_personalinfo.DOB like '%".$_GET['SearchMRN']."%' OR tbl_personalinfo.MobilePhone like '%".$_GET['SearchMRN']."%'";
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

<body><div id="patSearchResult"><table width="100%" border="0">
           <?php if ($totalRows_SearchResult > 0) { ?> <tr class="mainNav">
              <th width="9%" scope="col">&nbsp;</th>
              <th width="10%" scope="col">MR NO</th>
              <th width="41%" scope="col">PATIENT NAME</th>
              <th width="20%" scope="col">DATE OF BIRTH</th>
              <th width="20%" scope="col">PHONE NO.</th>
            </tr>
           
  <?php do { ?><tr>
    <td class="dataBox"><input name="btnMRN" type="radio" value="<?php echo $row_SearchResult['patientid']; ?>" onclick="parent.jumptoURL(this)"></td>
    <td class="dataBox"><?php echo $row_SearchResult['patientid']; ?></td>
    <td class="dataBox"><?php echo $row_SearchResult['PatientName']; ?></td>
    <td class="dataBox"><?php $date=date_create($row_SearchResult['DOB']); echo date_format($date,"d-m-Y")?></td>
    <td class="dataBox"><?php echo $row_SearchResult['MobilePhone']; ?></td>
  </tr><?php } while ($row_SearchResult = mysql_fetch_assoc($SearchResult)); ?>
              <?php } // Show if recordset not empty ?>
</table>
  <p align="center"><?php if ($totalRows_SearchResult == 0) { // Show if recordset empty ?>
    <span class="errorBox"> No Match Found!!! </span>
    <?php } // Show if recordset empty ?></p>
</div>
</body>
</html>
<?php
mysql_free_result($SearchResult);
?>
