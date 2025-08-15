<?php
session_start();
if (!isset($_SESSION['UserID'])) header("Location:index.php");
require_once('Connections/cov.php');
require_once('header.php');
?>
<!-- Your header/nav as before -->

<div class="container mt-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">Edit Contributions</h1>
        <a href="dashboard.php" class="btn btn-sm bg-blue-600 text-white px-3 py-1 rounded">Dashboard</a>
    </div>
    <div class="bg-white rounded shadow p-4 mb-4">
        <form id="contributionForm" class="grid grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold mb-1">Member</label>
                <input type="text" name="CoopName" id="CoopName" class="w-full border px-3 py-2 rounded"
                    autocomplete="off">
                <div id="suggestions" class="suggestionsBox" style="display:none">
                    <div id="autoSuggestionsList"></div>
                </div>
                <input type="hidden" name="txtCoopid" id="txtCoopid">

            </div>
            <div>
                <label class="block font-semibold mb-1">Period</label>
                <select id="PeriodId" name="PeriodId" class="w-full border px-3 py-2 rounded"></select>
            </div>
            <div>
                <label class="block font-semibold mb-1">Amount</label>
                <input type="number" name="Amount" id="Amount" class="w-full border px-3 py-2 rounded">
            </div>
            <div>
                <label class="block font-semibold mb-1">Special Savings</label>
                <input type="number" name="specialsavings" id="specialsavings" class="w-full border px-3 py-2 rounded"
                    value="0">
            </div>
            <div class="col-span-2 flex gap-2">
                <button type="button" class="bg-green-600 text-white px-4 py-2 rounded" id="btnSave">Save</button>
                <button type="button" class="bg-yellow-600 text-white px-4 py-2 rounded" id="btnUpdate"
                    style="display:none;">Update</button>

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
        $('#txtCoopid').val(memberId);
        $('#CoopName').val($(this).text());
        $('#suggestions').hide();
        let periodId = $('#PeriodId').val();
        if (periodId) loadContributions(periodId);
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
        $.post('api/contribution_save.php', fd, function(resp) {
            if (resp.success) {
                Swal.fire('Saved', resp.success, 'success');
                let periodId = $('#PeriodId').val();
                if (periodId) loadContributions(periodId);

                $('#contributionForm')[0].reset();
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
        $.post('api/contribution_update.php', fd, function(resp) {
            if (resp.success) {
                Swal.fire('Updated', resp.success, 'success');
                let periodId = $('#PeriodId').val();
                if (periodId) loadContributions(periodId);

                $('#contributionForm')[0].reset();
                $('#btnUpdate, #btnDelete').hide();
                $('#btnSave').show();
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
                        let periodId = $('#PeriodId').val();
                        if (periodId) loadContributions(periodId);
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