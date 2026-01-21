<?php
// Check authentication BEFORE including header
session_start();
// if (!isset($_SESSION['UserID'])) {
//     header("Location:index.php");
//     exit;
// }

// Now include header after authentication check
require_once('header.php');
require_once('Connections/cov.php');
?>
<div class="flex min-h-screen">
    <main class="flex-1 py-8 px-2 md:px-10 bg-gray-50">
        <div class="max-w-6xl mx-auto">
            <h1 class="text-xl sm:text-2xl font-bold text-blue-900 mb-4 sm:mb-6">Overdue Loans Report</h1>

            <!-- Refresh and Export Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 mb-4 items-center sm:items-end">
                <div class="w-full sm:w-auto">
                    <label for="overdueMonths" class="block text-sm font-medium text-gray-700 mb-1">Overdue Threshold (Months)</label>
                    <input type="number" id="overdueMonths" value="12" min="1" 
                        class="border border-gray-300 rounded px-3 py-2 w-full focus:ring-blue-500 focus:border-blue-500">
                </div>
                <button onclick="getOverdueLoans()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded w-full sm:w-auto h-[42px]">
                    <i class="fas fa-sync-alt mr-2"></i>Load Overdue Loans
                </button>
                <button id="exportExcelBtn" onclick="exportToExcel()" disabled
                    class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-4 py-2 rounded w-full sm:w-auto h-[42px]">
                    <i class="fas fa-file-excel mr-2"></i>Export to Excel
                </button>
            </div>

            <!-- Loader -->
            <div id="wait" style="display:none;" class="mb-2">
                <div class="flex items-center gap-2">
                    <img src="images/pageloading.gif" class="h-6 w-6"> <span>Please wait...</span>
                </div>
            </div>

            <!-- Table Results -->
            <div id="overdueDisplay" class="rounded shadow bg-white p-3 overflow-x-auto">
                <!-- Results will appear here -->
                <p class="text-gray-500 text-center py-8">Click "Load Overdue Loans" to view overdue loans</p>
            </div>
        </div>
    </main>
</div>
<?php require_once('footer.php'); ?>

<script>
function showBlockingLoader(msg = 'Loading, please wait...') {
    Swal.fire({
        title: '<div class="flex flex-col items-center gap-4"><svg class="animate-spin h-10 w-10 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg><span class="mt-2 text-blue-800 font-semibold">' +
            msg + '</span></div>',
        html: '',
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        showConfirmButton: false,
        backdrop: true,
        customClass: {
            popup: 'rounded-xl shadow-lg p-8'
        }
    });
}

function hideBlockingLoader() {
    Swal.close();
}

function getOverdueLoans() {
    const months = $('#overdueMonths').val() || 10;
    
    showBlockingLoader('Loading overdue loans...');
    $('#overdueDisplay').html('');
    $('#wait').show();

    $.get('api/get_overdue_loans.php', { months: months }, function(response) {
        hideBlockingLoader();
        $('#wait').hide();

        if (response.success) {
            displayOverdueLoans(response);
        } else {
            $('#overdueDisplay').html(`
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-red-800">Error: ${response.message || 'Failed to load overdue loans'}</p>
                </div>
            `);
        }
    }, 'json').fail(function(xhr) {
        hideBlockingLoader();
        $('#wait').hide();
        $('#overdueDisplay').html(`
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <p class="text-red-800">Error loading overdue loans. Please try again.</p>
            </div>
        `);
    });
}

function displayOverdueLoans(data) {
    if (!data.data || data.data.length === 0) {
        $('#overdueDisplay').html(`
            <div class="bg-green-50 border border-green-200 rounded-lg p-8 text-center">
                <div class="text-green-500 text-5xl mb-4">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Overdue Loans</h3>
                <p class="text-gray-600">All members are current with their loan repayments.</p>
            </div>
        `);
        // Disable export button when no data
        $('#exportExcelBtn').prop('disabled', true);
        window.overdueLoansData = null;
        return;
    }

    let html = `
        <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <div class="text-sm text-gray-600 mb-1">Current Period</div>
                <div class="text-xl font-bold text-blue-900">${escapeHtml(data.current_period_name ? data.current_period_name : (data.current_period ? 'Period ' + data.current_period : 'N/A'))}</div>
            </div>
            <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                <div class="text-sm text-gray-600 mb-1">Total Overdue</div>
                <div class="text-xl font-bold text-red-900">${data.total_overdue}</div>
                <div class="text-xs text-gray-500">member(s)</div>
            </div>
            <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                <div class="text-sm text-gray-600 mb-1">Overdue Criteria</div>
                <div class="text-xl font-bold text-yellow-900">> ${data.threshold} months</div>
                <div class="text-xs text-gray-500">since last loan</div>
            </div>
        </div>
        
        <!-- Mobile Card View (visible on small screens) -->
        <div class="block md:hidden space-y-4">
    `;

    let totalBalance = 0;
    data.data.forEach((loan, index) => {
        totalBalance += loan.loan_balance;
        html += `
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex-1">
                        <div class="text-lg font-semibold text-gray-900">${escapeHtml(loan.member_name)}</div>
                        <div class="text-sm text-gray-500 mt-1">Coop No: ${escapeHtml(loan.memberid)}</div>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 whitespace-nowrap ml-2">
                        ${loan.period_gap} months
                    </span>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Last Loan:</span>
                        <span class="font-medium text-gray-900">₦${formatNumber(loan.last_loan_amount)}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Last Loan Period:</span>
                        <span class="text-gray-900">${escapeHtml(loan.last_loan_period_name || 'N/A')}</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-gray-200">
                        <span class="font-semibold text-gray-900">Current Balance:</span>
                        <span class="font-bold text-red-600">₦${formatNumber(loan.loan_balance)}</span>
                    </div>
                </div>
            </div>
        `;
    });

    html += `
            <div class="bg-gray-50 border border-gray-300 rounded-lg p-4">
                <div class="flex justify-between items-center">
                    <span class="text-base font-bold text-gray-900">Total Outstanding:</span>
                    <span class="text-lg font-bold text-red-600">₦${formatNumber(totalBalance)}</span>
                </div>
            </div>
        </div>
        
        <!-- Desktop Table View (hidden on small screens) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="overdueTable">
                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">S/N</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Member Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Coop No</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider">Last Loan Collected</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Month of Last Loan</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider">Periods Overdue</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider">Current Balance</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
    `;

    data.data.forEach((loan, index) => {
        html += `
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">${index + 1}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">${escapeHtml(loan.member_name)}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">${escapeHtml(loan.memberid)}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-right">₦${formatNumber(loan.last_loan_amount)}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">${escapeHtml(loan.last_loan_period_name || 'N/A')}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                        ${loan.period_gap} months
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">₦${formatNumber(loan.loan_balance)}</td>
            </tr>
        `;
    });

    html += `
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="6" class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">Total Outstanding:</td>
                        <td class="px-4 py-3 text-sm font-bold text-gray-900 text-right">₦${formatNumber(totalBalance)}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    `;

    $('#overdueDisplay').html(html);
    $('#overdueTable thead th').addClass('sticky top-0 z-20 bg-blue-500 text-white');

    // Store data globally for export
    window.overdueLoansData = data;
    $('#exportExcelBtn').prop('disabled', false);
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

function formatNumber(num) {
    return parseFloat(num).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Export to Excel function
function exportToExcel() {
    if (!window.overdueLoansData || !window.overdueLoansData.data || window.overdueLoansData.data.length === 0) {
        Swal.fire('No Data', 'Please load overdue loans first.', 'info');
        return;
    }

    showBlockingLoader('Exporting to Excel...');

    const data = window.overdueLoansData;
    const currentPeriodName = data.current_period_name || ('Period_' + data.current_period);
    const filename =
        `Overdue_Loans_${currentPeriodName.replace(/[^a-z0-9]/gi, '_')}_${new Date().toISOString().split('T')[0]}`;

    // Create CSV content
    let csvContent = '\uFEFF'; // BOM for UTF-8 Excel compatibility

    // Header row
    csvContent +=
        'S/N,Member Name,Coop No,Last Loan Collected,Month of Last Loan,Periods Overdue (months),Current Balance\n';

    // Data rows
    let totalBalance = 0;
    data.data.forEach((loan, index) => {
        totalBalance += loan.loan_balance;
        csvContent +=
            `${index + 1},"${escapeCsv(loan.member_name)}","${escapeCsv(loan.memberid)}",${loan.last_loan_amount},"${escapeCsv(loan.last_loan_period_name || 'N/A')}",${loan.period_gap},${loan.loan_balance}\n`;
    });

    // Summary row
    csvContent += `\n,"Total Outstanding:","",,,,"${totalBalance.toFixed(2)}"\n`;

    // Create and download
    const blob = new Blob([csvContent], {
        type: 'text/csv;charset=utf-8;'
    });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);

    link.setAttribute('href', url);
    link.setAttribute('download', filename + '.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    hideBlockingLoader();
    Swal.fire('Exported!', 'Excel file exported successfully.', 'success');
}

function escapeCsv(text) {
    if (text === null || text === undefined) return '';
    const str = String(text);
    if (str.includes(',') || str.includes('"') || str.includes('\n')) {
        return '"' + str.replace(/"/g, '""') + '"';
    }
    return str;
}

// Auto-load on page load
$(document).ready(function() {
    getOverdueLoans();
});
</script>

<style>
/* Desktop table styling */
@media (min-width: 768px) {
    #overdueDisplay table {
        min-width: 1000px;
    }

    #overdueDisplay table thead th {
        position: sticky;
        top: 0;
        z-index: 10;
    }
}

/* Mobile card view - ensure proper spacing */
@media (max-width: 767px) {
    #overdueDisplay .space-y-4>*+* {
        margin-top: 1rem;
    }
}
</style>