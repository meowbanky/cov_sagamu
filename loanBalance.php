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

$col_loanBalance = "-1";
if (isset($_GET['id'])) {
  $col_loanBalance = $_GET['id'];
}
mysqli_select_db($cov,$database_cov);
$query_loanBalance = sprintf("SELECT tlb_mastertransaction.memberid, ((sum(ifnull(tlb_mastertransaction.loanAmount,0)))- (sum(ifnull(tlb_mastertransaction.loanRepayment,0)))) as 'balance',((sum(ifnull(tlb_mastertransaction.interestCal,0)))- (sum(ifnull(tlb_mastertransaction.interestPaid,0)))) as 'interestBalance' FROM tlb_mastertransaction WHERE memberid = %s", GetSQLValueString($cov,$col_loanBalance, "int"));
$loanBalance = mysqli_query($cov,$query_loanBalance) or die(mysqli_error($cov));
$row_loanBalance = mysqli_fetch_assoc($loanBalance);
$totalRows_loanBalance = mysqli_num_rows($loanBalance);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>

</head>

<body>
<p>
  <input name="txtLoanBalance" type="text" class="innerBox" id="txtLoanBalance" value="<?php  ; if($totalRows_loanBalance == 0) {$row_loanBalance['balance'] = 0;} echo number_format($row_loanBalance['balance'],2,'.',','); ?>" readonly="readonly" />
</p>
<p>
  <input name="txtLoanBalance2" type="text" class="innerBox" id="txtLoanBalance2" value="<?php if($totalRows_loanBalance == 0) {$row_loanBalance['interestBalance'] = 0;} echo number_format($row_loanBalance['interestBalance'],2,'.',','); ?>" readonly="readonly" />
</p>
</body>
</html>
<?php
mysqli_free_result($loanBalance);
?>
