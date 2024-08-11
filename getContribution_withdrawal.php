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

$Id = "-1";
if (isset($_GET['id'])) {
  $Id = (get_magic_quotes_gpc()) ? $_GET['id'] : addslashes($_GET['id']);
}else {$Id =  -1;}
mysqli_select_db($cov,$database_cov);
$query_contriution = "SELECT Sum(tlb_mastertransaction.savings)+ Sum(tlb_mastertransaction.shares) as contribution FROM tlb_mastertransaction WHERE memberid = '$Id'";
$contriution = mysqli_query($cov,$query_contriution) or die(mysql_error());
$row_contriution = mysqli_fetch_assoc($contriution);
$totalRows_contriution = mysqli_num_rows($contriution);


		?><?php echo '<strong>'.number_format($row_contriution['contribution'],2) . '</strong>'; ?>

<input id="contribution" name="contribution" type="hidden" value="<?php echo $row_contriution['contribution'] ; ?>" />
<?php
mysqli_free_result($contriution);
?>
