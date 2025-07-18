<?php require_once('Connections/cov.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($conn_vote, $theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
    {
      $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

      $theValue = function_exists("mysqli_real_escape_string") ? mysqli_real_escape_string($conn_vote, $theValue) : mysqli_escape_string($theValue);

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

mysqli_select_db($cov, $database_cov);
$query_title = "SELECT tbl_globa_settings.`value` FROM tbl_globa_settings WHERE tbl_globa_settings.setting_id = 1";
$title = mysqli_query($cov,$query_title) or die(mysqli_error($cov));
$row_title = mysqli_fetch_assoc($title);
$totalRows_title = mysqli_num_rows($title);

mysqli_select_db($cov, $database_cov);
$query_interestRate = "SELECT tbl_globa_settings.`value` FROM tbl_globa_settings where setting_id = 5";
$interestRate = mysqli_query($cov,$query_interestRate) or die(mysqli_error($cov));
$row_interestRate = mysqli_fetch_assoc($interestRate);
$totalRows_interestRate = mysqli_num_rows($interestRate);

mysqli_select_db($cov, $database_cov);
$query_sharesRate = "SELECT tbl_globa_settings.`value` FROM tbl_globa_settings WHERE setting_id = 3";
$sharesRate = mysqli_query($cov,$query_sharesRate) or die(mysqli_error($cov));
$row_sharesRate = mysqli_fetch_assoc($sharesRate);
$totalRows_sharesRate = mysqli_num_rows($sharesRate);

mysqli_select_db($cov, $database_cov);
$query_savingsRate = "SELECT tbl_globa_settings.`value` FROM tbl_globa_settings where setting_id = 4";
$savingsRate = mysqli_query($cov,$query_savingsRate) or die(mysqli_error($cov));
$row_savingsRate = mysqli_fetch_assoc($savingsRate);
$totalRows_savingsRate = mysqli_num_rows($savingsRate);


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $row_title['value']; ?> -  Member Contribution Processing</title>
<style>
.overlay{
    opacity:0.8;
    background-color:#ccc;
    position:fixed;
    width:100%;
    height:100%;
    top:0px;
    left:0px;
    z-index:1000;
	text-align: center;
    display:table-cell;
    
}
</style>

</head>

<body><div id="progress" style="width:500px;border:1px solid #ccc;"></div>
<!-- Progress information -->
<div id="information" style="width"></div>
<div id="information2" style="width"></div>
<?php

mysqli_select_db($cov, $database_cov);
$query_member = "SELECT * FROM tbl_personalinfo where status = 'Active'";
$member = mysqli_query($cov,$query_member) or die(mysqli_error($cov));
$row_member = mysqli_fetch_assoc($member);
$totalRows_member = mysqli_num_rows($member);




mysqli_select_db($cov, $database_cov);
$query_periodCount = "SELECT tbpayrollperiods.PayrollPeriod,tbpayrollperiods.Periodid FROM tbpayrollperiods WHERE Periodid BETWEEN ".$_GET['PeriodFrom'] . " and ".$_GET['PeriodTo'];
$periodCount = mysqli_query($cov,$query_periodCount) or die(mysqli_error($cov));
$row_periodCount = mysqli_fetch_assoc($periodCount);
$totalRows_periodCount = mysqli_num_rows($periodCount);

if (($totalRows_member > 0)) {
$i=1;


set_time_limit(0);
//ob_end_flush();
//ob_start();
//ob_end_flush();
$total = $totalRows_periodCount;   
//for( $i=0; $i <= $total; $i++ ){
// Calculate the percentation
    $percent = intval($i/$total * 100)."%";
	

mysqli_select_db($cov, $database_cov);
$sql = "SELECT tbpayrollperiods.PayrollPeriod,tbpayrollperiods.Periodid FROM tbpayrollperiods WHERE Periodid BETWEEN ".$_GET['PeriodFrom'] . " and ".$_GET['PeriodTo'];
$periodCount = mysqli_query($cov,$query_periodCount) or die(mysqli_error($cov));
$row_periodCount = mysqli_fetch_assoc($periodCount);
$totalRows_periodCount = mysqli_num_rows($periodCount);
//$run1 = $cov->query($sql);
//	if($run1->num_rows>0)
//	{
	
do {	
	
mysqli_select_db($cov, $database_cov);
$query_deductions = "SELECT IFNULL(sum(tbl_contributions.contribution),0) as contri FROM tbl_contributions WHERE membersid = '".$row_member['memberid']."' AND periodid = '".$row_periodCount['Periodid']."' GROUP BY membersid";
$deductions = mysqli_query($cov,$query_deductions) or die(mysqli_error($cov));
$row_deductions = mysqli_fetch_assoc($deductions);
$totalRows_deductions = mysqli_num_rows($deductions);

set_time_limit(0);
//ob_end_flush();
//ob_start();
//ob_end_flush();
$total = $totalRows_periodCount;   
//for( $i=0; $i <= $total; $i++ ){
// Calculate the percentation
    $percent = intval($i/$total * 100)."%";
	
echo $row_member['memberid']. ' ' ;	

do { 

	
mysqli_select_db($cov, $database_cov);	
$balancesSQL = sprintf("SELECT tbl_personalinfo.memberid, concat(tbl_personalinfo.Lname,' , ', tbl_personalinfo.Fname,' ', ifnull( tbl_personalinfo.Mname,'')) AS namess, IFNULL((sum(tlb_mastertransaction.loanAmount)),0) AS Loan, IFNULL(((sum(tlb_mastertransaction.loanAmount))- sum(tlb_mastertransaction.loanRepayment)),0) AS Loanbalance, IFNULL((sum(tlb_mastertransaction.interest)-sum(tlb_mastertransaction.interestPaid)),0) as interestBalance
FROM tlb_mastertransaction RIGHT JOIN tbl_personalinfo ON tbl_personalinfo.memberid = tlb_mastertransaction.memberid
WHERE tbl_personalinfo.memberid = %s GROUP BY memberid", GetSQLValueString($cov,$row_member['memberid'], "text"));
						 
$Result2 = mysqli_query($cov,$balancesSQL) or die(mysqli_error($cov));	
$row_balances = mysqli_fetch_assoc($Result2);

echo $row_member['memberid'].'- '.$row_balances['namess'] . '-'. $row_deductions['contri'].' - '. $row_periodCount['Periodid'].'<br>' ;
 
  
    
 } while ($row_member = mysqli_fetch_assoc($member)); 
	mysqli_data_seek($member,0);
 } while ($row_periodCount = mysqli_fetch_assoc($periodCount));
 
 
 
  

 echo '<script language="javascript">document.getElementById("information").innerHTML="Process completed"</script>';
// echo '<script language="javascript">setTimeout(function (){window.location.href = \'mastertransaction.php\';}, 5000);</script>';
}
?>
</body>
</html>
<?php
mysqli_free_result($deductions);

mysqli_free_result($title);

//mysqli_free_result($interestRate);

mysqli_free_result($sharesRate);

mysqli_free_result($savingsRate);
?>
