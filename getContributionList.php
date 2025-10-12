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
    <div class="bg-white rounded-lg shadow-lg overflow-hidden mt-4">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
            <h3 class="text-white text-lg font-bold flex items-center gap-2">
                <i class="fa fa-list-alt"></i>
                Contribution List
            </h3>
        </div>
        
        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr class="border-b border-gray-200">
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">S/N</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Member ID</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Name</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700">Loan Balance</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700">Deduction</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700">Special Savings</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-700">Period</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-700">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
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
            $rowClass = ($i % 2 == 0) ? 'bg-gray-50' : 'bg-white';
            ?>
            <tr class="<?php echo $rowClass; ?> hover:bg-blue-50 transition">
                <td class="px-4 py-3 text-gray-600"><?php echo $i; ?></td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        <?php echo $row_compare['membersid']; ?>
                    </span>
                </td>
                <td class="px-4 py-3 font-semibold text-gray-800"><?php echo htmlspecialchars($row_compare['namee']); ?></td>
                <td class="px-4 py-3 text-right text-gray-700">
                    <span class="<?php echo ($row_loanBalance['balance'] > 0) ? 'text-red-600 font-semibold' : 'text-gray-600'; ?>">
                        ₦<?php echo number_format($row_loanBalance['balance'] ?? 0, 2); ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-right font-semibold text-green-600">
                    ₦<?php echo number_format($row_compare['contribu'] ?? 0, 2); ?>
                </td>
                <td class="px-4 py-3 text-right">
                    <?php if ($row_compare['special_savings'] > 0): ?>
                        <span class="inline-flex items-center text-yellow-600 font-semibold">
                            <i class="fa fa-star mr-1"></i>
                            ₦<?php echo number_format($row_compare['special_savings'], 2); ?>
                        </span>
                    <?php else: ?>
                        <span class="text-gray-400">₦0.00</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-center text-gray-600">
                    <?php 
                    // Convert period to short month format (e.g., "Jan 2024")
                    $period = $row_compare['PayrollPeriod'];
                    $timestamp = strtotime($period);
                    if ($timestamp !== false) {
                        echo date('M Y', $timestamp);
                    } else {
                        echo htmlspecialchars($period);
                    }
                    ?>
                </td>
                <td class="px-4 py-3 text-center">
                    <?php if ($row_compare['pay_method'] == 0): ?>
                        <a href="editContributions.php?deleteid=<?php echo $row_compare['contriId']; ?>" 
                           class="inline-flex items-center px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded transition"
                           onclick="return confirm('Are you sure you want to delete this contribution?');">
                            <i class="fa fa-trash mr-1"></i>Delete
                        </a>
                    <?php else: ?>
                        <span class="inline-flex items-center px-3 py-1 bg-gray-200 text-gray-500 text-xs font-medium rounded">
                            <i class="fa fa-lock mr-1"></i>Online
                        </span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php
            $i++;
        } while ($row_compare = mysqli_fetch_assoc($compare));
        ?>
                </tbody>
                <tfoot>
                    <tr class="bg-gradient-to-r from-green-50 to-green-100 border-t-2 border-green-600">
                        <td colspan="4" class="px-4 py-4 text-right font-bold text-gray-800">
                            <span class="flex items-center justify-end gap-2">
                                <i class="fa fa-calculator text-green-600"></i>
                                Grand Total:
                            </span>
                        </td>
                        <td class="px-4 py-4 text-right">
                            <span class="text-xl font-bold text-green-700">
                                ₦<?php echo number_format($row_totalsum['contribu'], 2); ?>
                            </span>
                        </td>
                        <td colspan="3" class="px-4 py-4 text-center text-sm text-gray-600">
                            <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 rounded-full font-medium">
                                <i class="fa fa-users mr-1"></i>
                                <?php echo $totalRows_compare; ?> Members
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
<?php } else { ?>
    <div class="bg-white rounded-lg shadow-lg p-8 text-center">
        <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
            <i class="fa fa-inbox text-gray-400 text-2xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">No Contributions Found</h3>
        <p class="text-gray-500">There are no matching records for the selected period.</p>
    </div>
<?php } ?>
