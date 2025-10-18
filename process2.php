<?php include('header.php'); ?>
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-10 px-4">
    <div class="w-ful">
        <div class="bg-white shadow-xl rounded-2xl p-8">
            <h2 class="text-2xl font-bold text-blue-700 mb-6">
                Process Deductions
            </h2>
            <form class="space-y-5" method="POST" name="eduEntry" id="deductionForm" autocomplete="off">
                <div>
                    <label class="block font-semibold text-gray-700 mb-2">Select Periods to Process</label>
                    <div class="flex gap-2">
                        <select id="PeriodSelector" 
                            class="flex-1 rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 transition">
                            <option value="">Loading periods...</option>
                        </select>
                        <button type="button" id="addPeriodBtn" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition">
                            Add Period
                        </button>
                        <button type="button" id="clearAllPeriodsBtn" 
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                            Clear All
                        </button>
                    </div>
                    <input type="hidden" id="selectedPeriods" name="selectedPeriods">
                    
                    <!-- Selected Periods Display -->
                    <div id="selectedPeriodsDisplay" class="mt-3 flex flex-wrap gap-2 min-h-[40px] p-3 border-2 border-gray-200 rounded-lg bg-gray-50">
                        <span class="text-gray-500 text-sm">No periods selected</span>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center space-x-2">
                        <input id="sms" name="sms" type="checkbox" value="1"
                            class="rounded text-blue-600 focus:ring-2 focus:ring-blue-400" checked>
                        <label for="sms" class="text-sm text-gray-700 select-none flex items-center">
                            <i class="fa fa-sms mr-2"></i>Send SMS Notifications
                        </label>
                    </div>
                    <div class="flex items-center space-x-2">
                        <input id="email" name="email" type="checkbox" value="1"
                            class="rounded text-green-600 focus:ring-2 focus:ring-green-400" checked>
                        <label for="email" class="text-sm text-gray-700 select-none flex items-center">
                            <i class="fa fa-envelope mr-2"></i>Send Email Notifications
                        </label>
                    </div>
                </div>
                <button id="processBtn" type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded-lg shadow transition">
                    Process Transaction
                </button>
            </form>

            <div id="progressArea" class="mt-6">
                <div class="w-full h-4 bg-gray-200 rounded-full">
                    <div id="progressFill" class="h-4 bg-blue-600 rounded-full transition-all" style="width:0%"></div>
                </div>
                <div id="progressText" class="mt-2 text-sm text-gray-700"></div>
            </div>




            <div id="statusArea" class="mt-6">
                <div id="wait" class="hidden flex items-center space-x-2 text-blue-600">
                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                    </svg>
                    <span>Loading data...</span>
                </div>
                <div class="mt-4 w-full overflow-x-auto rounded-lg border bg-gray-50" style="min-height:48px">
                    <div id="contributionResult" class="min-w-[350px]"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert and jQuery CDN if not already included -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Multi-select period functionality
let selectedPeriodsList = [];
let allPeriodsData = [];

function addPeriod(periodId, periodName) {
    // Check if period is already selected
    if (selectedPeriodsList.find(period => period.id == periodId)) {
        Swal.fire({
            icon: 'info',
            title: 'Already Added',
            text: 'This period is already in the list.',
            timer: 2000,
            showConfirmButton: false
        });
        return;
    }
    
    // Add period to list
    selectedPeriodsList.push({id: periodId, name: periodName});
    updateSelectedPeriodsDisplay();
    updateHiddenPeriodField();
    
    // Reset selector to default
    $('#PeriodSelector').val('');
}

function removePeriod(periodId) {
    selectedPeriodsList = selectedPeriodsList.filter(period => period.id != periodId);
    updateSelectedPeriodsDisplay();
    updateHiddenPeriodField();
}

function updateSelectedPeriodsDisplay() {
    const displayDiv = $('#selectedPeriodsDisplay');
    
    if (selectedPeriodsList.length === 0) {
        displayDiv.html('<span class="text-gray-500 text-sm">No periods selected</span>');
        return;
    }
    
    let html = '';
    selectedPeriodsList.forEach(period => {
        html += `
            <div class="inline-flex items-center gap-2 bg-indigo-100 text-indigo-800 px-4 py-2 rounded-full text-sm font-medium shadow-sm">
                <i class="fa fa-calendar-alt"></i>
                <span>${period.name}</span>
                <button type="button" onclick="removePeriod('${period.id}')" class="text-indigo-600 hover:text-red-600 ml-1 font-bold text-lg">
                    ×
                </button>
            </div>
        `;
    });
    
    displayDiv.html(html);
}

function updateHiddenPeriodField() {
    const periodIds = selectedPeriodsList.map(period => period.id).join(',');
    $('#selectedPeriods').val(periodIds);
}

function clearAllPeriods() {
    selectedPeriodsList = [];
    updateSelectedPeriodsDisplay();
    updateHiddenPeriodField();
}

document.addEventListener("DOMContentLoaded", function() {
    fetch('api/periods.php')
        .then(response => response.json())
        .then(data => {
            allPeriodsData = data;
            const select = document.getElementById('PeriodSelector');
            select.innerHTML = '<option value="">Select a period to add...</option>';
            data.forEach(row => {
                const option = document.createElement('option');
                option.value = row.Periodid;
                option.textContent = row.PayrollPeriod;
                select.appendChild(option);
            });
        })
        .catch(() => {
            document.getElementById('PeriodSelector').innerHTML =
                '<option value="">Unable to load periods</option>';
        });
    
    // Add Period button click handler
    $('#addPeriodBtn').on('click', function() {
        const periodId = $('#PeriodSelector').val();
        if (!periodId || periodId === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Select Period',
                text: 'Please select a period from the dropdown first.',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }
        
        const selectedOption = $('#PeriodSelector option:selected');
        const periodName = selectedOption.text();
        addPeriod(periodId, periodName);
    });
    
    // Clear All button click handler
    $('#clearAllPeriodsBtn').on('click', function() {
        clearAllPeriods();
    });
});
const sessionId = '<?php echo session_id(); ?>';

function pollProgress(sessionId) {
    $.getJSON('progress_' + sessionId + '.json')
        .done(function(progress) {
            // This ensures percent is always a string with %
            var percent = progress.percent;
            if (!percent.endsWith('%')) percent += '%';

            // Animate for smoother UI
            $('#progressFill').css('width', percent);

            // Update text
            $('#progressText').text(progress.message + ' (' + progress.current + '/' + progress.total + ')');

            // Continue polling if not done
            if (!progress.done && progress.current < progress.total) {
                setTimeout(function() {
                    pollProgress(sessionId);
                }, 1000);
            } else {
                $('#progressText').text('Processing Complete!');
                $('#progressFill').css('width', '100%');
            }
        })
        .fail(function() {
            // Show loading or try again if file not ready yet
            $('#progressText').text('Waiting for progress...');
            setTimeout(function() {
                pollProgress(sessionId);
            }, 1000);
        });
}


$(function() {
    $('#deductionForm').on('submit', function(event) {
        event.preventDefault();
        const selectedPeriods = $('#selectedPeriods').val();
        const sms = $('#sms').is(':checked') ? 1 : 0;
        const email = $('#email').is(':checked') ? 1 : 0;

        if (!selectedPeriods || selectedPeriods === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Select Periods',
                text: 'Please select at least one period before processing.'
            });
            return false;
        }

        const periodIds = selectedPeriods.split(',');
        const periodCount = periodIds.length;
        
        // Build notification message
        let notifMsg = `This will process transactions for <strong>${periodCount} period(s)</strong>.<br>`;
        notifMsg += '<div class="mt-2 text-left"><strong>Selected Periods:</strong><ul class="list-disc ml-5">';
        selectedPeriodsList.forEach(period => {
            notifMsg += `<li>${period.name}</li>`;
        });
        notifMsg += '</ul></div>';
        
        if (sms && email) {
            notifMsg += '<br><strong>SMS and Email notifications will be sent.</strong>';
        } else if (sms) {
            notifMsg += '<br><strong>SMS notifications will be sent.</strong>';
        } else if (email) {
            notifMsg += '<br><strong>Email notifications will be queued.</strong>';
        }

        Swal.fire({
            title: 'Are you sure?',
            html: notifMsg,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Process All!'
        }).then((result) => {
            if (result.isConfirmed) {
                processMultiplePeriods(periodIds, sms, email, 0);
            }
        });
        return false;
    });
});

// Process multiple periods sequentially
let currentPeriodIndex = 0;
let totalPeriods = 0;
let processResults = [];

function processMultiplePeriods(periodIds, sms, email, index) {
    if (index === 0) {
        currentPeriodIndex = 0;
        totalPeriods = periodIds.length;
        processResults = [];
        
        // Show overall progress dialog
        Swal.fire({
            title: 'Processing Multiple Periods',
            html: `
                <div class="text-left">
                    <div class="mb-4">
                        <strong>Overall Progress:</strong>
                        <div class="w-full bg-gray-200 rounded-full h-6 mt-2">
                            <div id="overallProgress" class="bg-blue-600 h-6 rounded-full text-white text-xs flex items-center justify-center" style="width: 0%">0%</div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <strong>Current Period:</strong> <span id="currentPeriodName">-</span>
                    </div>
                    <div id="periodProgress"></div>
                    <div class="mt-4 max-h-40 overflow-y-auto">
                        <strong>Results:</strong>
                        <div id="resultsLog" class="text-xs mt-2 space-y-1"></div>
                    </div>
                </div>
            `,
            allowOutsideClick: false,
            showConfirmButton: false,
            width: '600px'
        });
    }
    
    if (index >= periodIds.length) {
        // All periods processed
        showFinalResults();
        return;
    }
    
    const periodId = periodIds[index];
    const periodData = selectedPeriodsList.find(p => p.id == periodId);
    const periodName = periodData ? periodData.name : `Period ${periodId}`;
    
    // Update current period display
    $('#currentPeriodName').text(periodName);
    
    // Update overall progress
    const overallPercent = Math.round((index / totalPeriods) * 100);
    $('#overallProgress').css('width', overallPercent + '%').text(overallPercent + '%');
    
    // Add to results log
    $('#resultsLog').append(`<div class="text-blue-600"><i class="fa fa-spinner fa-spin"></i> Processing ${periodName}...</div>`);
    
    // Process this period
    $.get('process.php', {
        PeriodID: periodId,
        sms: sms,
        email: email
    })
    .done(function(data) {
        processResults.push({
            period: periodName,
            success: true,
            message: 'Completed successfully'
        });
        
        // Update log
        $('#resultsLog div:last').html(`<div class="text-green-600"><i class="fa fa-check-circle"></i> ${periodName} - Completed</div>`);
        
        // Move to next period after a short delay
        setTimeout(function() {
            processMultiplePeriods(periodIds, sms, email, index + 1);
        }, 1000);
    })
    .fail(function(xhr, status, error) {
        processResults.push({
            period: periodName,
            success: false,
            message: error || 'Processing failed'
        });
        
        // Update log
        $('#resultsLog div:last').html(`<div class="text-red-600"><i class="fa fa-times-circle"></i> ${periodName} - Failed: ${error}</div>`);
        
        // Continue with next period even if this one failed
        setTimeout(function() {
            processMultiplePeriods(periodIds, sms, email, index + 1);
        }, 1000);
    });
}

function showFinalResults() {
    const successCount = processResults.filter(r => r.success).length;
    const failCount = processResults.filter(r => !r.success).length;
    
    let resultsHtml = '<div class="text-left">';
    resultsHtml += `<div class="mb-4"><strong>Summary:</strong></div>`;
    resultsHtml += `<div class="mb-2">✅ Successful: ${successCount}</div>`;
    resultsHtml += `<div class="mb-4">❌ Failed: ${failCount}</div>`;
    resultsHtml += '<div class="max-h-60 overflow-y-auto"><strong>Details:</strong><ul class="mt-2 space-y-1">';
    
    processResults.forEach(result => {
        const icon = result.success ? '✅' : '❌';
        const color = result.success ? 'text-green-600' : 'text-red-600';
        resultsHtml += `<li class="${color}">${icon} ${result.period} - ${result.message}</li>`;
    });
    
    resultsHtml += '</ul></div></div>';
    
    Swal.fire({
        title: 'Processing Complete!',
        html: resultsHtml,
        icon: successCount === totalPeriods ? 'success' : (successCount > 0 ? 'warning' : 'error'),
        confirmButtonText: 'OK',
        width: '600px'
    }).then(() => {
        // Optionally reload the page or reset the form
        clearAllPeriods();
    });
}


// Removed old single-period contribution display - not compatible with multi-period selection

// Modern jQuery submit event and SweetAlert
$(function() {
    // $('#deductionForm').on('submit', function(event) {
    //   event.preventDefault();
    //   const periodid = $('#PeriodId').val();
    //   const sms = $('#sms').is(':checked') ? 1 : 0;

    //   if (periodid === 'na') {
    //     Swal.fire({
    //       icon: 'warning',
    //       title: 'Select a Period',
    //       text: 'Please select a period before processing.'
    //     });
    //     return false;
    //   }

    //   Swal.fire({
    //     title: 'Are you sure?',
    //     text: 'This will process transactions for the selected period.',
    //     icon: 'question',
    //     showCancelButton: true,
    //     confirmButtonColor: '#3085d6',
    //     cancelButtonColor: '#d33',
    //     confirmButtonText: 'Yes, Process!'
    //   }).then((result) => {
    //     if (result.isConfirmed) {
    //       window.location.href = 'process.php?PeriodID=' + encodeURIComponent(periodid) + '&sms=' + sms;
    //     }
    //   });
    //   return false;
    // });
});
</script>
<?php include('footer.php'); ?>