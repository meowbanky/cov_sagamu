<?php
require_once('../Connections/cov.php');
mysqli_select_db($cov, $database_cov);
$periodId = $_GET['periodId'] ?? '';
if (!$periodId) exit;

// Only get: contriId, Name (Fname Lname), Period name, and amount (contribution)
$sql = "
SELECT tbl_contributions.membersid,
    tbl_contributions.contriId,
    CONCAT(tbl_personalinfo.Fname, ' ', tbl_personalinfo.Lname) AS member_name,
    tbpayrollperiods.PayrollPeriod,
    tbl_contributions.contribution
FROM
    tbl_contributions
    INNER JOIN tbl_personalinfo
        ON tbl_contributions.membersid = tbl_personalinfo.memberid
    INNER JOIN tbpayrollperiods
        ON tbl_contributions.periodid = tbpayrollperiods.Periodid
WHERE
    tbl_contributions.periodid = '".mysqli_real_escape_string($cov, $periodId)."'
ORDER BY
    contriId DESC
";
$res = $cov->query($sql);

echo '<table class="w-full border mt-2 text-sm">
<thead>
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Period</th>
    <th class="text-right">Amount</th>
    <th></th>
</tr>
</thead>
<tbody>';

global $grand_total;
$grand_total = 0;
while($row = $res->fetch_assoc()) {
    $amountFormatted = '₦' . number_format($row['contribution'], 2);
    $grand_total += $row['contribution'];
    echo "<tr 
        data-id='{$row['contriId']}'
        data-memberid='{$row['membersid']}'
        data-periodid='{$periodId}'
        data-amount='{$row['contribution']}'
        data-member_name='{$row['member_name']}'
        data-specialsavings='".($row['specialsavings'] ?? 0)."'
    >";
    echo "<td>{$row['contriId']}</td>";
    echo "<td>{$row['member_name']}</td>";
    echo "<td>{$row['PayrollPeriod']}</td>";
    echo "<td class='text-right'>{$amountFormatted}</td>";
    echo '<td>
        <button class="btn-edit bg-yellow-400 px-2 py-1 rounded mr-2">Edit</button>
        <button class="btn-delete bg-red-500 text-white px-2 py-1 rounded">Delete</button>
    </td></tr>';
}
// Add grand total row
$grand_total_formatted = '₦' . number_format($grand_total, 2);
echo "<tr class='font-bold bg-gray-200'>
    <td colspan='3' class='text-right'>Grand Total</td>
    <td class='text-right'>{$grand_total_formatted}</td>
    <td></td>
</tr>";
echo '</tbody></table>';