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

//if ((isset($_POST["ButtonSearch"])) && ($_POST["ButtonSearch"] == "Search")) {
mysqli_select_db($cov,$database_cov);
$query_SearchResult = "SELECT tbl_personalinfo.memberid,tbl_personalinfo.passport, CONCAT(ifnull(tbl_personalinfo.sfxname,' '),tbl_personalinfo.Lname,' ,', tbl_personalinfo.Fname,' ',ifnull(tbl_personalinfo.Mname,' ')) AS 'PatientName', tbl_personalinfo.DOB, tbl_personalinfo.MobilePhone FROM tbl_personalinfo WHERE tbl_personalinfo.memberid like '%".$_GET['SearchMRN']."%' OR tbl_personalinfo.Fname like '%".$_GET['SearchMRN']."%' OR tbl_personalinfo.Lname like '%".$_GET['SearchMRN']."%' OR tbl_personalinfo.DOB like '%".$_GET['SearchMRN']."%' OR tbl_personalinfo.MobilePhone like '%".$_GET['SearchMRN']."%'";
$SearchResult = mysqli_query($cov,$query_SearchResult) or die(mysql_error());
$row_SearchResult = mysqli_fetch_assoc($SearchResult);
$totalRows_SearchResult = mysqli_num_rows($SearchResult);

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
              <th width="9%" scope="col">Click to Edit Record</th>
              <th width="10%" scope="col">Click to Upload Passport</th>
              <th width="10%" scope="col">MEMBER'S NO</th>
              <th width="41%" scope="col"> NAME</th>
              <th width="20%" scope="col">DATE OF BIRTH</th>
              <th width="20%" scope="col">PHONE NO.</th>
              <th width="20%" scope="col">Passport</th>
            </tr>
           
  <?php do { ?><tr>
    <td class="dataBox"><input name="btnMRN" type="radio" value="<?php echo $row_SearchResult['memberid']; ?>" onclick="parent.jumptoURL(this)"></td>
    <td class="dataBox"><input name="btnMRN" type="radio" value="<?php echo $row_SearchResult['memberid']; ?>" onclick="parent.jumptoPassport(this)" /></td>
    <td class="dataBox"><?php echo $row_SearchResult['memberid']; ?></td>
    <td class="dataBox"><?php echo $row_SearchResult['PatientName']; ?></td>
    <td class="dataBox"><?php if($totalRows_SearchResult > 0 ) { 
    if($row_SearchResult['DOB'] == 'NULL'){$row_SearchResult['DOB'] = date("Y/m/d");}
    $date=date_create($row_SearchResult['DOB']); echo date_format($date,"d-m-Y") ;} ?></td>
    <td class="dataBox"><?php echo $row_SearchResult['MobilePhone']; ?></td>
    <td class="dataBox"><span class="greyBgd"><img src="<?php echo $row_SearchResult['passport']; ?>" alt="passport" name="passport" width="50" height="50" id="passport" /></span></td>
  </tr>
    <?php } while ($row_SearchResult = mysqli_fetch_assoc($SearchResult)); ?>
              <?php } // Show if recordset not empty ?>
</table>
  <p align="center"><?php if ($totalRows_SearchResult == 0) { // Show if recordset empty ?>
    <span class="errorBox"> No Match Found!!! </span>
    <?php } // Show if recordset empty ?></p>
</div>
</body>
</html>
<?php
mysqli_free_result($SearchResult);
?>
