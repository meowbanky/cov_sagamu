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

$col_status = "-1";
if (isset($_GET['id'])) {
  $col_status = $_GET['id'];
}

$col_period = "-1";
if (isset($_GET['period'])) {
  $col_period = $_GET['period'];
}



$period_perod = "-1";
if (isset($_GET['period'])) {
  $period_perod = $_GET['period'];
}
mysqli_select_db($cov,$database_cov);
$query_period = sprintf("SELECT * from tbpayrollperiods where Periodid <= %s", GetSQLValueString($cov,$period_perod, "int"));
$perod = mysqli_query($cov,$query_period) or die(mysqli_error($cov));
$row_perod = mysqli_fetch_assoc($perod);
$totalRows_perod = mysqli_num_rows($perod);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html><head>


    <title><?php echo $row_title['value']; ?> -  Members Status</title>
    <link rel="shortcut icon" href="favicon (1).ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="skills_files/oouth.css" rel="stylesheet" type="text/css">
    <link href="css/output.css" rel="stylesheet" type="text/css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>


    <script>

        var isNS4=(navigator.appName ==="Netscape")?1:0;

        function auto_logout(iSessionTimeout,iSessTimeOut,sessiontimeout)

        {

            window.setTimeout('', iSessionTimeout);

            window.setTimeout('winClose()', iSessTimeOut);

        }

        function winClose() {

            //alert("Your Application session is expired.");

            if(!isNS4)

            {

                window.navigate("index.php");

            }

            else

            {

                window.location="index.php";

            }

        }

        auto_logout(1440000,1500000,1500)

    </script>
    <style type="text/css">
        body {
            font-family: Helvetica;
            font-size: 11px;
            color: #000;
        }

        h3 {
            margin: 0px;
            padding: 0px;
        }
    </style>
</head>

<body>
<div class="overflow-x-auto">
    <table class="min-w-full bg-gray-700 text-sm">
        <thead class="dark:bg-gray-700 dark:text-gray-400 uppercase">
        <tr class="bg-gray-700 text-white">
            <th class="py-2 px-4 text-left">Staff ID</th>
            <th class="py-2 px-4 text-left">Name</th>
            <th class="py-2 px-4 text-left">Month</th>
            <th class="py-2 px-4 text-right">Entry Fee</th>
            <th class="py-2 px-4 text-right">Shares</th>
            <th class="py-2 px-4 text-right">Savings</th>
            <th class="py-2 px-4 text-right">Loan</th>
            <th class="py-2 px-4 text-right">Loan Repayment</th>
            <th class="py-2 px-4 text-right">Loan Balance</th>
            <th class="py-2 px-4 text-right">Interest</th>
            <th class="py-2 px-4 text-right">Interest Paid</th>
            <th class="py-2 px-4 text-right">Unpaid Interest</th>
        </tr>
        </thead>
        <tbody>
        <?php do {
            mysqli_select_db($cov, $database_cov);
            $query_status = sprintf("SELECT
                tbl_personalinfo.memberid,
                tlb_mastertransaction.transactionid,
                concat(tbl_personalinfo.Lname,' , ', tbl_personalinfo.Fname,' ', ifnull(tbl_personalinfo.Mname,'')) AS namess,
                ifnull((Sum(tlb_mastertransaction.loanAmount)),0) AS loan,
                ifnull(Sum(tlb_mastertransaction.loanRepayment),0) AS loanrepayments,
                ifnull(Sum(tlb_mastertransaction.withdrawal),0) AS withrawals,
                ((ifnull(Sum(tlb_mastertransaction.loanRepayment),0)+ifnull(sum(tlb_mastertransaction.entryFee),0)+ifnull(sum(tlb_mastertransaction.savings),0)+
                ifnull(sum(tlb_mastertransaction.shares),0)+ifnull(sum(tlb_mastertransaction.interestPaid),0))) AS total,
                tbpayrollperiods.PayrollPeriod,
                tlb_mastertransaction.periodid,
                ifnull(sum(tlb_mastertransaction.entryFee),0) as entryFee,
                ifnull(sum(tlb_mastertransaction.savings),0) as savings,
                ifnull(sum(tlb_mastertransaction.shares),0) as shares,
                ifnull(sum(tlb_mastertransaction.interestPaid),0) as interestPaid,ifnull(sum(tlb_mastertransaction.interest),0) as interest
                FROM
                tbl_personalinfo
                INNER JOIN tlb_mastertransaction ON tbl_personalinfo.memberid = tlb_mastertransaction.memberid
                INNER JOIN tbpayrollperiods ON tbpayrollperiods.Periodid = tlb_mastertransaction.periodid
                LEFT JOIN tbl_refund ON tbl_refund.membersid = tbl_personalinfo.memberid AND tbl_refund.periodid = tbpayrollperiods.Periodid where tbl_personalinfo.memberid = %s AND tlb_mastertransaction.periodid <= ".$row_perod['Periodid']." GROUP BY tbl_personalinfo.memberid", GetSQLValueString($cov,$col_status, "text"));
            $status = mysqli_query($cov, $query_status) or die(mysqli_error($cov));
            $row_status = mysqli_fetch_assoc($status);
            $totalRows_status = mysqli_num_rows($status);

            mysqli_select_db($cov, $database_cov);
            $query_loan = sprintf("SELECT (ifnull(sum(loanamount),0)) as loanamount,(sum(tlb_mastertransaction.loanRepayment)) as loanRepay,ifnull(sum(tlb_mastertransaction.repayment_bank),0) as bank from tlb_mastertransaction WHERE memberid = %s AND tlb_mastertransaction.periodid = ".$row_perod['Periodid']."", GetSQLValueString($cov,$col_status, "text"));
            $loan = mysqli_query($cov, $query_loan) or die(mysqli_error($cov));
            $row_loan = mysqli_fetch_assoc($loan);
            $totalRows_loan = mysqli_num_rows($loan);

            if($totalRows_status > 0) { ?>
                <tr class="bg-white border-b border-gray-200">
                    <td class="py-2 px-4"><?php echo $row_status['memberid']; ?></td>
                    <td class="py-2 px-4"><?php echo $row_status['namess']; ?></td>
                    <td class="py-2 px-4"><?php echo $row_perod['PayrollPeriod']; ?></td>
                    <td class="py-2 px-4 text-right"><?php echo number_format($row_status['entryFee'], 2, '.', ','); ?></td>
                    <td class="py-2 px-4 text-right"><?php echo number_format($row_status['shares'], 2, '.', ','); ?></td>
                    <td class="py-2 px-4 text-right"><?php echo number_format($row_status['savings'], 2, '.', ','); ?></td>
                    <td class="py-2 px-4 text-right"><?php echo number_format($row_loan['loanamount'], 2, '.', ','); ?></td>
                    <td class="py-2 px-4 text-right"><?php echo number_format($row_loan['loanRepay'], 2, '.', ','); ?></td>
                    <td class="py-2 px-4 text-right"><?php echo number_format($row_status['loan'] - $row_status['loanrepayments'], 2); ?></td>
                    <td class="py-2 px-4 text-right"><?php echo number_format($row_status['interest'], 2, '.', ','); ?></td>
                    <td class="py-2 px-4 text-right"><?php echo number_format($row_status['interestPaid'], 2, '.', ','); ?></td>
                    <td class="py-2 px-4 text-right"><?php echo number_format($row_status['interestPaid'] - $row_status['interest'], 2, '.', ','); ?></td>
                </tr>
            <?php }
        } while ($row_perod = mysqli_fetch_assoc($perod)); ?>
        </tbody>
    </table>
</div>


</body>
</html>
<?php
mysqli_free_result($status);

mysqli_free_result($perod);
?>
