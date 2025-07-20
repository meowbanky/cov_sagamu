<?php
require_once('Connections/cov.php');
mysqli_select_db($cov, $database_cov);
header('Content-Type: text/html; charset=utf-8');
$periodid = intval($_POST['periodid'] ?? 0);
if (!$periodid) exit('No period selected.');

$sql = "SELECT l.loanid, l.memberid, l.loanamount, l.loan_date,
           CONCAT(p.Lname, ' ', p.Fname, ' ', IFNULL(p.Mname,'')) AS member_name
        FROM tbl_loan l
        JOIN tbl_personalinfo p ON l.memberid = p.memberid
        WHERE l.periodid = $periodid
        ORDER BY l.loanid DESC";
$res = $cov->query($sql);

if ($res && $res->num_rows) {
    $grand_total = 0;
    echo '<table class="min-w-full bg-white rounded shadow">
        <thead>
        <tr>
            <th class="px-4 py-2 text-left bg-gray-100">Member No</th>
            <th class="px-4 py-2 text-left bg-gray-100">Name</th>
            <th class="px-4 py-2 text-left bg-gray-100">Amount</th>
            <th class="px-4 py-2 text-left bg-gray-100">Date</th>
            <th class="px-4 py-2 text-left bg-gray-100">Action</th>
        </tr>
        </thead><tbody>';
    while ($row = $res->fetch_assoc()) {
        $grand_total += $row['loanamount'];
        echo '<tr class="border-b hover:bg-gray-50">
            <td data-label="Member No" class="px-4 py-2">'.htmlspecialchars($row['memberid']).'</td>
            <td data-label="Name" class="px-4 py-2">'.htmlspecialchars($row['member_name']).'</td>
            <td data-label="Amount" class="px-4 py-2">'.number_format($row['loanamount'],2).'</td>
            <td data-label="Date" class="px-4 py-2">'.htmlspecialchars($row['loan_date']).'</td>
            <td data-label="Action" class="px-4 py-2">
                <button type="button" class="edit-loan-btn bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded mr-2 shadow-sm transition" data-loanid="'.$row['loanid'].'">
                    <i class="fa fa-edit mr-1"></i>Edit
                </button>
                <button type="button" class="delete-loan-btn bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded shadow-sm transition" data-loanid="'.$row['loanid'].'">
                    <i class="fa fa-trash mr-1"></i>Delete
                </button>
            </td>
        </tr>';
    }
    // Add grand total row
    echo '<tr class="font-bold bg-gray-200">
        <td colspan="2" class="px-4 py-2 text-right">Grand Total</td>
        <td class="px-4 py-2">'.number_format($grand_total,2).'</td>
        <td class="px-4 py-2"></td>
        <td class="px-4 py-2"></td>
    </tr>';
    echo '</tbody></table>';
} else {
    echo '<div class="text-gray-500 p-4">No loans for this period.</div>';
}
?>