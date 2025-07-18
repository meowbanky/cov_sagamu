<?php include('header.php'); ?>
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-10 px-4">
  <div class="w-full max-w-lg">
    <div class="bg-white shadow-xl rounded-2xl p-8">
      <h2 class="text-2xl font-bold text-blue-700 mb-6">
      Process Deductions 
      </h2>
      <form class="space-y-5" method="POST" name="eduEntry" id="deductionForm" autocomplete="off">
        <div>
          <label for="PeriodId" class="block font-semibold text-gray-700 mb-2">Select Period</label>
          <select name="PeriodId" id="PeriodId" required
            class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 transition disabled:bg-gray-100">
            <option value="na">Loading periods...</option>
          </select>
        </div>
        <div class="flex items-center space-x-2">
          <input id="sms" name="sms" type="checkbox" value="1" class="rounded text-blue-600 focus:ring-2 focus:ring-blue-400" checked>
          <label for="sms" class="text-sm text-gray-700 select-none">Send SMS/E-mail</label>
        </div>
        <button id="processBtn" type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded-lg shadow transition">
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
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
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
document.addEventListener("DOMContentLoaded", function () {
  fetch('api/periods.php')
    .then(response => response.json())
    .then(data => {
      const select = document.getElementById('PeriodId');
      select.innerHTML = '<option value="na">Select Period</option>';
      data.forEach(row => {
        const option = document.createElement('option');
        option.value = row.Periodid;
        option.textContent = row.PayrollPeriod;
        select.appendChild(option);
      });
    })
    .catch(() => {
      document.getElementById('PeriodId').innerHTML =
        '<option value="na">Unable to load periods</option>';
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
                setTimeout(function() { pollProgress(sessionId); }, 1000);
            } else {
                $('#progressText').text('Processing Complete!');
                $('#progressFill').css('width', '100%');
            }
        })
        .fail(function() {
            // Show loading or try again if file not ready yet
            $('#progressText').text('Waiting for progress...');
            setTimeout(function() { pollProgress(sessionId); }, 1000);
        });
}


$(function() {
  $('#deductionForm').on('submit', function(event) {
    event.preventDefault();
    const periodid = $('#PeriodId').val();
    const sms = $('#sms').is(':checked') ? 1 : 0;

    if (periodid === 'na') {
      Swal.fire({
        icon: 'warning',
        title: 'Select a Period',
        text: 'Please select a period before processing.'
      });
      return false;
    }

    Swal.fire({
      title: 'Are you sure?',
      text: 'This will process transactions for the selected period.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, Process!'
    }).then((result) => {
      if (result.isConfirmed) {
        // Start processing via AJAX POST (use process.php, which will run in the background)
        $.get('process.php', { PeriodID: periodid, sms: sms }, function(data) {
          // The backend should immediately return a response (or you can just ignore this if it runs long)
        });

        // Start polling for progress
        pollProgress(sessionId);

        Swal.fire({
          title: 'Processing...',
          html: '<div id="swal-progress"></div>',
          allowOutsideClick: false,
          didOpen: () => {
            $('#swal-progress').html($('#progressArea'));
          }
        });
      }
    });
    return false;
  });
});


document.addEventListener("DOMContentLoaded", function () {
  const select = document.getElementById('PeriodId');
  select.addEventListener('change', function () {
    const periodid = select.value;
    if (periodid === 'na') {
      document.getElementById('contributionResult').innerHTML = '';
      return;
    }
    const wait = document.getElementById('wait');
    const result = document.getElementById('contributionResult');
    wait.classList.remove('hidden');
    result.innerHTML = '';
    fetch(`getContributionList.php?periodid=${encodeURIComponent(periodid)}`)
      .then(r => r.text())
      .then(html => {
        wait.classList.add('hidden');
        result.innerHTML = html;
      })
      .catch(() => {
        wait.classList.add('hidden');
        result.innerHTML = `<div class="text-red-600 text-sm mt-2">Error loading contribution data.</div>`;
      });
  });
});

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
