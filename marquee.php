<?php require_once('Connections/cov.php'); ?>
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

mysqli_select_db($cov,$database_cov);
$query_activeMembers = "SELECT count(tbl_personalinfo.memberid) FROM tbl_personalinfo WHERE `Status` = 'Active'";
$activeMembers = mysqli_query($cov,$query_activeMembers) or die(mysql_error());
$row_activeMembers = mysqli_fetch_assoc($activeMembers);
$totalRows_activeMembers = mysqli_num_rows($activeMembers);

$maxRows_gender = 10;
$pageNum_gender = 0;
if (isset($_GET['pageNum_gender'])) {
  $pageNum_gender = $_GET['pageNum_gender'];
}
$startRow_gender = $pageNum_gender * $maxRows_gender;

mysqli_select_db($cov,$database_cov);
$query_gender = "SELECT count(gender),gender FROM tbl_personalinfo WHERE `Status` = 'Active' GROUP BY gender";
$query_limit_gender = sprintf("%s LIMIT %d, %d", $query_gender, $startRow_gender, $maxRows_gender);
$gender = mysqli_query($cov,$query_limit_gender) or die(mysql_error());
$row_gender = mysqli_fetch_assoc($gender);

if (isset($_GET['totalRows_gender'])) {
  $totalRows_gender = $_GET['totalRows_gender'];
} else {
  $all_gender = mysqli_query($cov,$query_gender);
  $totalRows_gender = mysqli_num_rows($all_gender);
}
$totalPages_gender = ceil($totalRows_gender/$maxRows_gender)-1;

mysqli_select_db($cov,$database_cov);
$query_contribution = "SELECT SUM(tlb_mastertransaction.savings) as shares,SUM(tlb_mastertransaction.shares) as savings FROM tlb_mastertransaction";
$contribution = mysqli_query($cov,$query_contribution) or die(mysql_error());
$row_contribution = mysqli_fetch_assoc($contribution);
$totalRows_contribution = mysqli_num_rows($contribution);

mysqli_select_db($cov,$database_cov);
$query_loanDebt = "SELECT (SUM(tlb_mastertransaction.loanAmount))-(SUM(tlb_mastertransaction.loanRepayment)) as 'LoanDebt' FROM tlb_mastertransaction";
$loanDebt = mysqli_query($cov,$query_loanDebt) or die(mysql_error());
$row_loanDebt = mysqli_fetch_assoc($loanDebt);
$totalRows_loanDebt = mysqli_num_rows($loanDebt);
?>

<marquee direction="down">
<p><strong><font color="#FF0000">SMS BALANCE:
<?php
try{
//echo number_format(curlPost('http://api.ebulksms.com/balance/cov@emmaggi.com/9e6ce612af1fa2dc982e668176e806435830e5ff'));
 $response = curlPost('https://api.ng.termii.com/api/get-balance?api_key=TLYa2oT5vTpT3X4r3fSv2lSfErDApbmhbOAjOP3ituAA2XnLYMFIqzrq3leU1y');

$jsonobj = $response;

$obj = json_decode($jsonobj);

echo number_format($obj->balance) ;


}
catch(Exception $e) 	{
    echo '0';
}

function curlPost($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if ($error !== '') {
        throw new \Exception($error);
    }

    return $response;
}
?></font>
</strong>
<br />
<p><strong> Active Members: - <?php echo $row_activeMembers['count(tbl_personalinfo.memberid)']; ?></strong></p>
        <?php do { ?>
          <p><strong><?php echo $row_gender['gender']; ?>:<?php echo $row_gender['count(gender)']; ?></strong></p>
          <?php } while ($row_gender = mysqli_fetch_assoc($gender)); ?>
        <p><strong>Savings:<?php echo number_format($row_contribution['savings'],2); ?></strong></p>
        <p><strong>Shares:<?php echo number_format($row_contribution['shares'],2); ?></strong></p>
        
   <p><strong>Loan:<?php echo number_format($row_loanDebt['LoanDebt'],2); ?></strong></p>
     
</marquee>


<?php
mysqli_free_result($activeMembers);

mysqli_free_result($gender);

mysqli_free_result($contribution);

mysqli_free_result($loanDebt);
?>
