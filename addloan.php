<?php
require_once('header.php');
if (!isset($_SESSION['UserID'])) {
    header("Location:index.php");
    exit;
}
require_once('Connections/cov.php');

// Fetch all periods
$periods = [];
$res = $cov->query("SELECT Periodid, PayrollPeriod FROM tbpayrollperiods ORDER BY Periodid DESC");
if ($res) $periods = $res->fetch_all(MYSQLI_ASSOC);

$selected_period = '';
if (isset($_GET['period'])) {
    $selected_period = $_GET['period'];
} elseif (count($periods)) {
    $selected_period = $periods[0]['Periodid'];
}
?>

<div class="container mt-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold mb-4">Add / Edit Loan</h2>
        <a href="dashboard.php" class="btn btn-sm bg-blue-600 text-white px-3 py-1 rounded">Dashboard</a>
    </div>

    <!-- Period Dropdown -->
    <label for="PeriodId" class="block font-medium mb-1">Period:</label>
    <select id="PeriodId" name="PeriodId" class="w-full rounded border mb-4 p-2">
        <option value="">Select Period</option>
        <?php foreach($periods as $period): ?>
            <option value="<?= $period['Periodid'] ?>" <?= ($selected_period == $period['Periodid']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($period['PayrollPeriod']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <!-- Add/Edit Loan Form -->
    <form id="loanForm" autocomplete="off" class="space-y-4 bg-white p-4 rounded shadow">
        <input type="hidden" id="formPeriodId" name="PeriodId" value="<?= htmlspecialchars($selected_period) ?>">
        <input type="hidden" name="edit_loanid" id="edit_loanid" value="">
        <label for="CoopName" class="block font-medium">Search Member:</label>
        <input name="CoopName" type="text" class="innerBox w-full border rounded p-2" id="CoopName" autocomplete="off">
        <input type="hidden" id="txtCoopid" name="txtCoopid" required readonly>
        <div id="memberNameHint" class="text-gray-500 text-sm mb-1"></div>

        <label for="txtAmountGranted" class="block font-medium">Amount Granted:</label>
        <input type="text" id="txtAmountGranted" name="txtAmountGranted" required pattern="\d+(\.\d{1,2})?" placeholder="e.g. 12000.00" class="w-full border rounded p-2">

        <label for="loan_date" class="block font-medium">Date:</label>
        <input type="text" id="loan_date" name="loan_date" required placeholder="Select date" class="w-full border rounded p-2">

        <div class="flex space-x-2 mt-2">
            <button type="submit" id="submitBtn" class="bg-blue-600 text-white px-4 py-2 rounded shadow">Add Loan</button>
            <button type="button" id="cancelEditBtn" style="display:none" class="bg-gray-400 text-white px-4 py-2 rounded">Cancel Edit</button>
        </div>
    </form>

    <!-- Table for Loans in Selected Period -->
    <div id="loansTableArea" class="mt-6"></div>
</div>

<!-- SweetAlert2, jQuery, jQuery UI (place in header.php if used globally) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function() {
    $("#loan_date").datepicker({ dateFormat: 'yy-mm-dd', maxDate: 0 });

    // Member Autocomplete
    $("#CoopName").autocomplete({
        source: "search_members.php",
        minLength: 2,
        select: function(event, ui) {
            $('#txtCoopid').val(ui.item.memberid);
            $('#CoopName').val(ui.item.label);
            $('#memberNameHint').text(ui.item.membername + (ui.item.mobile ? ' (' + ui.item.mobile + ')' : ''));
            return false;
        }
    }).autocomplete("instance")._renderItem = function(ul, item) {
        return $("<li>")
            .append("<div>" + item.label + "<br><span style='font-size:0.9em;color:#888'>" + item.membername + (item.mobile ? " (" + item.mobile + ")" : "") + "</span></div>")
            .appendTo(ul);
    };

    // Period selection logic
    $("#PeriodId").on('change', function() {
        var pid = $(this).val();
        $("#formPeriodId").val(pid);
        $("#edit_loanid").val('');
        resetForm();
        updateLoansTable(pid);
        window.history.replaceState({}, '', 'addloan.php?period=' + pid);
    });

    // AJAX submit (Add or Update)
    $("#loanForm").on("submit", function(e) {
        e.preventDefault();
        var pid = $("#PeriodId").val();
        if (!pid) {
            sweetMsg("Please select a period.", "error");
            return false;
        }
        $("#formPeriodId").val(pid);
        var formData = $(this).serialize();
        $.post("save_loan.php", formData, function(resp) {
            // Always expect JSON!
            if (resp.success) {
                sweetMsg(resp.success, "success");
                resetForm();
                updateLoansTable(pid);
            } else if (resp.error) {
                sweetMsg(resp.error, "error");
            } else {
                sweetMsg("Unknown response from server.", "error");
            }
        }, 'json');
    });

    // AJAX edit: click Edit in table loads the row into form
    $(document).on("click", ".edit-loan-btn", function(e) {
        e.preventDefault();
        var loanid = $(this).data("loanid");
        $.get("get_loan_row.php", { loanid: loanid }, function(row) {
            if (!row || typeof row !== "object") {
                Swal.fire("Loan not found.", "", "error"); 
                return;
            }
            $("#edit_loanid").val(row.loanid);
            $("#PeriodId").val(row.periodid);
            $("#formPeriodId").val(row.periodid);
            $("#CoopName").val(row.membername);
            $("#txtCoopid").val(row.memberid);
            $("#memberNameHint").text(row.membername);
            $("#txtAmountGranted").val(row.loanamount);
            $("#loan_date").val(row.loan_date);
            $("#submitBtn").text("Update Loan");
            $("#cancelEditBtn").show();
        }, 'json');
        
    });

    // Cancel Edit
    $("#cancelEditBtn").on("click", function() {
        resetForm();
    });

    // AJAX delete (with SweetAlert2 confirmation)
    $(document).on("click", ".delete-loan-btn", function(e) {
        e.preventDefault();
        var loanid = $(this).data("loanid");
        var pid = $("#PeriodId").val();
        Swal.fire({
            title: 'Are you sure?',
            text: "Delete this loan?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("delete_loan.php", { loanid: loanid }, function(resp) {
                    updateLoansTable(pid);
                    resetForm();
                    Swal.fire('Deleted!', 'Loan has been deleted.', 'success');
                });
            }
        });
    });

    // Load table at page load
    var pid = $("#PeriodId").val();
    if (pid) updateLoansTable(pid);

    // Helper: update loan table
    function updateLoansTable(pid) {
        if (!pid) {
            $("#loansTableArea").html('');
            return;
        }
        $("#loansTableArea").html('<div style="padding:1em;">Loading loans for selected period...</div>');
        $.post("get_loans_by_period.php", { periodid: pid }, function(data) {
            $("#loansTableArea").html(data);
        });
    }
    function sweetMsg(msg, type) {
        Swal.fire({
            icon: type,
            text: msg,
            timer: 2500,
            showConfirmButton: false,
            position: 'top'
        });
    }
    function resetForm() {
        $("#loanForm")[0].reset();
        $("#edit_loanid").val('');
        $("#submitBtn").text("Add Loan");
        $("#cancelEditBtn").hide();
        $("#memberNameHint").text('');
    }
});
</script>
<style>
/* Responsive table style for mobile friendliness */
#loansTableArea table {
    width:100%; border-collapse:collapse; margin-bottom:2em;
}
#loansTableArea th, #loansTableArea td {
    padding:6px 10px; border:1px solid #e5e7eb; font-size:1em;
}
#loansTableArea th { background:#f0f4f8; }
@media (max-width:600px){
    #loansTableArea table, #loansTableArea thead, #loansTableArea tbody, #loansTableArea th, #loansTableArea td, #loansTableArea tr {
        display:block;
    }
    #loansTableArea th { position: absolute; left: -9999px;}
    #loansTableArea td {
        border: none; position: relative; padding-left: 50%; min-height: 40px;
    }
    #loansTableArea td:before {
        position: absolute; left: 10px; top: 8px; white-space: nowrap; font-weight:bold;
        content: attr(data-label);
    }
}
</style>
<?php require_once('footer.php'); ?>
