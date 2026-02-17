<?php
require_once('../Connections/cov.php');
mysqli_select_db($cov, $database_cov);
$periodId = $_GET['periodId'] ?? '';
if (!$periodId) exit;

// Get Regular Contributions
$sqlRegular = "
    SELECT 
        'regular' as type,
        tbl_contributions.contriId as id,
        tbl_contributions.membersid,
        CONCAT(tbl_personalinfo.Fname, ' ', tbl_personalinfo.Lname) AS member_name,
        tbpayrollperiods.PayrollPeriod,
        tbl_contributions.contribution as regular_amount,
        tbl_contributions.special_savings as savings_amount,
        (tbl_contributions.contribution + tbl_contributions.special_savings) as total_amount
    FROM tbl_contributions
    INNER JOIN tbl_personalinfo ON tbl_contributions.membersid = tbl_personalinfo.memberid
    INNER JOIN tbpayrollperiods ON tbl_contributions.periodid = tbpayrollperiods.Periodid
    WHERE tbl_contributions.periodid = '".mysqli_real_escape_string($cov, $periodId)."'
";

// Get Special Repayments
$sqlSpecial = "
    SELECT 
        'special_repayment' as type,
        tbl_specialcontributions.id as id,
        tbl_specialcontributions.membersid,
        CONCAT(tbl_personalinfo.Fname, ' ', tbl_personalinfo.Lname) AS member_name,
        tbpayrollperiods.PayrollPeriod,
        tbl_specialcontributions.contribution as regular_amount,
        0 as savings_amount,
        tbl_specialcontributions.contribution as total_amount
    FROM tbl_specialcontributions
    INNER JOIN tbl_personalinfo ON tbl_specialcontributions.membersid = tbl_personalinfo.memberid
    INNER JOIN tbpayrollperiods ON tbl_specialcontributions.periodid = tbpayrollperiods.Periodid
    WHERE tbl_specialcontributions.periodid = '".mysqli_real_escape_string($cov, $periodId)."'
";

// Combine queries
$sql = "($sqlRegular) UNION ALL ($sqlSpecial) ORDER BY id DESC";
$res = $cov->query($sql);

echo '<div class="bg-white rounded-lg shadow-lg overflow-hidden mt-4">
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
        <h3 class="text-white text-lg font-bold flex items-center gap-2">
            <i class="fa fa-list-alt"></i>
            Contributions & Repayments List
        </h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr class="border-b border-gray-200">
                    <th class="px-4 py-3 text-center">
                        <input type="checkbox" id="selectAllContributions" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                    </th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Type</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Member Name</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-700">Amount</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-700">Savings/Details</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-700">Total</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">';

$grand_total = 0;
$row_count = 0;

while($row = $res->fetch_assoc()) {
    $row_count++;
    $type = $row['type'];
    $isSpecialRepayment = ($type === 'special_repayment');
    
    $amount = floatval($row['regular_amount']);
    $savings = floatval($row['savings_amount']);
    $total = floatval($row['total_amount']);
    
    $grand_total += $total;
    
    $rowClass = $row_count % 2 === 0 ? 'bg-gray-50 hover:bg-gray-100' : 'bg-white hover:bg-gray-50';
    $typeBadge = $isSpecialRepayment 
        ? '<span class="bg-purple-100 text-purple-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded">Special Repayment</span>' 
        : '<span class="bg-blue-100 text-blue-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded">Regular Contrib.</span>';
    
    echo "<tr class='{$rowClass} transition-colors duration-150'>";
    
    // Checkbox
    echo "<td class='px-4 py-3 text-center'>
            <input type='checkbox' class='contribution-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500' 
            value='{$row['id']}' data-type='{$type}'>
          </td>";
    
    echo "<td class='px-4 py-3'>{$typeBadge}</td>";
    echo "<td class='px-4 py-3 font-semibold text-gray-800'>{$row['member_name']}</td>";
    echo "<td class='px-4 py-3 text-right font-medium text-gray-700'>₦" . number_format($amount, 2) . "</td>";
    
    if ($isSpecialRepayment) {
        echo "<td class='px-4 py-3 text-right text-gray-400'>-</td>";
    } else {
         echo "<td class='px-4 py-3 text-right text-yellow-600'>₦" . number_format($savings, 2) . "</td>";
    }
    
    echo "<td class='px-4 py-3 text-right font-bold text-green-600'>₦" . number_format($total, 2) . "</td>";
    
    // Actions - Special Repayments are append-only so no edit/delete for now (as per analysis)
    // Actually, we can delete them if needed, but let's hide actions for them to simplify
    echo '<td class="px-4 py-3 text-center">';
    if (!$isSpecialRepayment) {
         echo '<div class="flex gap-2 justify-center">
            <button class="btn-edit bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-lg text-xs font-semibold transition-colors duration-150 flex items-center gap-1"
                data-id="'.$row['id'].'"
                data-memberid="'.$row['membersid'].'"
                data-periodid="'.$periodId.'"
                data-amount="'.$amount.'"
                data-member_name="'.$row['member_name'].'"
                data-specialsavings="'.$savings.'"
                data-type="regular">
                <i class="fa fa-edit"></i> Edit
            </button>
            <button class="btn-delete bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg text-xs font-semibold transition-colors duration-150 flex items-center gap-1"
                data-id="'.$row['id'].'" data-type="regular">
                <i class="fa fa-trash"></i> Delete
            </button>
        </div>';
    } else {
        echo '<div class="flex gap-2 justify-center">
            <button class="btn-edit bg-purple-500 hover:bg-purple-600 text-white px-3 py-1 rounded-lg text-xs font-semibold transition-colors duration-150 flex items-center gap-1"
                data-id="'.$row['id'].'"
                data-memberid="'.$row['membersid'].'"
                data-periodid="'.$periodId.'"
                data-amount="'.$total.'"
                data-member_name="'.$row['member_name'].'"
                data-type="special_repayment">
                <i class="fa fa-edit"></i> Edit
            </button>
            <button class="btn-delete bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg text-xs font-semibold transition-colors duration-150 flex items-center gap-1"
                data-id="'.$row['id'].'" data-type="special_repayment">
                <i class="fa fa-trash"></i> Delete
            </button>
        </div>';
    }
    echo '</td></tr>';
}

echo "<tr class='bg-gradient-to-r from-green-50 to-green-100 border-t-2 border-green-200'>
    <td colspan='5' class='px-4 py-4 text-right font-bold text-gray-700'>
        <i class='fa fa-calculator mr-2'></i>
        Grand Total
    </td>
    <td class='px-4 py-4 text-right font-bold text-green-600 text-lg'>₦" . number_format($grand_total, 2) . "</td>
    <td class='px-4 py-4'></td>
</tr>";

echo '</tbody></table></div></div>';

if ($row_count === 0) {
    echo '<div class="bg-white rounded-lg shadow-lg mt-4 p-8 text-center">
        <div class="text-gray-400 mb-4"><i class="fa fa-inbox text-6xl"></i></div>
        <h3 class="text-lg font-semibold text-gray-600 mb-2">No Records Found</h3>
        <p class="text-gray-500">No contributions or repayments found for this period.</p>
    </div>';
}