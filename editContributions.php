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
            <div class="md:col-span-2">
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

            <!-- Special Savings Section -->
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

        // Focus on the total amount field
        $('#Amount').focus();

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
                showSpecialSavingsSection();
                $('#specialSavingsAlert').removeClass('hidden');
                $('#specialsavings').val(response.data.special_savings_amount);
                calculateContributions();
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

    // Calculate contributions when amount changes
    $('#Amount').on('input', calculateContributions);

    function calculateContributions() {
        let totalAmount = parseFloat($('#Amount').val()) || 0;
        let specialAmount = 0;

        if (specialSavingsData) {
            specialAmount = parseFloat(specialSavingsData.special_savings_amount) || 0;
            let regularAmount = Math.max(0, totalAmount - specialAmount);

            // Set the values correctly
            $('#regularsavings').val(regularAmount.toFixed(2));
            $('#specialsavings').val(specialAmount.toFixed(2)); // Fixed special savings amount
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


    // Save new contribution
    $('#btnSave').click(function() {
        let fd = $('#contributionForm').serialize();
        let currentPeriodId = $('#PeriodId').val(); // Store current period
        $.post('api/contribution_save.php', fd, function(resp) {
            if (resp.success) {
                Swal.fire('Saved', resp.success, 'success');
                // Reset form but keep the period selected
                $('#contributionForm')[0].reset();
                $('#PeriodId').val(currentPeriodId); // Restore period
                // Clear member info and special savings sections
                $('#memberInfo').addClass('hidden');
                hideSpecialSavingsSection();
                $('#specialSavingsAlert').addClass('hidden');
                specialSavingsData = null;
                $('#btnSave').show();
                $('#btnUpdate').hide();
                // Reload contributions for the current period
                if (currentPeriodId) loadContributions(currentPeriodId);
            } else {
                Swal.fire('Error', resp.error, 'error');
            }
        }, 'json');
    });

    // Edit, Update, Delete logic can be bound here
    $(document).on('click', '.btn-edit', function() {
        // Populate form with the clicked contribution details
        let row = $(this).closest('tr');
        $('#txtContriId').val(row.data('id'));
        $('#txtCoopid').val(row.data('memberid'));
        $('#PeriodId').val(row.data('periodid'));
        $(
            '#CoopName').val(row.data('member_name'));
        $('#Amount').val(row.data(
            'amount'));
        $(
            '#specialsavings').val(row.data('specialsavings'));
        $('#btnSave').hide();
        $(
            '#btnUpdate, #btnDelete').show();
    });

    $('#btnUpdate').click(function() {
        let fd = $('#contributionForm').serialize();
        let currentPeriodId = $('#PeriodId').val(); // Store current period
        $.post('api/contribution_update.php', fd, function(resp) {
            if (resp.success) {
                Swal.fire('Updated', resp.success, 'success');
                // Reset form but keep the period selected
                $('#contributionForm')[0].reset();
                $('#PeriodId').val(currentPeriodId); // Restore period
                // Clear member info and special savings sections
                $('#memberInfo').addClass('hidden');
                hideSpecialSavingsSection();
                $('#specialSavingsAlert').addClass('hidden');
                specialSavingsData = null;
                $('#btnUpdate, #btnDelete').hide();
                $('#btnSave').show();
                // Reload contributions for the current period
                if (currentPeriodId) loadContributions(currentPeriodId);
            } else {
                Swal.fire('Error', resp.error, 'error');
            }
        }, 'json');
    });

    $('#PeriodId').change(function() {

        // let memberId = $('#txtCoopid').val();
        let periodId = $(this).val();
        if (periodId) {
            loadContributions(periodId);
        }
    });

    $(document).on('click', '.btn-delete', function() {
        let row = $(this).closest('tr');
        let contriId = row.data('id');
        let currentPeriodId = $('#PeriodId').val(); // Store current period
        Swal.fire({
            title: "Are you sure?",
            text: "This will delete the record!",
            icon: "warning",
            showCancelButton: true,
        }).then(result => {
            if (result.isConfirmed) {
                $.post('api/contribution_delete.php', {
                    contriId
                }, function(resp) {
                    if (resp.success) {
                        Swal.fire('Deleted', resp.success, 'success');
                        // Refresh the contributions list for the current period
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