<?php
require_once('header.php');
if (!isset($_SESSION['UserID'])) {
    header("Location:index.php");
    exit;
}
require_once('Connections/cov.php');

// Fetch all periods for dropdowns
$periods = [];
$res = $cov->query("SELECT Periodid, PayrollPeriod FROM tbpayrollperiods ORDER BY Periodid DESC");
if ($res) $periods = $res->fetch_all(MYSQLI_ASSOC);
?>
<div class="flex min-h-screen">
    <main class="flex-1 py-8 px-2 md:px-10 bg-gray-50">
        <div class="max-w-6xl mx-auto">
            <h1 class="text-2xl font-bold text-blue-900 mb-6">Master Transaction Status</h1>
            <!-- Period Selection Row -->
            <div class="flex flex-col sm:flex-row gap-3 mb-4 items-center sm:items-end">
                <div class="flex gap-2 w-full sm:w-auto">
                    <label for="fromPeriodId" class="block font-semibold mt-2 sm:mt-0">Period:</label>
                    <select id="fromPeriodId" class="border rounded px-2 py-1 w-36">
                        <?php foreach($periods as $p): ?>
                            <option value="<?= $p['Periodid'] ?>"><?= htmlspecialchars($p['PayrollPeriod']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="mx-1 mt-2 sm:mt-0">to</span>
                    <select id="toPeriodId" class="border rounded px-2 py-1 w-36">
                        <?php foreach($periods as $p): ?>
                            <option value="<?= $p['Periodid'] ?>"><?= htmlspecialchars($p['PayrollPeriod']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button onclick="getMasterTransaction()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded w-full sm:w-auto">Get Result</button>
            </div>
            <!-- Search Row -->
            <div class="flex flex-col sm:flex-row gap-3 mb-4">
                <div class="flex gap-2 w-full sm:w-1/2">
                    <input type="text" id="search" placeholder="Search member..." class="border rounded px-2 py-1 flex-1 w-full" autocomplete="off">
                    <input type="hidden" id="memberid">
                </div>
            </div>

            <!-- Loader -->
            <div id="wait" style="display:none;" class="mb-2">
                <div class="flex items-center gap-2">
                    <img src="images/pageloading.gif" class="h-6 w-6"> <span>Please wait...</span>
                </div>
            </div>
            <!-- Table Results -->
            <div id="status" class="rounded shadow bg-white p-3 overflow-x-auto">
                <!-- Results table will appear here -->
            </div>
        </div>
    </main>
</div>
<?php require_once('footer.php'); ?>

<script>
function showBlockingLoader(msg = 'Loading, please wait...') {
    Swal.fire({
        title: '<div class="flex flex-col items-center gap-4"><svg class="animate-spin h-10 w-10 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg><span class="mt-2 text-blue-800 font-semibold">'+msg+'</span></div>',
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

function getMasterTransaction() {
    const fromPeriod = $('#fromPeriodId').val();
    const toPeriodId = $('#toPeriodId').val();
    const memberid = $('#memberid').val();
    if (!fromPeriod || !toPeriodId) { 
        Swal.fire('Select period range.', '', 'warning');
        return;
    }
    if (parseInt(fromPeriod) > parseInt(toPeriodId)) {
        Swal.fire("From Period cannot be Greater Than To Period", '', 'error');
        return;
    }
    showBlockingLoader();
    $('#status').html('');
    $.get('getMasterTransaction.php', {
        id: memberid,
        periodTo: toPeriodId,
        periodfrom: fromPeriod,
        filename: ''
    }, function(html) {
        $('#status').html(html);
        hideBlockingLoader();
        $('#status table thead th').addClass('sticky top-0 z-20 bg-blue-500 text-white');
        $('#status table').parent().css({'max-height':'500px','overflow-y':'auto'});
    });
}

// DELETE SELECTED ROWS
$(document).on('click', '#deleteT', function() {
    let checkboxes = $('input[name="memberid"]:checked');
    if (checkboxes.length === 0) {
        Swal.fire('Please select at least one item to delete', '', 'info');
        return;
    }
    Swal.fire({
        title: 'Are you sure?',
        text: 'This action will delete the selected item(s)!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            showBlockingLoader("Deleting...");
            let transactionIds = [];
            checkboxes.each(function() {
                transactionIds.push($(this).val());
            });
            $.ajax({
                type: "POST",
                url: "deletetransaction.php",
                data: { transactionIds: transactionIds },
                success: function(response) {
                $('#wait').css('visibility', 'hidden');
                $('#status').css('visibility', 'visible');
                if (response.success) {
                    Swal.fire("Deleted!", "Selected item(s) deleted successfully.", "success").then(() => {
                        window.location.href = "mastertransaction.php";
                    });
                } else {
                    Swal.fire("Error", "Delete failed: " + (response.error || "Unknown error"), "error");
                }
                },
                error: function(xhr, status, error) {
                    $('#wait').css('visibility', 'hidden');
                    $('#status').css('visibility', 'visible');
                    Swal.fire("AJAX Error", "There was a problem while using AJAX:\n" + xhr.statusText, "error");
                }

            });
        }
    });
});

// EXPORT PDF
$(document).on('click','#exportpdf',function(){
    var table = document.getElementById('sample_1').outerHTML;
    var selectTo = document.getElementById('toPeriodId');
    var selectFr = document.getElementById('fromPeriodId');
    var selectedToFilename = selectTo.options[selectTo.selectedIndex].text;
    var selectedFrFilename = selectFr.options[selectFr.selectedIndex].text;
    var filename = selectedFrFilename+'_'+selectedToFilename;

    showBlockingLoader("Exporting PDF...");
    $.ajax({
        url: 'export_pdf.php',
        type: 'POST',
        data: {html: table, filename: filename},
        xhrFields: {
            responseType: 'blob'
        },
        success: function (data) {
            hideBlockingLoader();
            var a = document.createElement('a');
            var url = window.URL.createObjectURL(data);
            a.href = url;
            a.download = filename + '.pdf';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            a.remove();
            Swal.fire('Exported!', 'PDF exported successfully.', 'success');
        },
        error: function () {
            hideBlockingLoader();
            Swal.fire('Failed', 'Failed to export table as PDF.', 'error');
        }
    });
});

// EXPORT EXCEL
$(document).on('click', '#exportexcel', function() {
    var table = document.getElementById('sample_1').outerHTML;
    var selectTo = document.getElementById('toPeriodId');
    var selectFr = document.getElementById('fromPeriodId');
    var selectedToFilename = selectTo.options[selectTo.selectedIndex].text;
    var selectedFrFilename = selectFr.options[selectFr.selectedIndex].text;
    var filename = selectedFrFilename+'_'+selectedToFilename;
    Swal.fire({
        title: "Employee's Email",
        input: "email",
        inputLabel: "Please enter the employee's email address:",
        inputPlaceholder: "someone@email.com",
        showCancelButton: true,
        confirmButtonText: 'Export',
        inputValidator: (value) => {
            if (!value) return 'You need to enter an email address!';
            if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(value)) return 'Please enter a valid email address!';
        }
    }).then((result) => {
        if (result.isConfirmed) {
            showBlockingLoader("Exporting Excel...");
            $.ajax({
                url: 'export.php',
                type: 'POST',
                data: {
                    html: table,
                    email: result.value,
                    filename: filename
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function (data) {
                    hideBlockingLoader();
                    var a = document.createElement('a');
                    var url = window.URL.createObjectURL(data);
                    a.href = url;
                    a.download = filename + '.xlsx';
                    document.body.append(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    a.remove();
                    Swal.fire('Exported!', 'Excel file exported successfully.', 'success');
                },
                error: function () {
                    hideBlockingLoader();
                    Swal.fire('Failed', 'Failed to export table as Excel.', 'error');
                }
            });
        }
    });
});

// AUTOCOMPLETE AND PERIOD SELECT
$(function(){
    $("#search").autocomplete({
        source: "search_members.php",
        minLength: 2,
        select: function(event, ui) {
            $('#memberid').val(ui.item.value);
            $('#search').val(ui.item.membername);
            return false;
        }
    });
    $("#fromPeriodId").on('change', function () {
        $("#toPeriodId").val($(this).val());
    });
});
</script>

<style>
#status table thead th {
    position: sticky;
    top: 0;
    z-index: 10;
}
#status table {
    min-width: 1000px;
}
</style>
