<?php
//Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

include_once('Connections/cov.php');
include_once('model.php');

if (isset($_GET['month'])) {
	$current_month = $_GET['month'];
} else {
	$current_month = -1;
}


mysqli_select_db($cov, $database_cov);
$sql = "SELECT
tbl_loan.memberid,
ANY_VALUE(tbl_loan.loanamount) as loanamount,
ANY_VALUE(Max(tbl_loan.periodid)) as period,
concat(tbl_personalinfo.Lname,' ',ifnull(tbl_personalinfo.Mname,''),' ',tbl_personalinfo.Fname) nammee
FROM
tbl_loan
INNER JOIN tbl_personalinfo ON tbl_personalinfo.memberid = tbl_loan.memberid
GROUP BY memberid
HAVING (period + 11) < " . $current_month . "
ORDER BY period, memberid desc";
$result = mysqli_query($cov, $sql);
$row = mysqli_fetch_assoc($result);
$total_row = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>
</head>

<body>
	<table>
		<thead <tr>
			<th class="greyBgdHeader"> NAME</th>
			<th class="greyBgdHeader"> Coop No</th>
			<th class="greyBgdHeader">LAST LOAN COLLECTED</th>
			<th class="greyBgdHeader">MONTH OF LAST LOAN</th>
			<th class="greyBgdHeader">CURRENT BALANCE</th>
			</tr>
		</thead>
		<tbody>
			<?php if ($total_row > 0) {
				do {

					mysqli_select_db($cov, $database_cov);
					$sql_bal = "SELECT
sum(tlb_mastertransaction.loanAmount) - sum(tlb_mastertransaction.loanRepayment) as balance
FROM
tlb_mastertransaction
WHERE
memberid = {$row['memberid']}
GROUP BY memberid";
					$result_bal = mysqli_query($cov, $sql_bal);
					$row_bal = mysqli_fetch_assoc($result_bal);


					mysqli_select_db($cov, $database_cov);
					$sql_period = "SELECT
tbpayrollperiods.PayrollPeriod
FROM
tbpayrollperiods
WHERE
Periodid = {$row['period']}";
					$result_period = mysqli_query($cov, $sql_period);
					$row_period = mysqli_fetch_assoc($result_period);

					mysqli_select_db($cov, $database_cov);
					$sql_loan = "SELECT
sum(tlb_mastertransaction.loanAmount) as loan
FROM
tlb_mastertransaction
WHERE
memberid = {$row['memberid']} and periodid = {$row['period']}
GROUP BY memberid";
					$result_loan = mysqli_query($cov, $sql_loan);
					$row_loan = mysqli_fetch_assoc($result_loan);


					if ($row_bal['balance'] > 0) {
						$balance = number_format($row_bal['balance'], 2);
						$loan = number_format($row_loan['loan'], 2);
			?>
						<tr>
							<td class="greyBgd"> <?php echo $row['nammee']; ?></td>
							<td class="greyBgd"><?php echo $row['memberid']; ?></td>
							<td class="greyBgd" align='right'><?php echo $loan; ?></td>
							<td class="greyBgd"><?php echo $row_period['PayrollPeriod']; ?></td>
							<td class="greyBgd" align='right'><?php echo $balance; ?></td>
						</tr>
			<?php }
				} while ($row = mysqli_fetch_assoc($result));
			} ?>
		</tbody>
	</table>
</body>

</html>