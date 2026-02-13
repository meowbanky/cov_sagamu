<?php
session_start();
if (!isset($_SESSION['UserID'])) {
    header("Location: index.php");
    exit;
}

require_once('Connections/cov.php');
require_once('libs/reports/IncomeExpenditureStatement.php');
require_once('libs/reports/BalanceSheet.php');
require_once('libs/reports/CashflowStatement.php');
require_once('libs/reports/ExecutiveSummary.php');
require_once('header.php');

// Get all periods
$periods = [];
$periodQuery = "SELECT Periodid, PayrollPeriod FROM tbpayrollperiods ORDER BY Periodid DESC";
$periodResult = mysqli_query($cov, $periodQuery);
if ($periodResult) {
    while ($row = mysqli_fetch_assoc($periodResult)) {
        $periods[] = $row;
    }
}

// Get selected periods (up to 5)
$selectedPeriods = [];
for ($i = 1; $i <= 5; $i++) {
    if (isset($_GET["period{$i}"]) && $_GET["period{$i}"] > 0) {
        $selectedPeriods[] = intval($_GET["period{$i}"]);
    }
}

// Default to last 3 periods if none selected
if (empty($selectedPeriods) && count($periods) >= 3) {
    $selectedPeriods = array_slice(array_column($periods, 'Periodid'), 0, 3);
}

$statementType = isset($_GET['statement']) ? $_GET['statement'] : 'executive'; // Changed default to executive for visibility

// Generate comparative reports
$comparativeData = null;
if (!empty($selectedPeriods)) {
    $incomeGenerator = new IncomeExpenditureStatement($cov, $database_cov);
    $balanceGenerator = new BalanceSheet($cov, $database_cov);
    $cashflowGenerator = new CashflowStatement($cov, $database_cov);
    $executiveGenerator = new ExecutiveSummary($cov, $database_cov);
    
    if ($statementType == 'income') {
        $comparativeData = $incomeGenerator->generateStatement($selectedPeriods[0], array_slice($selectedPeriods, 1));
    } elseif ($statementType == 'balance') {
        $comparativeData = $balanceGenerator->generateStatement($selectedPeriods[0], array_slice($selectedPeriods, 1));
    } elseif ($statementType == 'cashflow') {
        $comparativeData = $cashflowGenerator->generateStatement($selectedPeriods[0], array_slice($selectedPeriods, 1));
    } elseif ($statementType == 'executive') {
        $comparativeData = $executiveGenerator->generateStatement($selectedPeriods[0], array_slice($selectedPeriods, 1));
    }
}

// Get all periods
$periods = [];
$years = [];
$yearPeriodMap = []; // Maps Year => Last Period ID

$periodQuery = "SELECT Periodid, PayrollPeriod, PhysicalYear FROM tbpayrollperiods ORDER BY Periodid DESC";
$periodResult = mysqli_query($cov, $periodQuery);
if ($periodResult) {
    while ($row = mysqli_fetch_assoc($periodResult)) {
        $periods[] = $row;
        
        // Track unique years
        $y = $row['PhysicalYear'];
        if ($y && !in_array($y, $years)) {
            $years[] = $y;
        }
        
        // Track last period (descending order helps, first one seen is the latest)
        if ($y && !isset($yearPeriodMap[$y])) {
            $yearPeriodMap[$y] = $row['Periodid'];
        }
    }
}
sort($years); // Ascending years for dropdown

// Report By Type: 'period' or 'year'
$reportBy = isset($_GET['report_by']) ? $_GET['report_by'] : 'year'; // Default to year per request

// Get selected items (Periods or Years)
$selectedIds = [];
$selectedYears = [];

for ($i = 1; $i <= 5; $i++) {
    if ($reportBy == 'year') {
        if (isset($_GET["year{$i}"]) && !empty($_GET["year{$i}"])) {
            $y = $_GET["year{$i}"];
            $selectedYears[] = $y;
            // Resolve to period ID
            if (isset($yearPeriodMap[$y])) {
                $selectedIds[] = $yearPeriodMap[$y];
            }
        }
    } else {
        if (isset($_GET["period{$i}"]) && $_GET["period{$i}"] > 0) {
            $selectedIds[] = intval($_GET["period{$i}"]);
        }
    }
}

// Default logic if nothing selected
if (empty($selectedIds)) {
    if ($reportBy == 'year' && count($years) >= 2) {
        // Default to last 2 years
        $last2 = array_slice($years, -2);
        foreach ($last2 as $y) {
            $selectedYears[] = $y;
            if (isset($yearPeriodMap[$y])) $selectedIds[] = $yearPeriodMap[$y];
        }
    } elseif ($reportBy == 'period' && count($periods) >= 2) {
        $selectedIds = array_slice(array_column($periods, 'Periodid'), 0, 2);
    }
}

$statementType = isset($_GET['statement']) ? $_GET['statement'] : 'executive';

// Generate comparative reports
$comparativeData = null;
if (!empty($selectedIds)) {
    $incomeGenerator = new IncomeExpenditureStatement($cov, $database_cov);
    $balanceGenerator = new BalanceSheet($cov, $database_cov);
    $cashflowGenerator = new CashflowStatement($cov, $database_cov);
    $executiveGenerator = new ExecutiveSummary($cov, $database_cov);
    
    if ($statementType == 'income') {
        $comparativeData = $incomeGenerator->generateStatement($selectedIds[0], array_slice($selectedIds, 1));
    } elseif ($statementType == 'balance') {
        $comparativeData = $balanceGenerator->generateStatement($selectedIds[0], array_slice($selectedIds, 1));
    } elseif ($statementType == 'cashflow') {
        $comparativeData = $cashflowGenerator->generateStatement($selectedIds[0], array_slice($selectedIds, 1));
    } elseif ($statementType == 'executive') {
        $comparativeData = $executiveGenerator->generateStatement($selectedIds[0], array_slice($selectedIds, 1), $reportBy);
    }
}

// Get column headers (Period Name or Year Name)
$columnHeaders = [];
if ($reportBy == 'year') {
    // For year view, map back from Period ID to Year
    // Actually, we should just use the Year from selection if possible, 
    // but the results are keyed by PeriodID.
    // Let's create a map: PeriodID -> Header
    foreach ($yearPeriodMap as $y => $pid) {
        $columnHeaders[$pid] = "FY " . $y;
    }
} else {
    foreach ($selectedIds as $pid) {
        foreach ($periods as $p) {
            if ($p['Periodid'] == $pid) {
                $columnHeaders[$pid] = $p['PayrollPeriod'];
                break;
            }
        }
    }
}
?>

<div class="container mx-auto px-4 py-8 max-w-7xl">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-blue-900">📊 Comparative Financial Reports</h1>
                <p class="text-gray-600 mt-1">Multi-period comparison and trend analysis</p>
            </div>
            <div class="flex gap-2">
                <button onclick="window.print()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                    <i class="fa fa-print mr-1"></i> Print
                </button>
                <button onclick="exportToExcel()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                    <i class="fa fa-file-excel mr-1"></i> CSV
                </button>
                <button onclick="exportToPDF()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                    <i class="fa fa-file-pdf mr-1"></i> PDF
                </button>
            </div>
        </div>
    </div>

    <!-- ... rest of file ... -->

<script>
function exportToExcel() {
    const table = document.querySelector('table');
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let row of rows) {
        let cols = [];
        const cells = row.querySelectorAll('td, th');
        for (let cell of cells) {
            let text = cell.innerText.replace(/"/g, '""').replace(/\n/g, ' '); // Clean newlines
            cols.push('"' + text.trim() + '"');
        }
        csv.push(cols.join(','));
    }
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'Comparative_Report_' + new Date().getTime() + '.csv';
    link.click();
}

function exportToPDF() {
    const table = document.querySelector('.dashboard-table') || document.querySelector('table');
    if (!table) {
        alert("No report table found to export.");
        return;
    }

    // Get table HTML
    let tableHtml = table.outerHTML;
    
    // Create a form to submit to the PDF generator
    let form = document.createElement("form");
    form.method = "POST";
    form.action = "export_pdf_formatted.php"; 
    form.target = "_blank"; // Open in new tab

    // HTML Content Input
    let inputHtml = document.createElement("input");
    inputHtml.type = "hidden";
    inputHtml.name = "html";
    inputHtml.value = tableHtml;
    form.appendChild(inputHtml);

    // Filename Input
    let inputFilename = document.createElement("input");
    inputFilename.type = "hidden";
    inputFilename.name = "filename";
    inputFilename.value = "Comparative_Report_" + new Date().toISOString().slice(0, 10);
    form.appendChild(inputFilename);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>

    <!-- Period Selection -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" id="reportForm">
            <div class="grid grid-cols-1 gap-6 mb-4">
                
                <div class="flex gap-6 items-center border-b pb-4">
                     <!-- Report By Toggle -->
                     <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Report By</label>
                        <div class="flex rounded-md shadow-sm" role="group">
                            <label class="cursor-pointer <?php echo $reportBy == 'year' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'; ?> px-4 py-2 text-sm font-medium border border-gray-200 rounded-l-lg transition-colors">
                                <input type="radio" name="report_by" value="year" class="hidden" onchange="this.form.submit()" <?php echo $reportBy == 'year' ? 'checked' : ''; ?>>
                                Annual (Yearly)
                            </label>
                            <label class="cursor-pointer <?php echo $reportBy == 'period' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'; ?> px-4 py-2 text-sm font-medium border border-gray-200 border-l-0 rounded-r-lg transition-colors">
                                <input type="radio" name="report_by" value="period" class="hidden" onchange="this.form.submit()" <?php echo $reportBy == 'period' ? 'checked' : ''; ?>>
                                Monthly (Period)
                            </label>
                        </div>
                    </div>

                    <!-- Statement Type -->
                    <div class="flex-grow">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Statement Type</label>
                        <select name="statement" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                            <option value="executive" <?php echo ($statementType == 'executive') ? 'selected' : ''; ?>>Executive Summary (Membership & Financials)</option>
                            <option value="income" <?php echo ($statementType == 'income') ? 'selected' : ''; ?>>Income & Expenditure</option>
                            <option value="balance" <?php echo ($statementType == 'balance') ? 'selected' : ''; ?>>Balance Sheet</option>
                            <option value="cashflow" <?php echo ($statementType == 'cashflow') ? 'selected' : ''; ?>>Cashflow Statement</option>
                        </select>
                    </div>
                </div>
                
                <!-- Dynamic Selectors (Year or Period) -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">
                                <?php echo $reportBy == 'year' ? "Year $i" : "Period $i"; ?>
                            </label>
                            
                            <?php if ($reportBy == 'year'): ?>
                                <select name="year<?php echo $i; ?>" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                                    <option value="">None</option>
                                    <?php foreach ($years as $year): ?>
                                        <option value="<?php echo $year; ?>" 
                                                <?php echo (isset($selectedYears[$i-1]) && $selectedYears[$i-1] == $year) ? 'selected' : ''; ?>>
                                            <?php echo $year; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <select name="period<?php echo $i; ?>" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                                    <option value="">None</option>
                                    <?php foreach ($periods as $period): ?>
                                        <option value="<?php echo $period['Periodid']; ?>" 
                                                <?php echo (isset($selectedIds[$i-1]) && $selectedIds[$i-1] == $period['Periodid']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($period['PayrollPeriod']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg shadow transition hover:-translate-y-0.5">
                    <i class="fa fa-sync-alt mr-2"></i> Update Report
                </button>
            </div>
        </form>
    </div>

    <!-- Comparative Report -->
    <?php if ($comparativeData && $comparativeData['success']): ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden animate-fade-in-up">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-700 to-indigo-800 text-white flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold">
                        <?php 
                        if ($statementType == 'executive') echo 'Executive Summary';
                        elseif ($statementType == 'income') echo 'Income & Expenditure';
                        elseif ($statementType == 'balance') echo 'Balance Sheet';
                        elseif ($statementType == 'cashflow') echo 'Cashflow Statement';
                        ?>
                    </h2>
                    <p class="text-sm text-blue-200 opacity-90">
                        <?php echo $reportBy == 'year' ? 'Annual Comparison' : 'Period Comparison'; ?>
                    </p>
                </div>
                <div class="text-xs bg-white bg-opacity-20 px-3 py-1 rounded-full">
                    <?php echo count($selectedIds); ?> data points
                </div>
            </div>

            <div class="overflow-x-auto">
                <?php if ($statementType == 'executive'): ?>
                    <!-- EXECUTIVE SUMMARY COMPARISON -->
                    <table class="w-full text-sm dashboard-table">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Metric</th>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <th class="px-6 py-3 text-right font-bold text-gray-800 text-base border-l">
                                        <?php echo isset($columnHeaders[$pid]) ? $columnHeaders[$pid] : "Period $pid"; ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <!-- MEMBERSHIP -->
                            <tr class="bg-indigo-50">
                                <td colspan="<?php echo count($selectedIds) + 1; ?>" class="px-4 py-2 font-bold text-indigo-900 border-l-4 border-indigo-500">MEMBERSHIP STATISTICS</td>
                            </tr>
<tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 pl-8 font-medium text-gray-700">New Members Registered (During Period)</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <td class="px-6 py-4 text-right font-mono text-lg font-bold text-indigo-600 border-l">
                                        <?php echo number_format($comparativeData['statement'][$pid]['membership']['new_members'] ?? 0); ?>
                                        <div class="text-xs font-normal text-gray-400 mt-1">new members</div>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                            <tr class="bg-gray-50 text-xs text-gray-400">
                                <td class="px-6 py-2 pl-8 font-medium">Total Membership Size (Cumulative)</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <td class="px-6 py-2 text-right font-mono text-gray-500 border-l">
                                        <?php echo number_format($comparativeData['statement'][$pid]['membership']['total_members'] ?? 0); ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                            
                            <!-- FINANCIALS (Cumulative - Balance Sheet) -->
                            <tr class="bg-green-50">
                                <td colspan="<?php echo count($selectedIds) + 1; ?>" class="px-4 py-2 font-bold text-green-900 border-l-4 border-green-500">FINANCIAL ASSETS</td>
                            </tr>
                            
                            <!-- Activity During Period -->
                            <tr class="bg-gray-50 text-xs text-gray-500 font-semibold uppercase tracking-wide">
                                <td colspan="<?php echo count($selectedIds) + 1; ?>" class="px-4 py-1 border-l-4 border-gray-300">New Contributions (During Period/Year)</td>
                            </tr>
                            <tr class="hover:bg-gray-50 transition-colors text-sm">
                                <td class="px-6 py-2 pl-8 font-medium text-gray-700">Shares Contributed</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <td class="px-6 py-2 text-right font-mono text-gray-800 border-l">₦<?php echo number_format($comparativeData['statement'][$pid]['financials']['period_shares'], 2); ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <tr class="hover:bg-gray-50 transition-colors text-sm border-b">
                                <td class="px-6 py-2 pl-8 font-medium text-gray-700">Savings Contributed</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <td class="px-6 py-2 text-right font-mono text-gray-800 border-l">₦<?php echo number_format($comparativeData['statement'][$pid]['financials']['period_savings'], 2); ?></td>
                                <?php endforeach; ?>
                            </tr>

                            <!-- Cumulative Balances -->
                             <tr class="bg-gray-50 text-xs text-gray-500 font-semibold uppercase tracking-wide mt-4">
                                <td colspan="<?php echo count($selectedIds) + 1; ?>" class="px-4 py-1 border-l-4 border-gray-300">Total Balances (Cumulative)</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 pl-8 font-medium text-gray-700">Total Shares</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <td class="px-6 py-3 text-right font-mono text-gray-800 border-l">₦<?php echo number_format($comparativeData['statement'][$pid]['financials']['total_shares'], 2); ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 pl-8 font-medium text-gray-700">Total Savings</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <td class="px-6 py-3 text-right font-mono text-gray-800 border-l">₦<?php echo number_format($comparativeData['statement'][$pid]['financials']['total_savings'], 2); ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <tr class="bg-gray-50 font-semibold text-gray-800">
                                <td class="px-6 py-3 pl-8">Total Members Funds</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <?php $sum = $comparativeData['statement'][$pid]['financials']['total_shares'] + $comparativeData['statement'][$pid]['financials']['total_savings']; ?>
                                    <td class="px-6 py-3 text-right font-mono border-l">₦<?php echo number_format($sum, 2); ?></td>
                                <?php endforeach; ?>
                            </tr>

                            <tr class="bg-orange-50 mt-4">
                                <td colspan="<?php echo count($selectedIds) + 1; ?>" class="px-4 py-2 font-bold text-orange-900 border-l-4 border-orange-500">LOAN PORTFOLIO</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 pl-8 font-medium text-gray-700">Net Loan Portfolio</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <td class="px-6 py-3 text-right font-mono font-semibold text-gray-800 border-l">₦<?php echo number_format($comparativeData['statement'][$pid]['financials']['loan_portfolio'], 2); ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <!-- LOAN DETAILS (Flows during Report Window) -->
                            <tr class="bg-gray-50 text-xs text-gray-500 font-semibold uppercase tracking-wide">
                                <td colspan="<?php echo count($selectedIds) + 1; ?>" class="px-4 py-2 border-l-4 border-gray-300">Activity During Period/Year</td>
                            </tr>
                            <tr class="text-xs text-gray-700 bg-gray-50/50">
                                <td class="px-6 py-2 pl-12">Loans Issued (During Period)</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <td class="px-6 py-2 text-right font-mono border-l">₦<?php echo number_format($comparativeData['statement'][$pid]['financials']['period_loan_issued'], 2); ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <tr class="text-xs text-gray-700 bg-gray-50/50">
                                <td class="px-6 py-2 pl-12">Repayments (During Period)</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <td class="px-6 py-2 text-right font-mono border-l">₦<?php echo number_format($comparativeData['statement'][$pid]['financials']['period_loan_repaid'], 2); ?></td>
                                <?php endforeach; ?>
                            </tr>

                            <!-- LOAN DETAILS (Cumulative) -->
                            <tr class="text-xs text-gray-400 mt-2">
                                <td class="px-6 py-1 pl-12 italic">Total Loans Issued (Cumulative)</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <td class="px-6 py-1 text-right font-mono italic border-l">₦<?php echo number_format($comparativeData['statement'][$pid]['financials']['cumulative_loan_issued'], 2); ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <tr class="text-xs text-gray-400 border-b">
                                <td class="px-6 py-1 pl-12 italic">Total Repayments (Cumulative)</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <td class="px-6 py-1 text-right font-mono italic border-l">₦<?php echo number_format($comparativeData['statement'][$pid]['financials']['cumulative_loan_repaid'], 2); ?></td>
                                <?php endforeach; ?>
                            </tr>

                        </tbody>
                    </table>

                <?php elseif ($statementType == 'income'): ?>
                    <!-- INCOME STATEMENT COMPARISON -->
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-gray-600">Item</th>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <th class="px-6 py-3 text-right font-semibold text-gray-800 border-l">
                                        <?php echo isset($columnHeaders[$pid]) ? $columnHeaders[$pid] : $periodNames[$pid]; ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <!-- REVENUE -->
                            <tr class="bg-purple-50">
                                <td colspan="<?php echo count($selectedIds) + 1; ?>" class="px-4 py-2 font-bold text-gray-900">REVENUE</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 pl-8">Entrance Fee</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <td class="px-4 py-2 text-right font-mono border-l">₦<?php echo number_format($comparativeData['statement'][$pid]['revenue']['entrance_fee'], 2); ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 pl-8">Interest Charges</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <td class="px-4 py-2 text-right font-mono border-l">₦<?php echo number_format($comparativeData['statement'][$pid]['revenue']['interest_charges'], 2); ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 pl-8">Other Income</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <td class="px-4 py-2 text-right font-mono border-l">₦<?php echo number_format($comparativeData['statement'][$pid]['revenue']['other_income'], 2); ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <tr class="bg-purple-100 font-bold">
                                <td class="px-4 py-2">TOTAL REVENUE</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <td class="px-4 py-2 text-right font-mono border-l">₦<?php echo number_format($comparativeData['statement'][$pid]['revenue']['total_revenue'], 2); ?></td>
                                <?php endforeach; ?>
                            </tr>
                            
                            <!-- EXPENSES -->
                            <tr class="bg-orange-50">
                                <td colspan="<?php echo count($selectedIds) + 1; ?>" class="px-4 py-2 font-bold text-gray-900">EXPENSES</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 pl-8">Cost of Sales</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <td class="px-4 py-2 text-right font-mono border-l">₦<?php echo number_format($comparativeData['statement'][$pid]['cost_of_sales'], 2); ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 pl-8">Total Overhead</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <td class="px-4 py-2 text-right font-mono border-l">₦<?php echo number_format($comparativeData['statement'][$pid]['overhead']['total_expenses'], 2); ?></td>
                                <?php endforeach; ?>
                            </tr>
                            
                            <!-- SURPLUS -->
                            <tr class="bg-green-100 font-bold text-green-900 text-lg">
                                <td class="px-4 py-3">SURPLUS (DEFICIT)</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <td class="px-4 py-3 text-right font-mono border-l">₦<?php echo number_format($comparativeData['statement'][$pid]['surplus'], 2); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        </tbody>
                    </table>

                <?php elseif ($statementType == 'balance'): ?>
                    <!-- BALANCE SHEET COMPARISON -->
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-gray-600">Item</th>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <th class="px-6 py-3 text-right font-semibold text-gray-800 border-l">
                                        <?php echo isset($columnHeaders[$pid]) ? $columnHeaders[$pid] : $periodNames[$pid]; ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr class="bg-blue-50">
                                <td colspan="<?php echo count($selectedIds) + 1; ?>" class="px-4 py-2 font-bold">ASSETS</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 pl-8">Non-Current Assets</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <td class="px-4 py-2 text-right font-mono border-l">₦<?php echo number_format($comparativeData['statement'][$pid]['total_non_current_assets'], 2); ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 pl-8">Current Assets</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <td class="px-4 py-2 text-right font-mono border-l">₦<?php echo number_format($comparativeData['statement'][$pid]['total_current_assets'], 2); ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <tr class="bg-blue-100 font-bold">
                                <td class="px-4 py-2">TOTAL ASSETS</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <td class="px-4 py-2 text-right font-mono border-l">₦<?php echo number_format($comparativeData['statement'][$pid]['total_current_assets'] + $comparativeData['statement'][$pid]['total_non_current_assets'], 2); ?></td>
                                <?php endforeach; ?>
                            </tr>
                            
                            <tr class="bg-green-50">
                                <td colspan="<?php echo count($selectedIds) + 1; ?>" class="px-4 py-2 font-bold">EQUITY</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 pl-8">Members Fund</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <td class="px-4 py-2 text-right font-mono border-l">₦<?php echo number_format($comparativeData['statement'][$pid]['total_members_fund'], 2); ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 pl-8">Reserves</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <td class="px-4 py-2 text-right font-mono border-l">₦<?php echo number_format($comparativeData['statement'][$pid]['total_reserves'], 2); ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 pl-8">Retained Earnings</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <td class="px-4 py-2 text-right font-mono border-l">₦<?php echo number_format($comparativeData['statement'][$pid]['retained_earnings'], 2); ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <tr class="bg-green-100 font-bold">
                                <td class="px-4 py-2">TOTAL EQUITY</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <td class="px-4 py-2 text-right font-mono border-l">₦<?php echo number_format($comparativeData['statement'][$pid]['total_equity'], 2); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        </tbody>
                    </table>

                <?php elseif ($statementType == 'cashflow'): ?>
                    <!-- CASHFLOW COMPARISON -->
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-gray-600">Activity</th>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <th class="px-6 py-3 text-right font-semibold text-gray-800 border-l">
                                        <?php echo isset($columnHeaders[$pid]) ? $columnHeaders[$pid] : $periodNames[$pid]; ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr>
                                <td class="px-4 py-2 font-semibold">Operating Activities</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <td class="px-4 py-2 text-right font-mono border-l">₦<?php echo number_format($comparativeData['statement'][$pid]['operating']['net_cashflow_operating'], 2); ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-semibold">Investing Activities</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <td class="px-4 py-2 text-right font-mono border-l">₦<?php echo number_format($comparativeData['statement'][$pid]['investing']['net_cashflow_investing'], 2); ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-semibold">Financing Activities</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <td class="px-4 py-2 text-right font-mono border-l">₦<?php echo number_format($comparativeData['statement'][$pid]['financing']['net_cashflow_financing'], 2); ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <tr class="bg-teal-100 font-bold">
                                <td class="px-4 py-3">NET CASHFLOW</td>
                                <?php foreach ($selectedIds as $pid): ?>
                                    <td class="px-4 py-3 text-right font-mono border-l">₦<?php echo number_format($comparativeData['statement'][$pid]['net_cashflow'], 2); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

    <?php else: ?>
        <div class="bg-blue-50 border-l-4 border-blue-400 p-6 rounded-lg animate-pulse">
            <div class="flex items-center">
                <i class="fa fa-info-circle text-blue-500 mr-3 text-xl"></i>
                <p class="text-blue-800">Select periods or years above and click "Update Report" to view comparatives.</p>
            </div>
        </div>
    <?php endif; ?>
</div>



<?php require_once('footer.php'); ?>
