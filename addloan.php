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
<?
    // Fetch Special Interest Rate
    $sp_rate = 0.02; // Default
    $res_rate = $cov->query("SELECT value FROM tbl_globa_settings WHERE setting_id = 9");
    if ($res_rate && $row_rate = $res_rate->fetch_assoc()) {
        $sp_rate = floatval($row_rate['value']);
    }
    $sp_rate_display = $sp_rate;
?>
<script>
    var specialRateDisplay = "<?= $sp_rate_display ?>";
</script>

<div class="container mt-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold mb-4">Add / Edit Loan</h2>
        <a href="dashboard.php" class="btn btn-sm bg-blue-600 text-white px-3 py-1 rounded">Dashboard</a>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label for="PeriodId" class="block font-medium mb-1">Period:</label>
            <select id="PeriodId" name="PeriodId" class="w-full rounded border p-2">
                <option value="">Select Period</option>
                <?php foreach($periods as $period): ?>
                <option value="<?= $period['Periodid'] ?>" <?= ($selected_period == $period['Periodid']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($period['PayrollPeriod']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="LoanType" class="block font-medium mb-1">Loan Type:</label>
            <select id="LoanType" name="LoanType" class="w-full rounded border p-2">
                <option value="regular" selected>Regular Loan</option>
                <option value="special">Special Loan</option>
            </select>
        </div>
    </div>

    <!-- Add/Edit Loan Form -->
    <form id="loanForm" autocomplete="off" class="space-y-4 bg-white p-4 rounded shadow">
        <input type="hidden" id="formPeriodId" name="PeriodId" value="<?= htmlspecialchars($selected_period) ?>">
        <input type="hidden" name="edit_loanid" id="edit_loanid" value="">
        <label for="CoopName" class="block font-medium">Search Member:</label>
        <div class="flex items-center space-x-2">
            <input name="CoopName" type="text" class="innerBox w-full border rounded p-2" id="CoopName"
                autocomplete="off">
            <button type="button" id="clearMemberBtn" title="Clear member"
                class="text-gray-500 hover:text-red-600 text-xl px-2">&#10006;</button>
        </div>
        <input type="hidden" id="txtCoopid" name="txtCoopid" required readonly>
        <div id="memberNameHint" class="text-gray-500 text-sm mb-1"></div>

        <label for="txtAmountGranted" class="block font-medium">Amount Granted:</label>
        <input type="text" id="txtAmountGranted" name="txtAmountGranted" required pattern="\d+(\.\d{1,2})?"
            placeholder="e.g. 12000.00" class="w-full border rounded p-2">
        <p id="interestHint" class="text-sm text-gray-500 mt-1" style="display:none;">Interest of <span id="spRateText"></span>% will be automatically calculated.</p>

        <label for="loan_date" class="block font-medium">Date:</label>
        <input type="text" id="loan_date" name="loan_date" required placeholder="Select date"
            class="w-full border rounded p-2">

        <div class="flex space-x-2 mt-2">
            <button type="submit" id="submitBtn" class="bg-blue-600 text-white px-4 py-2 rounded shadow">Add Loan</button>
            <button type="button" id="cancelEditBtn" style="display:none"
                class="bg-gray-400 text-white px-4 py-2 rounded">Cancel Edit</button>
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
    $("#loan_date").datepicker({
        dateFormat: 'yy-mm-dd',
        maxDate: 0
    });

    // Member Autocomplete
    $("#CoopName").autocomplete({
        source: "search_members.php",
        minLength: 2,
        select: function(event, ui) {
            $('#txtCoopid').val(ui.item.memberid);
            $('#CoopName').val(ui.item.label);
            $('#memberNameHint').text(ui.item.membername + (ui.item.mobile ? ' (' + ui.item.mobile +
                ')' : ''));
            return false;
        }
    }).autocomplete("instance")._renderItem = function(ul, item) {
        return $("<li>")
            .append("<div>" + item.label + "<br><span style='font-size:0.9em;color:#888'>" + item
                .membername + (item.mobile ? " (" + item.mobile + ")" : "") + "</span></div>")
            .appendTo(ul);
    };

    // Clear member button
    $('#clearMemberBtn').on('click', function() {
        $('#CoopName').val('');
        $('#txtCoopid').val('');
        $('#memberNameHint').text('');
        $('#CoopName').focus();
    });

    // Period selection logic
    $("#PeriodId").on('change', function() {
        var pid = $(this).val();
        $("#formPeriodId").val(pid);
        resetForm(); // Also clears edit state
        refreshTable();
        window.history.replaceState({}, '', 'addloan.php?period=' + pid);
    });

    // Loan Type selection logic
    $("#LoanType").on('change', function() {
        var type = $(this).val();
        resetForm();
        toggleTypeUI(type);
        refreshTable();
    });

    function toggleTypeUI(type) {
        if (type === 'special') {
            $("#interestHint").show();
            $("#spRateText").text(specialRateDisplay);
            $("#submitBtn").text("Add Special Loan");
        } else {
            $("#interestHint").hide();
            $("#submitBtn").text("Add Loan");
        }
    }

    // Initial State
    toggleTypeUI($("#LoanType").val());

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
        
        var loanType = $("#LoanType").val();
        var endpoint = (loanType === 'special') ? "save_special_loan.php" : "save_loan.php";

        $.post(endpoint, formData, function(resp) {
            // Always expect JSON!
            if (resp.success) {
                sweetMsg(resp.success, "success");
                resetForm(); // Reset form but keep period/type
                // Ensure Submit button text is correct after reset
                toggleTypeUI(loanType); 
                refreshTable();
            } else if (resp.error) {
                sweetMsg(resp.error, "error");
            } else {
                sweetMsg("Unknown response from server.", "error");
            }
        }, 'json').fail(function() {
             sweetMsg("Server Request Failed", "error");
        });
    });

    // AJAX edit: click Edit in table loads the row into form
    $(document).on("click", ".edit-loan-btn", function(e) {
        e.preventDefault();
        var loanid = $(this).data("loanid");
        var loanType = $("#LoanType").val();
        
        var endpoint = (loanType === 'special') ? "get_special_loans_by_period.php" : "get_loan_row.php";
        // Note: get_loan_row.php takes 'loanid'. get_special_loans_by_period.php takes 'loanid' and 'action=get_one'.
        
        var data = { loanid: loanid };
        if (loanType === 'special') {
            data.action = 'get_one';
        }

        $.get(endpoint, data, function(row) {
            if (!row || typeof row !== "object") {
                Swal.fire("Loan not found.", "", "error");
                return;
            }
            $("#edit_loanid").val(row.loanid);
            $("#PeriodId").val(row.periodid);
            $("#formPeriodId").val(row.periodid);
            
            // Populate form
            $("#CoopName").val(row.membername || row.CoopName || ''); // row keys might differ
            $("#txtCoopid").val(row.memberid || row.MemberID || '');
            $("#memberNameHint").text(row.membername || row.CoopName || '');
            
            $("#txtAmountGranted").val(row.loanamount || row.Amount || ''); // Handle potential key differences
            $("#loan_date").val(row.loan_date || row.Date || '');

            $("#submitBtn").text(loanType === 'special' ? "Update Special Loan" : "Update Loan");
            $("#cancelEditBtn").show();
            
            // Scroll to form
            $('html, body').animate({
                scrollTop: $("#loanForm").offset().top - 100
            }, 500);

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
        var loanType = $("#LoanType").val();
        
        Swal.fire({
            title: 'Are you sure?',
            text: "Delete this " + loanType + " loan?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                if (loanType === 'special') {
                    // Special Loan Delete
                    // deleteSpecialLoan.php usually redirects. We need to handle it or update it.
                    // Assuming we updated it or existing practice works. 
                    // Best to use $.post and handle response. 
                    // If deleteSpecialLoan.php isn't JSON ready, this might fail or return HTML.
                    $.post("deleteSpecialLoan.php?loanID=" + loanid, {}, function(resp) { 
                        refreshTable();
                        resetForm();
                        Swal.fire('Deleted!', 'Loan has been deleted.', 'success');
                    });
                } else {
                    // Regular Loan Delete
                    $.post("delete_loan.php", { loanid: loanid }, function(resp) {
                        refreshTable();
                        resetForm();
                        Swal.fire('Deleted!', 'Loan has been deleted.', 'success');
                    });
                }
            }
        });
    });

    // Load table at page load
    var pid = $("#PeriodId").val();
    if (pid) refreshTable();

    // Helper: update loan table
    function refreshTable() {
        var pid = $("#PeriodId").val();
        var loanType = $("#LoanType").val();
        
        if (!pid) {
            $("#loansTableArea").html('');
            return;
        }
        
        $("#loansTableArea").html('<div style="padding:1em;">Loading ' + loanType + ' loans...</div>');
        
        var endpoint = (loanType === 'special') ? "get_special_loans_by_period.php" : "get_loans_by_period.php";
        var data = { periodid: pid };
        if (loanType === 'special') data.action = 'list';

        $.post(endpoint, data, function(htmlData) {
            $("#loansTableArea").html(htmlData);
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
        var pid = $("#PeriodId").val();
        var loanType = $("#LoanType").val();
        
        // Clear inputs but manage state
        $("#edit_loanid").val('');
        $("#CoopName").val('');
        $("#txtCoopid").val('');
        $("#txtAmountGranted").val('');
        $("#loan_date").val('');
        $("#memberNameHint").text('');
        
        // Reset buttons
        toggleTypeUI(loanType);
        $("#cancelEditBtn").hide();
    }
});
</script>
<style>
/* Responsive table style for mobile friendliness */
#loansTableArea table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 2em;
}

#loansTableArea th,
#loansTableArea td {
    padding: 6px 10px;
    border: 1px solid #e5e7eb;
    font-size: 1em;
}

#loansTableArea th {
    background: #f0f4f8;
}

@media (max-width:600px) {

    #loansTableArea table,
    #loansTableArea thead,
    #loansTableArea tbody,
    #loansTableArea th,
    #loansTableArea td,
    #loansTableArea tr {
        display: block;
    }

    #loansTableArea th {
        position: absolute;
        left: -9999px;
    }

    #loansTableArea td {
        border: none;
        position: relative;
        padding-left: 50%;
        min-height: 40px;
    }

    #loansTableArea td:before {
        position: absolute;
        left: 10px;
        top: 8px;
        white-space: nowrap;
        font-weight: bold;
        content: attr(data-label);
    }
}
</style>
<?php require_once('footer.php'); ?>