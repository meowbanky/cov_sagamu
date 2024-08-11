<?php  
 //load_data.php  
 $connect = mysqli_connect("localhost", "root", "oluwaseyi", "salary");  
 $output = ''; 
 $staff_id = $_POST['curremployee'];
 if (isSet($_POST['newdeductioncodeloan'])){
 	$allow_id = $_POST['newdeductioncodeloan'];
 }else if(isSet($_POST['newCashcodeloan'])){
 	$allow_id = $_POST['newCashcodeloan'];
	}else if(isSet($_POST['SelNewContinueLoan'])){
 	$allow_id = $_POST['SelNewContinueLoan'];
	}
 
 
 $sql_loan = "SELECT tbl_debt.loan_id, tbl_debt.staff_id,tbl_debt.allow_id, SUM(ifnull(tbl_debt.principal,0))+SUM(ifnull(tbl_debt.interest,0)) as loan, tbl_debt.date_insert, tbl_debt.inserted_by FROM tbl_debt WHERE staff_id = '".$staff_id."' AND allow_id = '".$allow_id."'";
 $result_loan = mysqli_query($connect,$sql_loan);
 $row_loan = mysqli_fetch_assoc($result_loan);
 $total_loan = mysqli_num_rows($result_loan);
 
 $sql_repayment = "SELECT tbl_repayment.repayment_id, tbl_repayment.staff_id, tbl_repayment.allow_id, (SUM(ifnull(tbl_repayment.value,0))+SUM(ifnull(tbl_repayment.cashPay,0))) as repayment FROM tbl_repayment WHERE staff_id = '".$staff_id."' and allow_id = '".$allow_id."'";
 $result_repayment = mysqli_query($connect,$sql_repayment);
 $row_repayment = mysqli_fetch_assoc($result_repayment);
 $total_repayment = mysqli_num_rows($result_repayment);
 
$balance = $row_loan['loan'] - $row_repayment['repayment'];
//print number_format($balance);
echo $balance;
?>
