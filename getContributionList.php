<?php
require_once('Connections/cov.php');
session_start();
if (!isset($_SESSION['UserID'])){
    header("Location:index.php");
    exit();
}

if (!function_exists("GetSQLValueString")) {
    function GetSQLValueString($conn_vote, $theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") {
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

$periodid = isset($_GET['periodid']) ? GetSQLValueString($cov, $_GET['periodid'], 'int') : 'NULL';

mysqli_select_db($cov, $database_cov);

// Query for total sum
$query_totalsum = "
    SELECT SUM(tbl_contributions.contribution) AS contribu
    FROM tbl_contributions
    INNER JOIN tbl_personalinfo ON tbl_personalinfo.memberid = tbl_contributions.membersid
    WHERE `Status` = 'Active' AND tbl_contributions.periodid = $periodid
";
$totalsum = mysqli_query($cov, $query_totalsum);
if (!$totalsum) {
    die(mysqli_error($cov));
}
$row_totalsum = mysqli_fetch_assoc($totalsum);
$totalRows_totalsum = mysqli_num_rows($totalsum);

// Query for comparing contributions
$query_compare = "
    SELECT
        tbl_contributions.membersid,
        tbl_contributions.contribution AS contribu,tbl_contributions.special_savings AS special_savings,
        CONCAT(IFNULL(tbl_personalinfo.Lname, ''), ' ', IFNULL(tbl_personalinfo.Fname, ''), ' ', IFNULL(tbl_personalinfo.Mname, '')) AS namee,
        tbpayrollperiods.PayrollPeriod,
        tbl_contributions.contriId,
        tbl_contributions.pay_method
    FROM tbl_contributions
    LEFT JOIN tbl_personalinfo ON tbl_personalinfo.memberid = tbl_contributions.membersid
    LEFT JOIN tbpayrollperiods ON tbpayrollperiods.Periodid = tbl_contributions.periodid
    WHERE `Status` = 'Active' AND tbl_contributions.periodid = $periodid
    ORDER BY tbl_contributions.contriId  DESC
";
$compare = mysqli_query($cov, $query_compare);
if (!$compare) {
    die(mysqli_error($cov));
}
$row_compare = mysqli_fetch_assoc($compare);
$totalRows_compare = mysqli_num_rows($compare);
?>

<?php if ($totalRows_compare > 0) { ?>
    <table width="80%" border="1" align="center" cellpadding="4" cellspacing="1">
        <thead>
        <tr valign="top" align="center">
            <th width="8%">S/N</th>
            <th width="25%">Membership ID</th>
            <th colspan="5">Name</th>
            <th width="17%">Loan Balance</th>
            <th width="17%">Deduction</th>
            <th width="17%">Special Savings</th>
            <th width="35%">Delete</th>
            <th width="35%">Period</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $i = 1;
        do {
            $query_loanBalance = sprintf("
                SELECT (SUM(tlb_mastertransaction.loanAmount) - SUM(tlb_mastertransaction.loanRepayment)) AS balance,
                    (SUM(tlb_mastertransaction.interestCal) - SUM(tlb_mastertransaction.interestPaid)) AS interestBalance
                FROM tlb_mastertransaction
                WHERE memberid = %s
            ", GetSQLValueString($cov, $row_compare['membersid'], "int"));
            $loanBalance = mysqli_query($cov, $query_loanBalance);
            if (!$loanBalance) {
                die(mysqli_error($cov));
            }
            $row_loanBalance = mysqli_fetch_assoc($loanBalance);
            ?>
            <tr valign="top" align="left">
                <td><?php echo $i; ?></td>
                <td height="35"><?php echo $row_compare['membersid']; ?></td>
                <td colspan="5"><?php echo $row_compare['namee']; ?></td>
                <td align="right"><?php echo number_format($row_loanBalance['balance'] ?? 0, 2); ?></td>
                <td align="right"><?php echo number_format($row_compare['contribu'] ?? 0, 2); ?></td>
                <td align="right"><?php echo number_format($row_compare['special_savings'] ?? 0, 2); ?></td>
                <td align="center"><?php if ($row_compare['pay_method'] == 0) { ?><a href="editContributions.php?deleteid=<?php echo $row_compare['contriId'] ?>">Delete</a><?php } ?></td>
                <td align="right"><?php echo $row_compare['PayrollPeriod']; ?></td>
            </tr>
            <?php
            $i++;
        } while ($row_compare = mysqli_fetch_assoc($compare));
        ?>
        <tr valign="top" text-alignn="left">
            <td>&nbsp;</td>
            <td height="35">&nbsp;</td>
            <td colspan="5"><strong>Total</strong></td>
            <td text-align="right">&nbsp;</td>
            <td text-align="right"><strong><?php echo number_format($row_totalsum['contribu'], 2); ?></strong></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        </tbody>
    </table>
<?php } else { ?>
    <p align="center"><strong><font color="#FF0000">No Matching Record !!!</font></strong></p>
<?php } ?>
