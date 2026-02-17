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
        
            <div class="flex flex-col gap-2">
                 <div class="flex gap-2 justify-end">
                    <button type="button" id="btnSelectOverdue" class="text-xs bg-red-100 text-red-700 hover:bg-red-200 px-3 py-1 rounded border border-red-300 transition-colors hidden">
                        Select Overdue
                    </button>
                    <button type="button" id="btnSelectNormal" class="text-xs bg-gray-100 text-gray-700 hover:bg-gray-200 px-3 py-1 rounded border border-gray-300 transition-colors hidden">
                        Select Normal
                    </button>
                 </div>
                 <div class="flex gap-2 justify-end">
                    <button type="button" id="btnPreview" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors duration-200 flex items-center gap-2">
                        <i class="fa fa-search"></i> Preview
                    </button>
                    
                    <div class="relative group">
                        <button type="button" id="btnExportMenu" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors duration-200 flex items-center gap-2" disabled>
                            <i class="fa fa-file-excel"></i> Export <i class="fa fa-chevron-down text-xs ml-1"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden group-hover:block border">
                            <a href="#" id="btnExportAll" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50">Export All</a>
                            <a href="#" id="btnExportOverdue" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">Export Overdue Only</a>
                            <a href="#" id="btnExportNormal" class="block px-4 py-2 text-sm text-green-600 hover:bg-green-50">Export Normal Only</a>
                        </div>
                    </div>

                    <button type="button" id="btnPost" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors duration-200 flex items-center gap-2" disabled>
                        <i class="fa fa-save"></i> Post Normal (Savings)
                    </button>
                    <button type="button" id="btnSaveOverdue" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors duration-200 flex items-center gap-2" disabled>
                        <i class="fa fa-cloud-upload"></i> Save Overdue
                    </button>
                     <button type="button" id="btnDelete" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors duration-200 flex items-center gap-2">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                 </div>
            </div>
    </div>

    <!-- Results Table -->
    <div id="resultsContainer" class="hidden bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800">Preview Results</h3>
            <div class="flex items-center gap-4">
                 <span id="resultCount" class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">0 Members</span>
                 <span id="selectedCount" class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">0 Selected</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            <div class="flex items-center">
                                <input type="checkbox" id="selectAll" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                <span class="ml-2">S/N</span>
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3">Member ID</th>
                        <th scope="col" class="px-6 py-3">Name</th>
                        <th scope="col" class="px-6 py-3 text-right">Share & Savings (₦)</th>
                        <th scope="col" class="px-6 py-3 text-right">Dividend (₦)</th>
                        <th scope="col" class="px-6 py-3 text-center">Status</th>
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
                        <td colspan="1"></td>
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

    // Toggle all checkboxes
    $(document).on('change', '#selectAll', function() {
        $('.member-checkbox').prop('checked', $(this).prop('checked'));
        updateSelectedCount();
    });

    // Individual checkbox change
    $(document).on('change', '.member-checkbox', function() {
        updateSelectedCount();
        // Update header checkbox
        let allChecked = $('.member-checkbox:checked').length === $('.member-checkbox').length;
        $('#selectAll').prop('checked', allChecked);
    });

    // Update selected count and enable/disable button
    function updateSelectedCount() {
        let count = $('.member-checkbox:checked').length;
        // Button enabled if count > 0. (Target Period not needed for Saving to holding, but needed for normal post)
        
        if (count > 0) {
             $('#btnSaveOverdue').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
        } else {
             $('#btnSaveOverdue').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
        }
        $('#selectedCount').text(count + ' Selected');
    }

    $('#dividendPeriod').change(function() {
        checkPostButton(); // For normal post
    });

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
                    let overdueClass = row.is_overdue ? 'bg-red-50' : 'bg-white';
                    let overdueBadge = row.is_overdue ? '<span class="ml-2 px-2 py-0.5 text-xs rounded bg-red-100 text-red-800 font-bold border border-red-200">Overdue Loan</span>' : '<span class="px-2 py-0.5 text-xs rounded bg-green-100 text-green-800 border border-green-200">Normal</span>';
                    
                    html += `<tr class="${overdueClass} border-b hover:bg-gray-100 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <input type="checkbox" class="member-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500" value="${row.memberid}" data-is-overdue="${row.is_overdue}">
                                <span class="ml-2 text-gray-500 text-xs">${index + 1}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900">
                            ${row.memberid}
                        </td>
                        <td class="px-6 py-4 font-semibold text-gray-800">${row.name}</td>
                        <td class="px-6 py-4 text-right">${formatCurrency(row.total_holdings)}</td>
                        <td class="px-6 py-4 text-right font-bold text-green-600">${formatCurrency(row.dividend)}</td>
                        <td class="px-6 py-4 text-center">
                            ${overdueBadge}
                        </td>
                    </tr>`;
                });
                
                $('#resultsBody').html(html);
                $('#totalHoldings').text(formatCurrency(response.totalHoldings));
                $('#totalDividend').text(formatCurrency(response.totalDividend));
                $('#resultCount').text(count + ' Members');
                $('#resultsContainer').removeClass('hidden');
                
                // Reset Selection
                $('#selectAll').prop('checked', false);
                updateSelectedCount();
                
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

    // Post to Ledger (Normal - Savings)
    $('#btnPost').click(function() {
        let basePeriod = $('#period').val();
        let targetPeriod = $('#dividendPeriod').val();
        let percentage = $('#percentage').val();

        if (!basePeriod || !targetPeriod || !percentage) {
            Swal.fire('Error', 'Please ensure all fields are filled (including Target Period).', 'error');
            return;
        }

        Swal.fire({
            title: 'Confirm Posting?',
            text: `You are about to post dividends to the Target Period (Ledger) as Savings. This creates records for ALL eligible members.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#16a34a',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Post All!'
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
    
    // Save Overdue Dividends (New Method)
    $('#btnSaveOverdue').click(function() {
         let basePeriod = $('#period').val();
        let percentage = $('#percentage').val();
        
        // Collect selected members
        let selectedMembers = [];
        let hasNormal = false;
        
        $('.member-checkbox:checked').each(function() {
             let memberId = $(this).val();
             let isOverdue = $(this).data('is-overdue'); // Check if row is overdue
             
             if (isOverdue) {
                 selectedMembers.push(memberId);
             } else {
                 hasNormal = true;
             }
        });

        if (selectedMembers.length === 0) {
             Swal.fire('Selection Required', 'Please select at least one OVERDUE member to save.', 'warning');
             return;
        }
        
        if (hasNormal) {
             // Warn that normal members will be ignored? Or just continue with valid ones.
             // Better to warn.
        }

        Swal.fire({
            title: 'Save Overdue Dividends?',
            text: `You are about to save ${selectedMembers.length} overdue members' dividends to the holding table. They can be imported later in Contribution List.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ea580c',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Save'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.showLoading();
                
                 $.post('api/save_overdue_dividend.php', {
                    basePeriod: basePeriod,
                    percentage: percentage,
                    members: JSON.stringify(selectedMembers)
                }, function(response) {
                    Swal.close();
                    if (response.success) {
                        Swal.fire({
                            title: 'Saved!', 
                            html: response.success, 
                            icon: 'success'
                        });
                    } else {
                        Swal.fire('Error', response.error || 'Unknown error occurred.', 'error');
                    }
                }, 'json').fail(function() {
                     Swal.close();
                    Swal.fire('Error', 'Failed to save dividends.', 'error');
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

    // Select Overdue Handler
    $('#btnSelectOverdue').click(function() {
        $('.member-checkbox').prop('checked', false); // clear
        $('.member-checkbox').each(function() {
            let row = $(this).closest('tr');
            if (row.hasClass('bg-red-50')) { // Overdue rows
                $(this).prop('checked', true);
            }
        });
        updateSelectedCount();
    });

    // Select Normal Handler
    $('#btnSelectNormal').click(function() {
        $('.member-checkbox').prop('checked', false); // clear
        $('.member-checkbox').each(function() {
            let row = $(this).closest('tr');
            if (!row.hasClass('bg-red-50')) { // Normal rows
                $(this).prop('checked', true);
            }
        });
        updateSelectedCount();
    });

    // Export Function
    function exportDividend(filter = 'all') {
        let period = $('#period').val();
        let percentage = $('#percentage').val();

        if (!period || !percentage) {
            Swal.fire('Input Required', 'Please preview dividend first before exporting.', 'warning');
            return;
        }

        // Trigger download
        window.location.href = `api/dividend_export.php?period=${period}&percentage=${percentage}&filter=${filter}`;
        
        Swal.fire({
            icon: 'success',
            title: 'Exporting...',
            text: `Generating ${filter} members report.`,
            timer: 2000,
            showConfirmButton: false
        });
    }

    // Export Buttons Handlers
    $('#btnExportAll').click(function(e) { e.preventDefault(); exportDividend('all'); });
    $('#btnExportOverdue').click(function(e) { e.preventDefault(); exportDividend('overdue'); });
    $('#btnExportNormal').click(function(e) { e.preventDefault(); exportDividend('normal'); });

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
            $('#btnExportMenu').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
             // Show selection helper buttons
             $('#btnSelectOverdue').removeClass('hidden');
             $('#btnSelectNormal').removeClass('hidden');
        } else {
            $('#btnExportMenu').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
             $('#btnSelectOverdue').addClass('hidden');
             $('#btnSelectNormal').addClass('hidden');
        }
    }

    $('#dividendPeriod').change(checkPostButton);
});
</script>

<?php require_once('footer.php'); ?>
