<?php
session_start();
if (!isset($_SESSION['UserID'])) header("Location:index.php");
require_once('Connections/cov.php');
require_once('header.php');
?>
<!-- Your header/nav as before -->

<style>
.suggestionsBox {
    position: absolute;
    z-index: 1000;
    background: white;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    max-height: 200px;
    overflow-y: auto;
    width: 100%;
}

.suggestionList {
    margin: 0;
    padding: 0;
    list-style: none;
}

.suggestionList li {
    padding: 8px 12px;
    cursor: pointer !important;
    border-bottom: 1px solid #f0f0f0;
    transition: background-color 0.2s ease;
}

.suggestionList li:hover {
    background-color: #f8f9fa;
    color: #007bff;
}

.suggestionList li:last-child {
    border-bottom: none;
}
</style>

<div class="container mt-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">Edit Contributions</h1>
        <a href="dashboard.php" class="btn btn-sm bg-blue-600 text-white px-3 py-1 rounded">Dashboard</a>
    </div>
    <!-- Special Savings Alert -->
    <div id="specialSavingsAlert" class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4 hidden">
        <div class="flex items-center">
            <i class="fa fa-star mr-2"></i>
            <div>
                <p class="font-bold">Special Savings Member Detected!</p>
                <p class="text-sm">This member has special savings configured. The contribution will be split
                    automatically.</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded shadow p-4 mb-4">
        <!-- Transaction Type Selector -->
        <div class="mb-6 border-b pb-4">
            <label class="block font-semibold mb-2 text-gray-700">Transaction Type</label>
            <div class="flex gap-4">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="radio" name="transactionType" value="regular" class="form-radio text-blue-600 h-5 w-5" checked>
                    <span class="ml-2 text-gray-800">Regular Contribution</span>
                </label>
                <label class="inline-flex items-center cursor-pointer">
                    <input type="radio" name="transactionType" value="special_repayment" class="form-radio text-purple-600 h-5 w-5">
                    <span class="ml-2 text-gray-800">Special Loan Repayment</span>
                </label>
            </div>
        </div>

        <form id="contributionForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold mb-1">Member</label>
                <input type="text" name="CoopName" id="CoopName" class="w-full border px-3 py-2 rounded"
                    autocomplete="off">
                <div id="suggestions" class="suggestionsBox" style="display:none">
                    <div id="autoSuggestionsList"></div>
                </div>
                <input type="hidden" name="txtCoopid" id="txtCoopid">
                <div id="memberInfo" class="mt-2 p-2 bg-blue-50 rounded hidden">
                    <span class="text-blue-800 font-semibold"></span>
                </div>
            </div>
            <div>
                <label class="block font-semibold mb-1">Period</label>
                <select id="PeriodId" name="PeriodId" class="w-full border px-3 py-2 rounded"></select>
            </div>

            <!-- Regular Contribution Section -->
            <div id="regularContributionSection" class="md:col-span-2">
                <h3 class="text-lg font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <i class="fa fa-piggy-bank text-blue-600"></i>
                    Regular Contribution
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold mb-1">Total Amount</label>
                        <input type="number" name="Amount" id="Amount" class="w-full border px-3 py-2 rounded"
                            step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Regular Savings</label>
                        <input type="number" name="regularsavings" id="regularsavings"
                            class="w-full border px-3 py-2 rounded bg-gray-50" step="0.01" min="0" readonly>
                        <small class="text-gray-500">Auto-calculated</small>
                    </div>
                </div>
            </div>

            <!-- Special Repayment Section (Hidden by default) -->
            <div id="specialRepaymentSection" class="md:col-span-2 hidden">
                <h3 class="text-lg font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <i class="fa fa-money-bill-transfer text-purple-600"></i>
                    Special Loan Repayment
                </h3>
                <div class="bg-purple-50 border border-purple-200 rounded p-4">
                    <label class="block font-semibold mb-1">Repayment Amount</label>
                    <input type="number" id="RepaymentAmount" class="w-full border px-3 py-2 rounded"
                        step="0.01" min="0" placeholder="0.00">
                    <small class="text-gray-600">Enter the amount to repay for the special loan.</small>
                </div>
            </div>

            <!-- Special Savings Section (For Regular Contributions) -->
            <div id="specialSavingsSection" class="md:col-span-2 hidden">
                <h3 class="text-lg font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <i class="fa fa-star text-yellow-600"></i>
                    Special Savings (Additional)
                </h3>
                <div class="bg-yellow-50 border border-yellow-200 rounded p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold mb-1">Special Savings Amount</label>
                            <input type="number" name="specialsavings" id="specialsavings"
                                class="w-full border px-3 py-2 rounded bg-yellow-50" step="0.01" min="0" readonly>
                            <small class="text-gray-500">From special savings configuration</small>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Total Contribution</label>
                            <input type="number" id="totalContribution"
                                class="w-full border px-3 py-2 rounded bg-green-50" step="0.01" min="0" readonly>
                            <small class="text-green-600 font-semibold">Regular + Special</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Section -->
            <div id="contributionSummary" class="md:col-span-2 hidden">
                <h3 class="text-lg font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <i class="fa fa-calculator text-green-600"></i>
                    Contribution Summary
                </h3>
                <div class="bg-green-50 border border-green-200 rounded p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-sm text-gray-600">Regular Savings</p>
                            <p id="summaryRegular" class="text-xl font-bold text-blue-600">₦0.00</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Special Savings</p>
                            <p id="summarySpecial" class="text-xl font-bold text-yellow-600">₦0.00</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total Contribution</p>
                            <p id="summaryTotal" class="text-xl font-bold text-green-600">₦0.00</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2 flex gap-2">
                <button type="button" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded font-semibold"
                    id="btnSave">
                    <i class="fa fa-save mr-2"></i>Save Contribution
                </button>
                <button type="button"
                    class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-2 rounded font-semibold" id="btnUpdate"
                    style="display:none;">
                    <i class="fa fa-edit mr-2"></i>Update Contribution
                </button>
                <button type="button" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded font-semibold"
                    id="btnClear">
                    <i class="fa fa-times mr-2"></i>Clear Form
                </button>
            </div>
            <input type="hidden" name="txtContriId" id="txtContriId">
        </form>
    </div>
    <div id="contributionsList"></div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function() {
    let specialSavingsData = null;
    let currentMode = 'regular'; // 'regular' or 'special_repayment'

    // Check URL for mode parameter
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('mode') === 'special_repayment') {
        currentMode = 'special_repayment';
        $('input[name="transactionType"][value="special_repayment"]').prop('checked', true);
        // Call updateFormMode() after DOM is ready (it's defined below, but we can call it if we move definition up or just wait)
        // Since updateFormMode is defined inside $(function...), we can call it after definition.
        // Let's just set the flag here and call updateFormMode() after function definition.
    }
    
    // Toggle Transaction Type
    $('input[name="transactionType"]').change(function() {
        currentMode = $(this).val();
        updateFormMode();
    });

    function updateFormMode() {
        if (currentMode === 'regular') {
            $('#regularContributionSection').removeClass('hidden');
            $('#specialRepaymentSection').addClass('hidden');
            $('#btnSave').html('<i class="fa fa-save mr-2"></i>Save Contribution');
            
            // Show special savings/summary if applicable
            if (specialSavingsData) {
                $('#specialSavingsSection').removeClass('hidden');
                $('#contributionSummary').removeClass('hidden');
            }
        } else {
            // Special Repayment Mode
            $('#regularContributionSection').addClass('hidden');
            $('#specialRepaymentSection').removeClass('hidden');
            
            // Hide Regular-specific sections
            $('#specialSavingsSection').addClass('hidden');
            $('#contributionSummary').addClass('hidden');
            
            $('#btnSave').html('<i class="fa fa-save mr-2"></i>Save Repayment');
        }
    }
    
    // Initialize mode
    updateFormMode();

    // Load periods dropdown
    $.get('api/periods.php', function(periods) {
        let options = '<option value="">Select...</option>';
        periods.forEach(p => options += `<option value="${p.Periodid}">${p.PayrollPeriod}</option>`);
        $('#PeriodId').html(options);
    }, 'json');

    // Lookup member for auto-suggest
    $('#CoopName').keyup(function() {
        let val = $(this).val();
        if (val.length < 2) {
            $('#suggestions').hide();
            return;
        }
        $.post("api/member_search.php", {
            q: val
        }, function(data) {
            if (data.length > 0) {
                $('#suggestions').show();
                $('#autoSuggestionsList').html(data);
            } else {
                $('#suggestions').hide();
            }
        });
    });

    $(document).on('click', '.suggestionList li', function() {
        let memberId = $(this).attr('data-id');
        let memberName = $(this).text();

        $('#txtCoopid').val(memberId);
        $('#CoopName').val(memberName);
        $('#suggestions').hide();

        // Show selected member info
        $('#memberInfo span').text(memberName);
        $('#memberInfo').removeClass('hidden');

        // Check if member has special savings
        checkSpecialSavings(memberId);

        // Focus on appropriate field
        if (currentMode === 'regular') {
             $('#Amount').focus();
        } else {
             $('#RepaymentAmount').focus();
        }

        let periodId = $('#PeriodId').val();
        if (periodId) loadContributions(periodId);
    });

    // Check if member has special savings
    function checkSpecialSavings(memberId) {
        $.post('api/check_special_savings.php', {
            memberid: memberId
        }, function(response) {
            if (response.success && response.hasSpecialSavings) {
                specialSavingsData = response.data;
                $('#specialSavingsAlert').removeClass('hidden');
                $('#specialsavings').val(response.data.special_savings_amount);
                calculateContributions();
                // Only show section if in regular mode
                if (currentMode === 'regular') {
                    showSpecialSavingsSection();
                }
            } else {
                hideSpecialSavingsSection();
                $('#specialSavingsAlert').addClass('hidden');
                specialSavingsData = null;
            }
        }, 'json').fail(function() {
            hideSpecialSavingsSection();
            $('#specialSavingsAlert').addClass('hidden');
            specialSavingsData = null;
        });
    }

    function showSpecialSavingsSection() {
        $('#specialSavingsSection').removeClass('hidden');
        $('#contributionSummary').removeClass('hidden');
    }

    function hideSpecialSavingsSection() {
        $('#specialSavingsSection').addClass('hidden');
        $('#contributionSummary').addClass('hidden');
        $('#specialsavings').val(0);
    }

    // Calculate contributions for Regular Mode
    $('#Amount').on('input', calculateContributions);

    function calculateContributions() {
        let totalAmount = parseFloat($('#Amount').val()) || 0;
        let specialAmount = 0;

        if (specialSavingsData) {
            specialAmount = parseFloat(specialSavingsData.special_savings_amount) || 0;
            let regularAmount = Math.max(0, totalAmount - specialAmount);

            // Set the values correctly
            $('#regularsavings').val(regularAmount.toFixed(2));
            $('#specialsavings').val(specialAmount.toFixed(2));
            $('#totalContribution').val(totalAmount.toFixed(2));

            // Update summary
            $('#summaryRegular').text('₦' + regularAmount.toFixed(2));
            $('#summarySpecial').text('₦' + specialAmount.toFixed(2));
            $('#summaryTotal').text('₦' + totalAmount.toFixed(2));
        } else {
            $('#regularsavings').val(totalAmount.toFixed(2));
            $('#specialsavings').val(0);
            $('#totalContribution').val(totalAmount.toFixed(2));

            // Update summary
            $('#summaryRegular').text('₦' + totalAmount.toFixed(2));
            $('#summarySpecial').text('₦0.00');
            $('#summaryTotal').text('₦' + totalAmount.toFixed(2));
        }
    }

    // Clear form
    $('#btnClear').click(function() {
        $('#contributionForm')[0].reset();
        $('#memberInfo').addClass('hidden');
        hideSpecialSavingsSection();
        $('#specialSavingsAlert').addClass('hidden');
        specialSavingsData = null;
        $('#btnSave').show();
        $('#btnUpdate').hide();
        
        // Reset to default mode
        $('input[name="transactionType"][value="regular"]').prop('checked', true).trigger('change');
    });

    function loadContributions(periodId) {
        if (!periodId) {
            $('#contributionsList').html('');
            return;
        }
        $.get('api/contributions_list.php', {
            periodId
        }, function(html) {
            $('#contributionsList').html(html);
        });
    }


    // Save Handler (Handles both Regular and Special)
    $('#btnSave').click(function() {
        let memberId = $('#txtCoopid').val();
        let periodId = $('#PeriodId').val();
        
        if (!memberId || !periodId) {
            Swal.fire('Error', 'Please select a member and period.', 'error');
            return;
        }

        if (currentMode === 'regular') {
            // Regular Save
            let fd = $('#contributionForm').serialize();
            $.post('api/contribution_save.php', fd, function(resp) {
                handleSaveResponse(resp, periodId);
            }, 'json');
        } else {
            // Special Repayment Save
            let amount = $('#RepaymentAmount').val();
            if (!amount || amount <= 0) {
                 Swal.fire('Error', 'Please enter a valid repayment amount.', 'error');
                 return;
            }

            $.post('api/contribution_special_save.php', {
                txtCoopid: memberId,
                PeriodId: periodId,
                Amount: amount
            }, function(resp) {
                 handleSaveResponse(resp, periodId);
            }, 'json');
        }
    });

    // Update Handler
    $('#btnUpdate').click(function() {
        let currentPeriodId = $('#PeriodId').val();
        
        if (currentMode === 'regular') {
            let fd = $('#contributionForm').serialize();
            $.post('api/contribution_update.php', fd, function(resp) {
                handleUpdateResponse(resp, currentPeriodId);
            }, 'json');
        } else {
            // Special Repayment Update
            let id = $('#txtContriId').val();
            let amount = $('#RepaymentAmount').val();
            
            $.post('api/contribution_special_update.php', {
                txtContriId: id,
                Amount: amount
            }, function(resp) {
                handleUpdateResponse(resp, currentPeriodId);
            }, 'json');
        }
    });

    $('#PeriodId').change(function() {
        let periodId = $(this).val();
        if (periodId) {
            loadContributions(periodId);
        } else {
            $('#contributionsList').html('');
        }
    });
    
    function handleSaveResponse(resp, periodId) {
        if (resp.success) {
            Swal.fire('Saved', resp.success, 'success');
            resetForm(periodId);
        } else {
            Swal.fire('Error', resp.error, 'error');
        }
    }

    function handleUpdateResponse(resp, periodId) {
        if (resp.success) {
            Swal.fire('Updated', resp.success, 'success');
            resetForm(periodId);
        } else {
            Swal.fire('Error', resp.error, 'error');
        }
    }
    
    function resetForm(periodId) {
        $('#contributionForm')[0].reset();
        $('#PeriodId').val(periodId);
        
        // Clean up UI
        $('#memberInfo').addClass('hidden');
        hideSpecialSavingsSection();
        $('#specialSavingsAlert').addClass('hidden');
        specialSavingsData = null;
        
        $('#btnSave').show();
        $('#btnUpdate').hide();
        
        // Restore mode checkbox based on currentMode variable 
        // (reset() clears it, so we need to set it back)
        $('input[name="transactionType"][value="' + currentMode + '"]').prop('checked', true);
        updateFormMode(); // Ensure UI matches mode

        if (periodId) loadContributions(periodId);
    }

    // Edit Button Click
    $(document).on('click', '.btn-edit', function() {
        let type = $(this).data('type') || 'regular'; // Default to regular if not set
        
        // Switch mode first
        currentMode = type;
        $('input[name="transactionType"][value="' + type + '"]').prop('checked', true).trigger('change');
        
        // Common fields
        $('#txtContriId').val($(this).data('id'));
        $('#txtCoopid').val($(this).data('memberid'));
        $('#PeriodId').val($(this).data('periodid'));
        $('#CoopName').val($(this).data('member_name'));
        $('#memberInfo span').text($(this).data('member_name'));
        $('#memberInfo').removeClass('hidden');

        if (type === 'regular') {
            $('#Amount').val($(this).data('amount'));
            $('#specialsavings').val($(this).data('specialsavings'));
            // Check special savings for regular calculations
            checkSpecialSavings($(this).data('memberid'));
        } else {
            // Special Repayment
            $('#RepaymentAmount').val($(this).data('amount'));
        }

        $('#btnSave').hide();
        $('#btnUpdate').show();
    });

    // Delete Button Click
    $(document).on('click', '.btn-delete', function() {
        let contriId = $(this).data('id');
        let type = $(this).data('type');
        let currentPeriodId = $('#PeriodId').val(); // Store current period
        
        // Safety check for ID
        if (!contriId) {
             Swal.fire('Error', 'Invalid or missing contribution ID.', 'error');
             return;
        }
        
        let endpoint = (type === 'special_repayment') 
            ? 'api/contribution_special_delete.php' 
            : 'api/contribution_delete.php';

        Swal.fire({
            title: "Are you sure?",
            text: "This will delete the record!",
            icon: "warning",
            showCancelButton: true,
        }).then(result => {
            if (result.isConfirmed) {
                $.post(endpoint, {
                    contriId: contriId
                }, function(resp) {
                    if (resp.success) {
                        Swal.fire('Deleted', resp.success, 'success');
                        if (currentPeriodId) loadContributions(currentPeriodId);
                    } else {
                        Swal.fire('Error', resp.error, 'error');
                    }
                }, 'json');
            }
        });
    });

});
</script>
<?php require_once('footer.php'); ?>