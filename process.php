<?php
session_start();
$sessionId = session_id();
$progressFile = __DIR__ . "/progress_$sessionId.json";
error_log("Progress file: " . $progressFile);
global $cov;
require_once('Connections/cov.php');

require_once __DIR__ . '/libs/services/NotificationService.php';

use App\Services\NotificationService;

// Initialize notification service
try {
    $notificationService = new NotificationService($cov);
} catch (Exception $e) {
    error_log("Failed to initialize notification service: " . $e->getMessage());
}

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

mysqli_select_db($cov, $database_cov);
$query_title = "SELECT tbl_globa_settings.`value` FROM tbl_globa_settings WHERE tbl_globa_settings.setting_id = 1";
$title = mysqli_query($cov, $query_title) or die(mysqli_error($cov));
$row_title = mysqli_fetch_assoc($title);
$totalRows_title = mysqli_num_rows($title);

mysqli_select_db($cov, $database_cov);
$query_interestRate = "SELECT tbl_globa_settings.`value` FROM tbl_globa_settings where setting_id = 5";
$interestRate = mysqli_query($cov, $query_interestRate) or die(mysqli_error($cov));
$row_interestRate = mysqli_fetch_assoc($interestRate);
$totalRows_interestRate = mysqli_num_rows($interestRate);

mysqli_select_db($cov, $database_cov);
$query_sharesRate = "SELECT tbl_globa_settings.`value` FROM tbl_globa_settings WHERE setting_id = 3";
$sharesRate = mysqli_query($cov, $query_sharesRate) or die(mysqli_error($cov));
$row_sharesRate = mysqli_fetch_assoc($sharesRate);
$totalRows_sharesRate = mysqli_num_rows($sharesRate);

mysqli_select_db($cov, $database_cov);
$query_savingsRate = "SELECT tbl_globa_settings.`value` FROM tbl_globa_settings where setting_id = 4";
$savingsRate = mysqli_query($cov, $query_savingsRate) or die(mysqli_error($cov));
$row_savingsRate = mysqli_fetch_assoc($savingsRate);
$totalRows_savingsRate = mysqli_num_rows($savingsRate);

mysqli_select_db($cov, $database_cov);
$query_entrySettings = "SELECT tbl_globa_settings.`value` FROM tbl_globa_settings where setting_id = 7";
$entrySettings  = mysqli_query($cov, $query_entrySettings) or die(mysqli_error($cov));
$row_entrySettings  = mysqli_fetch_assoc($entrySettings);
$totalRows_entrySettings  = mysqli_num_rows($entrySettings);

$entryFees = (int)($row_entrySettings['value']);

// session_start();
// $progressFile = __DIR__ . '/progress_' . session_id() . '.json';



?>
<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo $row_title['value']; ?> - Member Contribution Processing</title>
    <style>
    .overlay {
        opacity: 0.8;
        background-color: #ccc;
        position: fixed;
        width: 100%;
        height: 100%;
        top: 0px;
        left: 0px;
        z-index: 1000;
        text-align: center;
        display: table-cell;

    }
    </style>

</head>

<body>
    <div id="progress" style="width:500px;border:1px solid #ccc;"></div>
    <!-- Progress information -->
    <div id="information" style="width:0"></div>
    <div id="information2" style="width:0"></div>
    <?php

	mysqli_select_db($cov, $database_cov);
	$query_member = "SELECT * FROM tbl_personalinfo where status = 'Active'";
	$member = mysqli_query($cov, $query_member) or die(mysqli_error($cov));
	$row_member = mysqli_fetch_assoc($member);
	$totalRows_member = mysqli_num_rows($member);

		file_put_contents($progressFile, json_encode([
			"percent" => "0%",
			"current" => 0,
			"total" => $totalRows_member,
			"message" => "Initializing..."
		]));

	if (($totalRows_member > 0)) {
		// === Refactor: Define constants and helpers ===
		define('LOAN_EARLY_DAY_CUTOFF', 20);
		define('COMPLETED_STATUS', 1);

		function db_query($conn, $query) {
			$result = mysqli_query($conn, $query);
			if (!$result) {
				throw new Exception(mysqli_error($conn));
			}
			return $result;
		}

		function db_fetch_assoc($result) {
			return mysqli_fetch_assoc($result);
		}

		function processMember($cov, $database_cov, $row_member, $row_interestRate, $row_sharesRate, $row_savingsRate, $entryFees, $progressFile, $i, $totalRows_member, $notificationService) {
			$loans_early = [];
			$loans_late = [];
			// Check if already processed
			$query_completed = "SELECT tlb_mastertransaction.memberid FROM tlb_mastertransaction WHERE memberid = '" . $row_member['memberid'] . "' AND periodid = " . $_GET["PeriodID"] . " AND completed = " . COMPLETED_STATUS;
			$completed = db_query($cov, $query_completed);
			$totalRows_completed = mysqli_num_rows($completed);
			mysqli_free_result($completed);
			// Deductions
			$query_deductions = "SELECT IFNULL(sum(tbl_contributions.contribution),0) as contri, IFNULL(sum(tbl_contributions.special_savings),0) as special_savings FROM tbl_contributions WHERE membersid = '" . $row_member['memberid'] . "' AND periodid = '" . $_GET['PeriodID'] . "' AND pay_method = 0 GROUP BY membersid";
			$deductions = db_query($cov, $query_deductions);
			$row_deductions = db_fetch_assoc($deductions);
			$totalRows_deductions = mysqli_num_rows($deductions);
			mysqli_free_result($deductions);
			if ($totalRows_deductions == 0) {
				$row_deductions['contri'] = 0;
			}
			// Entry fees
			$query_entry = "SELECT tbl_entryfees.entryFee_id, tbl_entryfees.memberid, tbl_entryfees.periodid, tbl_entryfees.Amount FROM tbl_entryfees WHERE memberid = '" . $row_member['memberid'] . "'";
			$entry = db_query($cov, $query_entry);
			$row_entry = db_fetch_assoc($entry);
			$totalRows_entry = mysqli_num_rows($entry);
			mysqli_free_result($entry);
			$contribution_entry = 0;
			if ($totalRows_entry > 0) {
				$contribution_entry = $row_deductions['contri'];
			} else {
				if ($row_deductions['contri'] > $entryFees) {
					$insertSQLEntryMaster = sprintf(
						"INSERT INTO tlb_mastertransaction (periodid, memberid, entryFee,completed) VALUES (%s,%s, %s,%s)",
						GetSQLValueString($cov, $_GET["PeriodID"], "int"),
						GetSQLValueString($cov, $row_member['memberid'], "text"),
						GetSQLValueString($cov, $entryFees, "int"),
						GetSQLValueString($cov, COMPLETED_STATUS, "int")
					);
					db_query($cov, $insertSQLEntryMaster);
					$insertSQLEntry = sprintf(
						"INSERT INTO tbl_entryfees (periodid, memberid, Amount) VALUES (%s,%s, %s)",
						GetSQLValueString($cov, $_GET["PeriodID"], "int"),
						GetSQLValueString($cov, $row_member['memberid'], "text"),
						GetSQLValueString($cov, $entryFees, "int")
					);
					db_query($cov, $insertSQLEntry);
					$contribution_entry = $row_deductions['contri'] - $entryFees;
				} else {
					$contribution_entry = $row_deductions['contri'];
				}
			}
			// Online payment check
			$query_OnlinePaymentCheck = "SELECT tlb_mastertransaction.memberid,IFNULL(tlb_mastertransaction.pay_method,1) AS pay_method FROM tlb_mastertransaction WHERE memberid = '" . $row_member['memberid'] . "' AND periodid = " . $_GET["PeriodID"] . " AND pay_method = 1";
			$OnlinePaymentCheck = db_query($cov, $query_OnlinePaymentCheck);
			$totalRows_OnlinePaymentCheck = mysqli_num_rows($OnlinePaymentCheck);
			mysqli_free_result($OnlinePaymentCheck);
			$contribution = $contribution_entry;
			// Already processed?
			if ($totalRows_completed > 0) {
				file_put_contents($progressFile, json_encode([
					'percent' => intval($i / $totalRows_member * 100) . "%",
					'current' => $i,
					'total' => $totalRows_member,
					'message' => "{$row_member['memberid']} already processed. Skipping..."
				]));
				return;
			}
			// Loan batch
			$query_Batch = sprintf(
				"SELECT tbl_loan.loanamount, tbl_loan.loanid, tbl_loan.periodid, tbl_loan.memberid, tbl_loan.loan_date FROM tbl_loan WHERE tbl_loan.memberid = %s AND periodid = %s",
				GetSQLValueString($cov, $row_member['memberid'], "text"),
				GetSQLValueString($cov, $_GET["PeriodID"], "int")
			);
			$Batch = db_query($cov, $query_Batch);
			while ($row_Batch = db_fetch_assoc($Batch)) {
				$loanDay = intval(date('d', strtotime($row_Batch['loan_date'])));
				if ($loanDay <= LOAN_EARLY_DAY_CUTOFF) {
					$loans_early[] = $row_Batch;
				} else {
					$loans_late[] = $row_Batch;
				}
			}
			mysqli_free_result($Batch);
			// Insert early loans
			foreach ($loans_early as $loan) {
				$insertSQL_MasterTransaction = sprintf(
					"INSERT INTO tlb_mastertransaction (periodid, memberid, loanid,loanAmount) VALUES (%s, %s, %s, %s)",
					GetSQLValueString($cov, $_GET["PeriodID"], "int"),
					GetSQLValueString($cov, $row_member['memberid'], "text"),
					GetSQLValueString($cov, $loan['loanid'], "int"),
					GetSQLValueString($cov, doubleval($loan['loanamount']), "double")
				);
				db_query($cov, $insertSQL_MasterTransaction);
			}
			// Special savings
			if ($totalRows_deductions > 0 && $row_deductions['special_savings'] > 0) {
				$insertSQLspecialSaving = sprintf(
					"INSERT INTO tlb_mastertransaction (periodid, memberid, savings,completed) VALUES (%s,%s, %s,%s)",
					GetSQLValueString($cov, $_GET["PeriodID"], "int"),
					GetSQLValueString($cov, $row_member['memberid'], "text"),
					GetSQLValueString($cov, $row_deductions['special_savings'], "int"),
					GetSQLValueString($cov, COMPLETED_STATUS, "int")
				);
				db_query($cov, $insertSQLspecialSaving);
			}
			// Balances
			$balancesSQL = sprintf("SELECT tbl_personalinfo.memberid, concat(tbl_personalinfo.Lname,' , ', tbl_personalinfo.Fname,' ', ifnull( tbl_personalinfo.Mname,'')) AS namess, IFNULL((sum(tlb_mastertransaction.loanAmount)),0) AS Loan, IFNULL(((sum(tlb_mastertransaction.loanAmount))- sum(tlb_mastertransaction.loanRepayment)),0) AS Loanbalance, IFNULL((sum(tlb_mastertransaction.interest)-sum(tlb_mastertransaction.interestPaid)),0) as interestBalance FROM tlb_mastertransaction RIGHT JOIN tbl_personalinfo ON tbl_personalinfo.memberid = tlb_mastertransaction.memberid WHERE tbl_personalinfo.memberid = %s GROUP BY memberid", GetSQLValueString($cov, $row_member['memberid'], "text"));
			$Result2 = db_query($cov, $balancesSQL);
			$row_balances = db_fetch_assoc($Result2);
			mysqli_free_result($Result2);
			$interestBalance = $row_balances['interestBalance'];
			$loanBalance = $row_balances['Loanbalance'];
			$interestRate = $row_interestRate['value'] * $row_member['interest'];
			$currentInterest = ($totalRows_OnlinePaymentCheck > 0) ? 0 : $row_balances['Loanbalance'] * $interestRate;
			$interest = $interestBalance + $currentInterest;
			if ($loanBalance > 0) {
				if ($contribution == 0) {
					$insertSQL = sprintf(
						"INSERT INTO tlb_mastertransaction (periodid, memberid, interest, completed) VALUES (%s,%s, %s, %s)",
						GetSQLValueString($cov, $_GET["PeriodID"], "int"),
						GetSQLValueString($cov, $row_member['memberid'], "text"),
						GetSQLValueString($cov, $currentInterest, "float"),
						GetSQLValueString($cov, COMPLETED_STATUS, "int")
					);
					db_query($cov, $insertSQL);
				} elseif (($contribution > 0) && ($contribution > $interest)) {
					$balanceafterinterestdeduction = $contribution - $interest;
					if ($balanceafterinterestdeduction < $loanBalance) {
						$repayment = floor($balanceafterinterestdeduction);
						$savings = number_format($balanceafterinterestdeduction - floor($balanceafterinterestdeduction), 2);
					} else {
						$repayment = $loanBalance;
						$savings = $balanceafterinterestdeduction - $loanBalance;
					}
					$insertSQL = sprintf(
						"INSERT INTO tlb_mastertransaction (periodid, memberid, interest,interestPaid, loanRepayment,savings,completed) VALUES (%s,%s, %s,%s, %s,%s,%s)",
						GetSQLValueString($cov, $_GET["PeriodID"], "int"),
						GetSQLValueString($cov, $row_member['memberid'], "text"),
						GetSQLValueString($cov, $currentInterest, "float"),
						GetSQLValueString($cov, $interest, "float"),
						GetSQLValueString($cov, $repayment, "float"),
						GetSQLValueString($cov, $savings, "float"),
						GetSQLValueString($cov, COMPLETED_STATUS, "int")
					);
					db_query($cov, $insertSQL);
				} elseif (($contribution > 0) && ($contribution < $interest)) {
					$insertSQL = sprintf(
						"INSERT INTO tlb_mastertransaction (periodid, memberid, interest,interestPaid, loanRepayment,savings,completed) VALUES (%s,%s, %s,%s, %s,%s,%s)",
						GetSQLValueString($cov, $_GET["PeriodID"], "int"),
						GetSQLValueString($cov, $row_member['memberid'], "text"),
						GetSQLValueString($cov, $currentInterest, "float"),
						GetSQLValueString($cov, $contribution, "float"),
						GetSQLValueString($cov, 0.0, "float"),
						GetSQLValueString($cov, 0.0, "float"),
						GetSQLValueString($cov, COMPLETED_STATUS, "int")
					);
					db_query($cov, $insertSQL);
				}
			} else {
				$shareSavings = sprintf(
					"INSERT INTO tlb_mastertransaction (periodid, memberid, shares,savings,completed) VALUES (%s,%s, %s,%s,%s)",
					GetSQLValueString($cov, $_GET["PeriodID"], "int"),
					GetSQLValueString($cov, $row_member['memberid'], "text"),
					GetSQLValueString($cov, $contribution * $row_sharesRate['value'], "float"),
					GetSQLValueString($cov, $contribution * $row_savingsRate['value'], "float"),
					GetSQLValueString($cov, COMPLETED_STATUS, "int")
				);
				db_query($cov, $shareSavings);
			}
			// Insert late loans
			foreach ($loans_late as $loan) {
				$insertSQL_LateLoan = sprintf(
					"INSERT INTO tlb_mastertransaction (periodid, memberid, loanid, loanAmount) VALUES (%s, %s, %s, %s)",
					GetSQLValueString($cov, $_GET["PeriodID"], "int"),
					GetSQLValueString($cov, $row_member['memberid'], "text"),
					GetSQLValueString($cov, $loan['loanid'], "int"),
					GetSQLValueString($cov, doubleval($loan['loanamount']), "double")
				);
				db_query($cov, $insertSQL_LateLoan);
			}
			// Notification
			if (isset($_GET['sms']) && $_GET['sms'] == 1) {
				try {
					$notificationService->sendTransactionNotification(
						$row_member['memberid'],
						$_GET["PeriodID"]
					);
				} catch (Exception $e) {
					error_log("Failed to send notification: " . $e->getMessage());
				}
			}
			// Progress
			file_put_contents($progressFile, json_encode([
				'percent' => intval($i / $totalRows_member * 100) . "%",
				'current' => $i,
				'total' => $totalRows_member,
				'message' => "Processing member: {$row_member['memberid']}"
			]));
		}

		$i = 1;
		do {
			set_time_limit(0);
			try {
				processMember($cov, $database_cov, $row_member, $row_interestRate, $row_sharesRate, $row_savingsRate, $entryFees, $progressFile, $i, $totalRows_member, $notificationService);
			} catch (Exception $e) {
				error_log("Error processing member {$row_member['memberid']}: " . $e->getMessage());
			}
			// Javascript for updating the progress bar and information
			echo '<script language="javascript">
         document.getElementById("progress").innerHTML="<div align=\"center\" style=\"width:' . intval($i / $totalRows_member * 100) . '%;background-color:#ddd; background-image:url(pbar-ani.gif)\">' . intval($i / $totalRows_member * 100) . '%</div>";
    	document.getElementById("information").innerHTML="' . $i . ' row(s) processed.";
    	
	</script>';


			// This is for the buffer achieve the minimum size in order to flush data
			echo str_repeat(' ', 1024 * 64);


			// Send output to browser immediately
			ob_end_flush();
			flush();
			echo '<script language="javascript">document.getElementById("information").innerHTML="Transaction of Member :- "' . $row_member['memberid'] . " Processing" . '</script>';
			//echo "Transaction of Member :- " . $row_member['memberid'] . " Processing <br>" ;

			ob_start();
			//  sleep(3);

			// Write progress to JSON file for AJAX polling
			// Increment the counter		
			$i++;
		} while ($row_member = mysqli_fetch_assoc($member));

		file_put_contents($progressFile, json_encode([
			'percent' => '100%',
			'current' => $totalRows_member,
			'total' => $totalRows_member,
			'message' => 'Process completed.',
			'done' => true
		]));
		
		echo '<script language="javascript">document.getElementById("information").innerHTML="Process completed"</script>';
		echo '<script language="javascript">setTimeout(function (){window.location.href = \'mastertransaction.php\';}, 5000);</script>';
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