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
	
do { 


	
mysqli_select_db($cov, $database_cov);	
$balancesSQL = sprintf("SELECT tbl_personalinfo.memberid, concat(tbl_personalinfo.Lname,' , ', tbl_personalinfo.Fname,' ', ifnull( tbl_personalinfo.Mname,'')) AS namess, IFNULL((sum(tlb_mastertransaction.loanAmount)),0) AS Loan, IFNULL(((sum(tlb_mastertransaction.loanAmount))- sum(tlb_mastertransaction.loanRepayment)),0) AS Loanbalance, IFNULL((sum(tlb_mastertransaction.interest)-sum(tlb_mastertransaction.interestPaid)),0) as interestBalance
FROM tlb_mastertransaction RIGHT JOIN tbl_personalinfo ON tbl_personalinfo.memberid = tlb_mastertransaction.memberid
WHERE tbl_personalinfo.memberid = %s GROUP BY memberid", GetSQLValueString($cov,$row_member['memberid'], "text"));
						 
$Result2 = mysqli_query($cov,$balancesSQL) or die(mysqli_error($cov));	
$row_balances = mysqli_fetch_assoc($Result2);
//$totalRows_balances = mysqli_num_rows($row_balances);

//if ($row_deductions['memberid'] == '815'){ echo $row_balances['Loanbalance']."-".$row_balances['contLoan'];}

mysqli_select_db($cov, $database_cov);
$query_completed = "SELECT tlb_mastertransaction.memberid FROM tlb_mastertransaction WHERE memberid = '".$row_member['memberid']."' AND periodid = " . $row_periodCount['Periodid']. " AND completed = 1";
$completed = mysqli_query($cov,$query_completed) or die(mysqli_error($cov));
$row_completed = mysqli_fetch_assoc($completed);
$totalRows_completed = mysqli_num_rows($completed);

mysqli_select_db($cov, $database_cov);
$query_deductions = "SELECT IFNULL(sum(tbl_contributions.contribution),0) as contri FROM tbl_contributions WHERE membersid = '".$row_member['memberid']."' AND periodid = '".$row_periodCount['Periodid']."' GROUP BY membersid";
$deductions = mysqli_query($cov,$query_deductions) or die(mysqli_error($cov));
$row_deductions = mysqli_fetch_assoc($deductions);
$totalRows_deductions = mysqli_num_rows($deductions);


mysqli_select_db($cov, $database_cov);
$query_entry = "SELECT tbl_entryfees.entryFee_id, tbl_entryfees.memberid, tbl_entryfees.periodid, tbl_entryfees.Amount
FROM tbl_entryfees WHERE memberid = '".$row_member['memberid']."'";
$entry = mysqli_query($cov,$query_entry) or die(mysqli_error($cov));
$row_entry = mysqli_fetch_assoc($entry);
$totalRows_entry = mysqli_num_rows($entry);


$contribution_entry = 0;
if ($totalRows_entry > 0){
	
	
	 $contribution_entry = $row_deductions['contri'];
	 
	}else{
		if($row_deductions['contri'] > 1000){
			
			
		
		$insertSQLEntryMaster = sprintf("INSERT INTO tlb_mastertransaction (periodid, memberid, entryFee,completed) VALUES (%s,%s, %s,%s)",
                       	GetSQLValueString($cov,$row_periodCount['Periodid'], "int"),
					   	GetSQLValueString($cov,$row_member['memberid'], "text"),
					   	GetSQLValueString($cov,1000, "float"),
						GetSQLValueString($cov,(1), "int"));

					  mysqli_select_db($cov, $database_cov);
					  $Result1 = mysqli_query($cov,$insertSQLEntryMaster) or die(mysqli_error($cov));
  
  
					$insertSQLEntry = sprintf("INSERT INTO tbl_entryfees (periodid, memberid, Amount) VALUES 					(%s,%s, %s)",
                       	GetSQLValueString($cov,$row_periodCount['Periodid'], "int"),
					   	GetSQLValueString($cov,$row_member['memberid'], "text"),
					   	GetSQLValueString($cov,1000, "float"));

					  mysqli_select_db($cov, $database_cov);
					  $Result1 = mysqli_query($cov,$insertSQLEntry) or die(mysqli_error($cov));
					  $contribution_entry = $row_deductions['contri'] - 1000;
					  
					  
					  
			}else{
				
				$contribution_entry = $row_deductions['contri'];
				}
		
		
		}




//contribution

$contribution =  $contribution_entry ; //$row_deductions['contri'];
$interestBalance = $row_balances['interestBalance'];
$loanBalance = $row_balances['Loanbalance'];
$interestRate = $row_interestRate['value'];
$currentInterest = $row_balances['Loanbalance'] * $interestRate;
$interest = $interestBalance + $currentInterest;




if ($totalRows_completed > 0 ){
}else{
	if ($loanBalance  > 0) {
		
		
		if(($contribution == 0)){

	$insertSQL = sprintf("INSERT INTO tlb_mastertransaction (periodid, memberid, interest, completed) VALUES (%s,%s, %s, %s)",
                       	GetSQLValueString($cov,$row_periodCount['Periodid'], "int"),
					   	GetSQLValueString($cov,$row_member['memberid'], "text"),
					   	GetSQLValueString($cov,$currentInterest, "float"),
						GetSQLValueString($cov,(1), "int"));

  mysqli_select_db($cov, $database_cov);
  $Result1 = mysqli_query($cov,$insertSQL) or die(mysqli_error($cov));
	
	
	
	}elseif (($contribution > 0) and ($contribution > $interest)){
		$savings = 0; $balanceafterinterestdeduction = $contribution - $interest;
		
		if($balanceafterinterestdeduction < $loanBalance){
			$repayment = floor($balanceafterinterestdeduction);
			$repayment = number_format($repayment);
			$repayment = str_replace(",","",$repayment);
				$savings = number_format($balanceafterinterestdeduction - floor($balanceafterinterestdeduction),2) ;
				
				//0;
			}else{
				
				$repayment = $loanBalance;
				$savings = $balanceafterinterestdeduction - $loanBalance;
				
				
				}
		
		$insertSQL = sprintf("INSERT INTO tlb_mastertransaction (periodid, memberid, interest,interestPaid, loanRepayment,savings,completed) VALUES (%s,%s, %s,%s, %s,%s,%s)",
                       	GetSQLValueString($cov,$row_periodCount['Periodid'], "int"),
					   	GetSQLValueString($cov,$row_member['memberid'], "text"),
						GetSQLValueString($cov,$currentInterest, "float"),
					   	GetSQLValueString($cov,$interest, "float"),
						GetSQLValueString($cov,$repayment, "float"),
						GetSQLValueString($cov,$savings, "float"),
						GetSQLValueString($cov,(1), "int"));

						 mysqli_select_db($cov, $database_cov);
						 $Result1 = mysqli_query($cov,$insertSQL) or die(mysqli_error($cov));

}elseif(($contribution > 0) and ($contribution < $interest)){
	
	$insertSQL = sprintf("INSERT INTO tlb_mastertransaction (periodid, memberid, interest,interestPaid, loanRepayment,savings,completed) VALUES (%s,%s, %s,%s, %s,%s,%s)",
                       	GetSQLValueString($cov,$row_periodCount['Periodid'], "int"),
					   	GetSQLValueString($cov,$row_member['memberid'], "text"),
						GetSQLValueString($cov,$currentInterest, "float"),
					   	GetSQLValueString($cov,$contribution, "float"),
						GetSQLValueString($cov,0.0, "float"),
						GetSQLValueString($cov,0.0, "float"),
						GetSQLValueString($cov,(1), "int"));

						 mysqli_select_db($cov, $database_cov);
						 $Result1 = mysqli_query($cov,$insertSQL) or die(mysqli_error($cov));
	}
		
		
		
	}else{
				
					$shareSavings = sprintf("INSERT INTO tlb_mastertransaction (periodid, memberid, shares,savings,completed) VALUES (%s,%s, %s,%s,%s)",
                       	GetSQLValueString($cov,$row_periodCount['Periodid'], "int"),
					   	GetSQLValueString($cov,$row_member['memberid'], "text"),
						GetSQLValueString($cov,$contribution*$row_sharesRate['value'], "float"),
					   	GetSQLValueString($cov,$contribution*$row_savingsRate['value'], "float"),
						GetSQLValueString($cov,(1), "int"));

						 mysqli_select_db($cov, $database_cov);
						 $Result1 = mysqli_query($cov,$shareSavings) or die(mysqli_error($cov));
						 
						 
						 
		
		

	}
mysqli_select_db($cov, $database_cov);
$query_Batch = sprintf("SELECT (tbl_loan.loanamount) as loanamount, tbl_loan.loanid, tbl_loan.periodid, tbl_loan.memberid FROM tbl_loan WHERE tbl_loan.memberid= %s and periodid = %s", GetSQLValueString($cov,$row_member['memberid'], "text"),GetSQLValueString($cov,$row_periodCount['Periodid'], "int"));
$Batch = mysqli_query($cov,$query_Batch) or die(mysqli_error($cov));
$row_Batch = mysqli_fetch_assoc($Batch);
$totalRows_Batch = mysqli_num_rows($Batch);

if ($totalRows_Batch > 0){
	
	  $insertSQL_MasterTransaction = sprintf("INSERT INTO tlb_mastertransaction (periodid, memberid, loanid,loanAmount) VALUES (%s, %s, %s, %s)",
                      GetSQLValueString($cov,$row_periodCount['Periodid'], "int"),
					  GetSQLValueString($cov,$row_member['memberid'], "text"),
					   GetSQLValueString($cov,$row_Batch['loanid'], "int"),
                       GetSQLValueString($cov,doubleval($row_Batch['loanamount']), "double"));
                      //GetSQLValueString($cov,$_POST['txtInterest'], "double"));
					   
  mysqli_select_db($cov, $database_cov);
  $Result1 = mysqli_query($cov,$insertSQL_MasterTransaction) or die(mysqli_error($cov));
	
	}

}



 
// Javascript for updating the progress bar and information
   echo '<script language="javascript">
         document.getElementById("progress").innerHTML="<div align=\"center\" style=\"width:'.$percent.';background-color:#ddd; background-image:url(pbar-ani.gif)\">'.$percent.'</div>";
    	document.getElementById("information").innerHTML="'.$i.' row(s) processed.";
    	
	</script>';

    
// This is for the buffer achieve the minimum size in order to flush data
    echo str_repeat(' ',1024*64);

    
// Send output to browser immediately
	ob_end_flush();
    flush();
	echo '<script language="javascript">document.getElementById("information").innerHTML="Transaction of Member :- "'.$row_member['memberid'] . " Processing".'</script>';
//	echo "Transaction of Member :- " . $row_member['memberid'] . " Processing <br>" ;

   ob_start();
 //  sleep(3);
 
  
     
 } while ($row_member = mysqli_fetch_assoc($member)); 
  $i++;
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
