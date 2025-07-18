<?php require_once('Connections/hms.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  

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

$col_status = "-1";
if (isset($_GET['id'])) {
  $col_status = $_GET['id'];
}

mysql_select_db($database_hms, $hms);
$query_status = sprintf("SELECT tbl_personalinfo.patientid, concat(tbl_personalinfo.Lname,' , ', tbl_personalinfo.Fname,' ', ifnull( tbl_personalinfo.Mname,'')) as namess, (sum(tlb_mastertransaction.Contribution)+sum(tlb_mastertransaction.withdrawal)) as Contribution, (sum(tlb_mastertransaction.loanAmount)+ sum(tlb_mastertransaction.interest)) as Loan, ((sum(tlb_mastertransaction.loanAmount)+ sum(tlb_mastertransaction.interest))- sum(tlb_mastertransaction.loanRepayment)) as Loanbalance, sum(tlb_mastertransaction.withdrawal) as withdrawal FROM tlb_mastertransaction INNER JOIN tbl_personalinfo ON tbl_personalinfo.patientid = tlb_mastertransaction.memberid where patientid = %s GROUP BY patientid", GetSQLValueString($col_status, "text"));
$status = mysql_query($query_status, $hms) or die(mysql_error());
$row_status = mysql_fetch_assoc($status);
$totalRows_status = mysql_num_rows($status);


echo "<p align=\"left\"> <strong>".number_format($row_status['Loanbalance'],2)."</strong>";

mysql_free_result($status);
?>
