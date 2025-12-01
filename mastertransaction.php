<?php
// Check authentication BEFORE including header
session_start();
if (!isset($_SESSION['UserID'])) {
    header("Location:index.php");
    exit;
}

// Now include header after authentication check
require_once('header.php');
require_once('Connections/cov.php');

// Fetch all periods for dropdowns
$periods = [];
$res = $cov->query("SELECT Periodid, PayrollPeriod FROM tbpayrollperiods ORDER BY Periodid DESC");
if ($res) $periods = $res->fetch_all(MYSQLI_ASSOC);
?>
<div class="flex min-h-screen" style="overflow-x: hidden; width: 100%; max-width: 100%; box-sizing: border-box;">
    <main class="flex-1 py-8 px-2 md:px-10 bg-gray-50"
        style="overflow-x: hidden; width: 100%; max-width: 100%; box-sizing: border-box;">
        <div class="max-w-6xl mx-auto" style="width: 100%; max-width: 100%; box-sizing: border-box;">
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
                <button onclick="getMasterTransaction()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded w-full sm:w-auto">Get
                    Result</button>
            </div>
            <!-- Multi-Select Member Search -->
            <div class="mb-4">
                <div class="flex gap-2 w-full sm:w-2/3">
                    <div class="flex-1">
                        <input type="text" id="memberSearch" placeholder="Search and select multiple members..."
                            class="border rounded px-3 py-2 w-full" autocomplete="off">
                        <input type="hidden" id="selectedMembers">
                    </div>
                    <button type="button" id="clearAllMembersBtn" title="Clear all selected members"
                        class="text-gray-500 hover:text-red-600 text-xl px-3 py-2 border rounded">Clear All</button>
                </div>

                <!-- Selected Members Display -->
                <div id="selectedMembersDisplay"
                    class="mt-3 flex flex-wrap gap-2 min-h-[40px] p-2 border border-gray-200 rounded bg-gray-50">
                    <span class="text-gray-500 text-sm">No members selected</span>
                </div>
            </div>

            <!-- Loader -->
            <div id="wait" style="display:none;" class="mb-2">
                <div class="flex items-center gap-2">
                    <img src="images/pageloading.gif" class="h-6 w-6"> <span>Please wait...</span>
                </div>
            </div>
            <!-- Table Results -->
            <div id="status" class="rounded shadow bg-white p-3 overflow-x-auto"
                style="width: 100%; max-width: 100%; box-sizing: border-box; overflow-x: auto; overflow-y: visible;">
                <!-- Results table will appear here -->
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

function getMasterTransaction() {
    const fromPeriod = $('#fromPeriodId').val();
    const toPeriodId = $('#toPeriodId').val();
    const selectedMembers = $('#selectedMembers').val();

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

    // Convert comma-separated member IDs to array
    const memberIds = selectedMembers ? selectedMembers.split(',') : [];

    $.get('getMasterTransaction.php', {
        memberIds: memberIds,
        periodTo: toPeriodId,
        periodfrom: fromPeriod,
        filename: ''
    }, function(html) {
        $('#status').html(html);
        hideBlockingLoader();
        $('#status table thead th').addClass('sticky top-0 z-20 bg-blue-500 text-white');
        $('#status table').parent().css({
            'max-height': '500px',
            'overflow-y': 'auto'
        });
    });
}

// DELETE SELECTED ROWS WITH ACCOUNTING REVERSAL
$(document).on('click', '#deleteT', function() {
    let checkboxes = $('input[name="memberid"]:checked');
    if (checkboxes.length === 0) {
        Swal.fire('Please select at least one item to delete', '', 'info');
        return;
    }

    // Check if any selected transactions have accounting entries
    let transactionsWithEntries = [];
    let transactionIds = [];

    checkboxes.each(function() {
        const value = $(this).val();
        transactionIds.push(value);

        if ($(this).data('has-entries') == '1') {
            const memberId = $(this).data('memberid');
            const periodId = $(this).data('periodid');
            transactionsWithEntries.push({
                memberId,
                periodId
            });
        }
    });

    // Show appropriate confirmation message
    let confirmTitle = 'Are you sure?';
    let confirmHtml = '<p class="mb-4">This action will delete the selected transaction(s).</p>';

    if (transactionsWithEntries.length > 0) {
        confirmTitle = '⚠️ Transactions with Accounting Entries';
        confirmHtml = `
            <div class="text-left">
                <p class="mb-4"><strong>${transactionsWithEntries.length} transaction(s) have been posted to the accounting system.</strong></p>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <p class="font-semibold text-blue-900 mb-2">What will happen:</p>
                    <ol class="list-decimal list-inside space-y-2 text-sm text-blue-800">
                        <li><strong>Automatic Reversal:</strong> All related journal entries will be reversed</li>
                        <li><strong>Member Accounts:</strong> Balances will be corrected automatically</li>
                        <li><strong>Transaction Deleted:</strong> The transaction will be removed from the database</li>
                        <li><strong>Audit Trail:</strong> All reversals will be logged for compliance</li>
                    </ol>
                </div>
                <p class="text-sm text-gray-700 mb-2"><strong>This is safe and maintains accounting integrity.</strong></p>
                <p class="text-xs text-gray-500">If you need to correct the transaction instead, cancel and re-process with correct values.</p>
            </div>
        `;
    }

    Swal.fire({
        title: confirmTitle,
        html: confirmHtml,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: transactionsWithEntries.length > 0 ? 'Yes, Reverse & Delete' : 'Yes, Delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc2626',
        width: '600px'
    }).then((result) => {
        if (result.isConfirmed) {
            // If there are accounting entries, reverse them first
            if (transactionsWithEntries.length > 0) {
                reverseAndDelete(transactionsWithEntries, transactionIds);
            } else {
                // No accounting entries, just delete
                deleteTransactions(transactionIds);
            }
        }
    });
});

// Function to reverse accounting entries and then delete transactions
async function reverseAndDelete(transactionsWithEntries, transactionIds) {
    showBlockingLoader("Step 1/2: Reversing accounting entries...");

    try {
        // Reverse each transaction's accounting entries
        let reversalErrors = [];
        for (let i = 0; i < transactionsWithEntries.length; i++) {
            const transaction = transactionsWithEntries[i];

            const response = await $.ajax({
                type: "POST",
                url: "api/reverse_transaction.php",
                data: {
                    memberid: transaction.memberId,
                    periodid: transaction.periodId
                },
                dataType: 'json'
            });

            if (!response.success) {
                reversalErrors.push(`Member ${transaction.memberId}: ${response.error}`);
            }
        }

        if (reversalErrors.length > 0) {
            hideBlockingLoader();
            Swal.fire({
                icon: 'error',
                title: 'Reversal Failed',
                html: '<p class="mb-2">Could not reverse accounting entries:</p><ul class="text-left text-sm">' +
                    reversalErrors.map(e => `<li>${e}</li>`).join('') + '</ul>',
                confirmButtonColor: '#dc2626'
            });
            return;
        }

        // All reversals successful, now delete transactions
        showBlockingLoader("Step 2/2: Deleting transactions...");
        deleteTransactions(transactionIds);

    } catch (error) {
        hideBlockingLoader();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to reverse accounting entries: ' + (error.responseText || error.message),
            confirmButtonColor: '#dc2626'
        });
    }
}

// Function to delete transactions
function deleteTransactions(transactionIds) {
    $.ajax({
        type: "POST",
        url: "deletetransaction.php",
        data: {
            transactionIds: transactionIds
        },
        success: function(response) {
            hideBlockingLoader();
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    html: '<p>Transactions deleted successfully.</p><p class="text-sm text-gray-600 mt-2">Accounting entries have been reversed and audit trail updated.</p>',
                    confirmButtonColor: '#16a34a'
                }).then(() => {
                    window.location.href = "mastertransaction.php";
                });
            } else {
                Swal.fire("Error", "Delete failed: " + (response.error || "Unknown error"), "error");
            }
        },
        error: function(xhr, status, error) {
            hideBlockingLoader();
            Swal.fire("Error", "AJAX Error: " + xhr.statusText, "error");
        }
    });
}

// EXPORT PDF
$(document).on('click', '#exportpdf', function() {
    var table = document.getElementById('sample_1').outerHTML;
    var selectTo = document.getElementById('toPeriodId');
    var selectFr = document.getElementById('fromPeriodId');
    var selectedToFilename = selectTo.options[selectTo.selectedIndex].text;
    var selectedFrFilename = selectFr.options[selectFr.selectedIndex].text;
    var filename = selectedFrFilename + '_' + selectedToFilename;

    Swal.fire({
        title: "Recipient's Email",
        input: "text",
        inputLabel: "Please enter the email address where the PDF will be sent:",
        inputPlaceholder: "someone@email.com",
        showCancelButton: true,
        confirmButtonText: 'Send PDF',
        cancelButtonText: 'Cancel',
        allowEnterKey: false,
        allowOutsideClick: false,
        inputAttributes: {
            type: 'email',
            autocapitalize: 'off',
            autocorrect: 'off',
            autocomplete: 'email'
        },
        didOpen: () => {
            setTimeout(() => {
                const input = Swal.getInput();
                const confirmButton = Swal.getConfirmButton();

                if (input) {
                    // Prevent Enter key from submitting
                    input.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' || e.keyCode === 13) {
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            return false;
                        }
                    }, true);

                    // Disable confirm button until user manually clicks it
                    if (confirmButton) {
                        confirmButton.disabled = true;
                        input.addEventListener('input', function() {
                            confirmButton.disabled = false;
                        });
                        // Re-enable after a short delay to allow typing
                        setTimeout(() => {
                            if (confirmButton) confirmButton.disabled = false;
                        }, 500);
                    }

                    input.focus();
                }
            }, 100);
        },
        preConfirm: (value) => {
            if (!value || !value.trim()) {
                Swal.showValidationMessage('You need to enter an email address!');
                return false;
            }
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value.trim())) {
                Swal.showValidationMessage('Please enter a valid email address!');
                return false;
            }
            return value.trim();
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            showBlockingLoader("Exporting PDF...");
            $.ajax({
                url: 'export_pdf_formatted.php',
                type: 'POST',
                data: {
                    html: table,
                    email: result.value,
                    filename: filename
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(data) {
                    hideBlockingLoader();
                    var a = document.createElement('a');
                    var url = window.URL.createObjectURL(data);
                    a.href = url;
                    a.download = filename + '.pdf';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    a.remove();
                    Swal.fire('Exported!', 'PDF file exported successfully.', 'success');
                },
                error: function() {
                    hideBlockingLoader();
                    Swal.fire('Failed', 'Failed to export table as PDF.', 'error');
                }
            });
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
    var filename = selectedFrFilename + '_' + selectedToFilename;
    Swal.fire({
        title: "Recipient's Email",
        input: "text",
        inputLabel: "Please enter the email address where the Excel file will be sent:",
        inputPlaceholder: "someone@email.com",
        showCancelButton: true,
        confirmButtonText: 'Send Excel',
        cancelButtonText: 'Cancel',
        allowEnterKey: false,
        allowOutsideClick: false,
        inputAttributes: {
            type: 'email',
            autocapitalize: 'off',
            autocorrect: 'off',
            autocomplete: 'email'
        },
        didOpen: () => {
            setTimeout(() => {
                const input = Swal.getInput();
                const confirmButton = Swal.getConfirmButton();

                if (input) {
                    // Prevent Enter key from submitting
                    input.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' || e.keyCode === 13) {
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            return false;
                        }
                    }, true);

                    // Disable confirm button until user manually clicks it
                    if (confirmButton) {
                        confirmButton.disabled = true;
                        input.addEventListener('input', function() {
                            confirmButton.disabled = false;
                        });
                        // Re-enable after a short delay to allow typing
                        setTimeout(() => {
                            if (confirmButton) confirmButton.disabled = false;
                        }, 500);
                    }

                    input.focus();
                }
            }, 100);
        },
        preConfirm: (value) => {
            if (!value || !value.trim()) {
                Swal.showValidationMessage('You need to enter an email address!');
                return false;
            }
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value.trim())) {
                Swal.showValidationMessage('Please enter a valid email address!');
                return false;
            }
            return value.trim();
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            showBlockingLoader("Exporting Excel...");
            $.ajax({
                url: 'export_excel_formatted.php',
                type: 'POST',
                data: {
                    html: table,
                    email: result.value,
                    filename: filename
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(data) {
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
                error: function() {
                    hideBlockingLoader();
                    Swal.fire('Failed', 'Failed to export table as Excel.', 'error');
                }
            });
        }
    });
});

// Multi-select member functionality
let selectedMembersList = [];

function addMember(memberId, memberName) {
    // Check if member is already selected
    if (selectedMembersList.find(member => member.id === memberId)) {
        return;
    }

    // Add member to list
    selectedMembersList.push({
        id: memberId,
        name: memberName
    });
    updateSelectedMembersDisplay();
    updateHiddenField();
    $('#memberSearch').val(''); // Clear search input
}

function removeMember(memberId) {
    // Convert to string for comparison since onclick passes string
    selectedMembersList = selectedMembersList.filter(member => member.id != memberId);
    updateSelectedMembersDisplay();
    updateHiddenField();
}

function updateSelectedMembersDisplay() {
    const displayDiv = $('#selectedMembersDisplay');

    if (selectedMembersList.length === 0) {
        displayDiv.html('<span class="text-gray-500 text-sm">No members selected</span>');
        return;
    }

    let html = '';
    selectedMembersList.forEach(member => {
        html += `
            <div class="inline-flex items-center gap-1 bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                <span>${member.name} (${member.id})</span>
                <button type="button" onclick="removeMember('${member.id}')" class="text-blue-600 hover:text-red-600 ml-1">
                    ×
                </button>
            </div>
        `;
    });

    displayDiv.html(html);
}

function updateHiddenField() {
    const memberIds = selectedMembersList.map(member => member.id).join(',');
    $('#selectedMembers').val(memberIds);
}

function clearAllMembers() {
    selectedMembersList = [];
    updateSelectedMembersDisplay();
    updateHiddenField();
    $('#memberSearch').val('');
}

// AUTOCOMPLETE AND PERIOD SELECT
$(function() {
    $("#memberSearch").autocomplete({
        source: "search_members.php",
        minLength: 2,
        select: function(event, ui) {
            addMember(ui.item.value, ui.item.membername);
            return false;
        }
    });

    $("#fromPeriodId").on('change', function() {
        $("#toPeriodId").val($(this).val());
    });

    // Clear all members button
    $('#clearAllMembersBtn').on('click', function() {
        clearAllMembers();
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

/* Prevent horizontal scroll caused by buttons on mobile */
@media (max-width: 767px) {
    #status {
        overflow-x: auto;
        overflow-y: visible;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }

    /* Target the buttons container (first div inside #status) */
    #status>div:first-of-type {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        overflow: hidden !important;
    }

    /* Ensure buttons don't cause overflow */
    #status>div:first-of-type button {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        min-width: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    /* Ensure the table wrapper can still scroll horizontally */
    #status>div:last-of-type,
    #status>div.overflow-x-auto {
        overflow-x: auto !important;
        width: 100%;
        max-width: 100%;
    }
}

/* Ensure parent containers don't cause overflow */
@media (max-width: 767px) {
    .max-w-6xl {
        width: 100% !important;
        max-width: 100% !important;
        padding-left: 0.5rem !important;
        padding-right: 0.5rem !important;
        box-sizing: border-box !important;
    }

    main.flex-1 {
        padding-left: 0.5rem !important;
        padding-right: 0.5rem !important;
        overflow-x: hidden !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
}
</style>