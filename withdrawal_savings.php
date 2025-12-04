<?php
session_start();
if (!isset($_SESSION['UserID'])) {
    header("Location:index.php");
    exit;
}
require_once('header.php');
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
        <h2 class="text-2xl font-semibold mb-4">Withdraw from Savings</h2>
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

    <!-- Withdraw Form -->
    <form id="withdrawForm" autocomplete="off" class="space-y-4 bg-white p-4 rounded shadow">
        <input type="hidden" id="formPeriodId" name="PeriodId" value="<?= htmlspecialchars($selected_period) ?>">
        <label for="CoopName" class="block font-medium">Search Member:</label>
        <div class="flex items-center space-x-2">
            <input name="CoopName" type="text" class="innerBox w-full border rounded p-2" id="CoopName"
                autocomplete="off">
            <button type="button" id="clearMemberBtn" title="Clear member"
                class="text-gray-500 hover:text-red-600 text-xl px-2">&#10006;</button>
        </div>
        <input type="hidden" id="txtCoopid" name="txtCoopid" required readonly>
        <div id="memberNameHint" class="text-gray-500 text-sm mb-1"></div>

        <label for="availableContribution" class="block font-medium">Available Contribution:</label>
        <input type="text" id="availableContribution" name="availableContribution"
            class="w-full border rounded p-2 bg-gray-100" readonly value="0.00">

        <label for="Amount" class="block font-medium">Amount to Withdraw:</label>
        <input type="text" id="Amount" name="Amount" required placeholder="e.g. 5000.00"
            class="w-full border rounded p-2" autocomplete="off">

        <div class="flex space-x-2 mt-2">
            <button type="submit" id="submitBtn"
                class="bg-blue-600 text-white px-4 py-2 rounded shadow">Withdraw</button>
            <button type="button" id="resetBtn" class="bg-gray-400 text-white px-4 py-2 rounded">Reset</button>
        </div>
    </form>
</div>

<!-- SweetAlert2, jQuery, jQuery UI (place in header.php if used globally) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function() {
    // Member Autocomplete
    $("#CoopName").autocomplete({
        source: "search_members.php",
        minLength: 2,
        select: function(event, ui) {
            $('#txtCoopid').val(ui.item.memberid);
            $('#CoopName').val(ui.item.label);
            $('#memberNameHint').text(ui.item.membername + (ui.item.mobile ? ' (' + ui.item.mobile +
                ')' : ''));
            // Fetch available contribution
            fetchContribution(ui.item.memberid);
            return false;
        }
    }).autocomplete("instance")._renderItem = function(ul, item) {
        return $("<li>")
            .append("<div>" + item.label + "<br><span style='font-size:0.9em;color:#888'>" + item
                .membername + (item.mobile ? " (" + item.mobile + ")" : "") + "</span></div>")
            .appendTo(ul);
    };

    // Period selection logic
    $("#PeriodId").on('change', function() {
        var pid = $(this).val();
        $("#formPeriodId").val(pid);
        resetForm();
        window.history.replaceState({}, '', 'withdrawal_savings.php?period=' + pid);
    });

    // Fetch available contribution
    function fetchContribution(memberid) {
        if (!memberid) {
            $('#availableContribution').val('0.00');
            return;
        }
        $.get('getContribution_withdrawal.php', {
            id: memberid
        }, function(resp) {
            // Try to extract the value from the hidden input in the response
            var match = resp.match(/<input[^>]*id=["']contribution["'][^>]*value=["']([\d.]+)["']/);
            if (match && match[1]) {
                let amount = parseFloat(match[1]).toFixed(2);
                $('#availableContribution').val('₦' + amount.replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            } else {
                $('#availableContribution').val('₦0.00');
            }
        });
    }

    // AJAX submit
    $("#withdrawForm").on("submit", function(e) {
        e.preventDefault();
        var pid = $("#PeriodId").val();
        var memberid = $('#txtCoopid').val();
        var amount = $('#Amount').val();
        if (!pid) {
            sweetMsg("Please select a period.", "error");
            return false;
        }
        if (!memberid) {
            sweetMsg("Please select a member.", "error");
            return false;
        }
        if (!amount || parseFloat(amount) <= 0) {
            sweetMsg("Please enter a valid withdrawal amount.", "error");
            return false;
        }
        var available = parseFloat($('#availableContribution').val());
        if (parseFloat(amount) > available) {
            sweetMsg("Withdrawal amount exceeds available contribution.", "error");
            return false;
        }
        var formData = $(this).serialize();
        $.post("process_withdrawal.php", formData, function(resp) {
            if (resp.success) {
                sweetMsg(resp.success, "success");
                resetForm();
            } else if (resp.error) {
                sweetMsg(resp.error, "error");
            } else {
                sweetMsg("Unknown response from server.", "error");
            }
        }, 'json');
    });

    // Reset button
    $('#resetBtn').on('click', function() {
        resetForm();
    });

    // Clear member button
    $('#clearMemberBtn').on('click', function() {
        $('#CoopName').val('');
        $('#txtCoopid').val('');
        $('#availableContribution').val('0.00');
        $('#memberNameHint').text('');
        $('#CoopName').focus();
    });

    // Format withdrawal amount as you type
    $('#Amount').on('input', function() {
        let val = $(this).val().replace(/[^\d.]/g, '');
        // Only allow one decimal point
        let parts = val.split('.');
        if (parts.length > 2) {
            val = parts[0] + '.' + parts.slice(1).join('');
        }
        // Format integer part with commas
        let formatted = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        if (parts.length === 2) {
            formatted += '.' + parts[1].slice(0, 2); // max 2 decimals
        }
        $(this).val(formatted);
    });

    // Remove formatting before submit
    $('#withdrawForm').on('submit', function() {
        let amt = $('#Amount').val().replace(/,/g, '');
        $('#Amount').val(amt);
    });

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
        $("#withdrawForm")[0].reset();
        $('#txtCoopid').val('');
        $('#availableContribution').val('0.00');
        $('#memberNameHint').text('');
    }
});
</script>
<style>
/* Responsive form style for mobile friendliness */
@media (max-width:600px) {
    .container {
        padding: 0 0.5em;
    }
}
</style>
<?php require_once('footer.php'); ?>