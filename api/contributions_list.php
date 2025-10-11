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
    tbl_contributions.contribution,
    tbl_contributions.special_savings
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

echo '<div class="bg-white rounded-lg shadow-lg overflow-hidden mt-4">
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
        <h3 class="text-white text-lg font-bold flex items-center gap-2">
            <i class="fa fa-list-alt"></i>
            Contributions List
        </h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr class="border-b border-gray-200">
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">ID</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Member Name</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Period</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-700">Regular Amount</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-700">Special Savings</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-700">Total</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">';

global $grand_total, $grand_regular, $grand_special;
$grand_total = 0;
$grand_regular = 0;
$grand_special = 0;
$row_count = 0;

while($row = $res->fetch_assoc()) {
    $row_count++;
    $regularAmount = $row['contribution'];
    $specialAmount = $row['special_savings'] ?? 0;
    $totalAmount = $regularAmount + $specialAmount;
    
    $amountFormatted = '₦' . number_format($regularAmount, 2);
    $specialSavingsFormatted = '₦' . number_format($specialAmount, 2);
    $totalFormatted = '₦' . number_format($totalAmount, 2);
    
    $grand_total += $totalAmount;
    $grand_regular += $regularAmount;
    $grand_special += $specialAmount;
    
    $rowClass = $row_count % 2 === 0 ? 'bg-gray-50 hover:bg-gray-100' : 'bg-white hover:bg-gray-50';
    $hasSpecialSavings = $specialAmount > 0;
    
    echo "<tr class='{$rowClass} transition-colors duration-150'
        data-id='{$row['contriId']}'
        data-memberid='{$row['membersid']}'
        data-periodid='{$periodId}'
        data-amount='{$regularAmount}'
        data-member_name='{$row['member_name']}'
        data-specialsavings='{$specialAmount}'
    >";
    
    echo "<td class='px-4 py-3 font-mono text-gray-600'>{$row['contriId']}</td>";
    echo "<td class='px-4 py-3 font-semibold text-gray-800'>{$row['member_name']}</td>";
    echo "<td class='px-4 py-3 text-gray-600'>{$row['PayrollPeriod']}</td>";
    echo "<td class='px-4 py-3 text-right font-semibold text-blue-600'>{$amountFormatted}</td>";
    
    if ($hasSpecialSavings) {
        echo "<td class='px-4 py-3 text-right font-semibold text-yellow-600 flex items-center justify-end gap-1'>
            <i class='fa fa-star text-xs'></i>
            {$specialSavingsFormatted}
        </td>";
    } else {
        echo "<td class='px-4 py-3 text-right text-gray-400'>-</td>";
    }
    
    echo "<td class='px-4 py-3 text-right font-bold text-green-600'>{$totalFormatted}</td>";
    
    echo '<td class="px-4 py-3 text-center">
        <div class="flex gap-2 justify-center">
            <button class="btn-edit bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-lg text-xs font-semibold transition-colors duration-150 flex items-center gap-1">
                <i class="fa fa-edit"></i>
                Edit
            </button>
            <button class="btn-delete bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg text-xs font-semibold transition-colors duration-150 flex items-center gap-1">
                <i class="fa fa-trash"></i>
                Delete
            </button>
        </div>
    </td></tr>';
}
// Add grand total row
$grand_regular_formatted = '₦' . number_format($grand_regular, 2);
$grand_special_formatted = '₦' . number_format($grand_special, 2);
$grand_total_formatted = '₦' . number_format($grand_total, 2);

echo "<tr class='bg-gradient-to-r from-green-50 to-green-100 border-t-2 border-green-200'>
    <td colspan='3' class='px-4 py-4 text-right font-bold text-gray-700'>
        <i class='fa fa-calculator mr-2'></i>
        Grand Total ({$row_count} contributions)
    </td>
    <td class='px-4 py-4 text-right font-bold text-blue-600'>{$grand_regular_formatted}</td>
    <td class='px-4 py-4 text-right font-bold text-yellow-600'>{$grand_special_formatted}</td>
    <td class='px-4 py-4 text-right font-bold text-green-600 text-lg'>{$grand_total_formatted}</td>
    <td class='px-4 py-4'></td>
</tr>";

echo '</tbody></table>
    </div>
</div>';

// Add empty state if no contributions
if ($row_count === 0) {
    echo '<div class="bg-white rounded-lg shadow-lg mt-4 p-8 text-center">
        <div class="text-gray-400 mb-4">
            <i class="fa fa-inbox text-6xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-600 mb-2">No Contributions Found</h3>
        <p class="text-gray-500">No contributions have been recorded for this period yet.</p>
        <p class="text-sm text-gray-400 mt-2">Add contributions using the form above.</p>
    </div>';
}