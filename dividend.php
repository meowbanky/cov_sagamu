<?php
session_start();
if (!isset($_SESSION['UserID'])) header("Location:index.php");
require_once('Connections/cov.php');
require_once('header.php');

// Fetch Periods for Dropdowns
mysqli_select_db($cov, $database_cov);
$query_Period = "SELECT Periodid, PayrollPeriod FROM tbpayrollperiods ORDER BY Periodid DESC";
$Period = mysqli_query($cov, $query_Period) or die(mysqli_error($cov));
$periods = [];
while ($row = mysqli_fetch_assoc($Period)) {
    $periods[] = $row;
}
?>

<div class="container mx-auto mt-6 px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
            <i class="fa fa-calculator text-green-600"></i>
            Dividend Calculator
        </h1>
        <a href="dashboard.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
            <i class="fa fa-arrow-left mr-1"></i> Dashboard
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <form id="dividendForm" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
            <!-- Calculation Period -->
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Base Period (Calculation)</label>
                <select id="period" name="period" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select Period...</option>
                    <?php foreach ($periods as $p): ?>
                        <option value="<?= $p['Periodid'] ?>"><?= $p['PayrollPeriod'] ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="text-gray-500">Select the period to base the calculation on.</small>
            </div>

            <!-- Percentage -->
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Dividend Percentage</label>
                <div class="relative">
                    <input type="number" id="percentage" name="percentage" step="0.01" min="0" placeholder="e.g. 0.05 for 5%" class="w-full border border-gray-300 rounded-lg px-4 py-2 pl-4 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <i class="fa fa-percent text-gray-400"></i>
                    </div>
                </div>
                <small class="text-gray-500">Enter as decimal (e.g. 0.05) or factor.</small>
            </div>

             <!-- Posting Period -->
             <div>
                <label class="block text-gray-700 font-semibold mb-2">Target Period (Posting)</label>
                <select id="dividendPeriod" name="dividendPeriod" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select Period...</option>
                    <?php foreach ($periods as $p): ?>
                        <option value="<?= $p['Periodid'] ?>"><?= $p['PayrollPeriod'] ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="text-gray-500">Select the period to Post/Delete dividends.</small>
            </div>
        </form>
        
        <div class="mt-6 flex gap-3 justify-end border-t pt-4">
             <button type="button" id="btnPreview" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors duration-200 flex items-center gap-2">
                <i class="fa fa-search"></i> Preview Dividend
            </button>
            <button type="button" id="btnExport" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors duration-200 flex items-center gap-2" disabled>
                <i class="fa fa-file-excel"></i> Export to Excel
            </button>
            <button type="button" id="btnPost" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors duration-200 flex items-center gap-2" disabled>
                <i class="fa fa-save"></i> Post Dividend
            </button>
             <button type="button" id="btnDelete" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors duration-200 flex items-center gap-2">
                <i class="fa fa-trash"></i> Delete Dividend
            </button>
        </div>
    </div>

    <!-- Results Table -->
    <div id="resultsContainer" class="hidden bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800">Preview Results</h3>
            <span id="resultCount" class="bg-blue-100 text-blue-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded">0 Members</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">S/N</th>
                        <th scope="col" class="px-6 py-3">Member ID</th>
                        <th scope="col" class="px-6 py-3">Name</th>
                        <th scope="col" class="px-6 py-3 text-right">Share & Savings (₦)</th>
                        <th scope="col" class="px-6 py-3 text-right">Dividend (₦)</th>
                        <th scope="col" class="px-6 py-3">Bank</th>
                        <th scope="col" class="px-6 py-3">Account No</th>
                    </tr>
                </thead>
                <tbody id="resultsBody">
                    <!-- Data will be populated here -->
                </tbody>
                <tfoot class="bg-gray-100 font-bold text-gray-900">
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-right">Total:</td>
                        <td id="totalHoldings" class="px-6 py-4 text-right">₦0.00</td>
                        <td id="totalDividend" class="px-6 py-4 text-right">₦0.00</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let previewData = [];

    // Format currency
    const formatCurrency = (amount) => {
        return '₦' + parseFloat(amount).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    // Preview Button Handler
    $('#btnPreview').click(function() {
        let period = $('#period').val();
        let percentage = $('#percentage').val();

        if (!period || !percentage) {
            Swal.fire('Input Required', 'Please select a Base Period and enter a Percentage.', 'warning');
            return;
        }

        // Show loading
        Swal.fire({
            title: 'Calculating...',
            text: 'Please wait while we calculate estimates.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.get('api/dividend_preview.php', {
            period: period,
            percentage: percentage
        }, function(response) {
            Swal.close();

            if (response.error) {
                Swal.fire('Error', response.error, 'error');
                return;
            }

            // Populate Table
            let html = '';
            let count = 0;
            previewData = response.data; // Store for valid post check

            if (response.data && response.data.length > 0) {
                response.data.forEach((row, index) => {
                    count++;
                    html += `<tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4">${index + 1}</td>
                        <td class="px-6 py-4 font-medium text-gray-900">${row.memberid}</td>
                        <td class="px-6 py-4 font-semibold text-gray-800">${row.name}</td>
                        <td class="px-6 py-4 text-right">${formatCurrency(row.total_holdings)}</td>
                        <td class="px-6 py-4 text-right font-bold text-green-600">${formatCurrency(row.dividend)}</td>
                        <td class="px-6 py-4">${row.bank || '-'}</td>
                        <td class="px-6 py-4 font-mono">${row.AccountNo || '-'}</td>
                    </tr>`;
                });
                
                $('#resultsBody').html(html);
                $('#totalHoldings').text(formatCurrency(response.totalHoldings));
                $('#totalDividend').text(formatCurrency(response.totalDividend));
                $('#resultCount').text(count + ' Members');
                $('#resultsContainer').removeClass('hidden');
                
                // Enable Post button if target period selected
                checkPostButton();
            } else {
                Swal.fire('No Data', 'No eligible members found for calculation.', 'info');
                $('#resultsContainer').addClass('hidden');
            }
        }, 'json').fail(function() {
            Swal.close();
            Swal.fire('Error', 'Failed to communicate with server.', 'error');
        });
    });

    // Post Button Handler
    $('#btnPost').click(function() {
        let basePeriod = $('#period').val();
        let targetPeriod = $('#dividendPeriod').val();
        let percentage = $('#percentage').val();

        if (!basePeriod || !targetPeriod || !percentage) {
            Swal.fire('Error', 'Please ensure all fields are filled.', 'error');
            return;
        }

        Swal.fire({
            title: 'Confirm Posting?',
            text: `You are about to post dividends to the Target Period. This action will create transaction records.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#16a34a',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Post It!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Perform Post
                $.post('api/dividend_post.php', {
                    basePeriod: basePeriod,
                    targetPeriod: targetPeriod,
                    percentage: percentage
                }, function(response) {
                    if (response.success) {
                        Swal.fire('Success', response.success, 'success');
                    } else {
                        Swal.fire('Error', response.error || 'Unknown error occurred.', 'error');
                    }
                }, 'json').fail(function() {
                    Swal.fire('Error', 'Failed to post dividends.', 'error');
                });
            }
        });
    });

    // Delete Button Handler
    $('#btnDelete').click(function() {
        let targetPeriod = $('#dividendPeriod').val();
        
        if (!targetPeriod) {
            Swal.fire('Target Period Required', 'Please select a Target Period to delete dividends from.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Delete Dividends?',
            text: "This will delete ALL dividend-like transactions (savings-only) from the selected Target Period. Are you sure?",
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, Delete!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('api/dividend_delete.php', {
                    targetPeriod: targetPeriod
                }, function(response) {
                    if (response.success) {
                        Swal.fire('Deleted!', response.success, 'success');
                    } else {
                        Swal.fire('Error', response.error || 'Failed to delete.', 'error');
                    }
                }, 'json');
            }
        });
    });

    // Export to Excel Button Handler
    $('#btnExport').click(function() {
        let period = $('#period').val();
        let percentage = $('#percentage').val();

        if (!period || !percentage) {
            Swal.fire('Input Required', 'Please preview dividend first before exporting.', 'warning');
            return;
        }

        // Trigger download
        window.location.href = `api/dividend_export.php?period=${period}&percentage=${percentage}`;
        
        Swal.fire({
            icon: 'success',
            title: 'Exporting...',
            text: 'Your Excel file is being generated.',
            timer: 2000,
            showConfirmButton: false
        });
    });

    // Check if Post and Export buttons should be enabled
    function checkPostButton() {
        let hasData = previewData.length > 0;
        let hasTarget = $('#dividendPeriod').val();
        
        if (hasData && hasTarget) {
            $('#btnPost').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
        } else {
            $('#btnPost').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
        }
        
        // Enable export button if we have preview data
        if (hasData) {
            $('#btnExport').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
        } else {
            $('#btnExport').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
        }
    }

    $('#dividendPeriod').change(checkPostButton);
});
</script>

<?php require_once('footer.php'); ?>
