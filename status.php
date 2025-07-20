<?php require_once('header.php'); ?>
<?php
require_once('Connections/cov.php');
if (!isset($_SESSION['UserID'])) {
    header("Location:index.php");
    exit;
}
// Fetch periods for dropdown
$periods = [];
$res = $cov->query("SELECT Periodid, PayrollPeriod FROM tbpayrollperiods ORDER BY Periodid DESC");
if ($res) $periods = $res->fetch_all(MYSQLI_ASSOC);
?>

<!-- Container (no <main>, since header.php already opens it) -->
<div class="max-w-xl mx-auto w-full">
  <form id="member-status-form" class="w-full bg-white rounded-2xl shadow-xl p-8 space-y-6 mt-4" autocomplete="off" novalidate>
    <h2 class="text-2xl font-extrabold text-blue-900 mb-6 text-center tracking-tight">Check Member Status</h2>

    <div>
      <label for="search" class="block text-sm font-medium text-blue-800 mb-1">Search Member</label>
      <div class="flex items-center space-x-2">
        <input type="text" id="search"
          class="block w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 px-3 py-2 text-base"
          placeholder="Type name or ID..." aria-label="Search member" autocomplete="off">
        <button type="button" id="clearMemberBtn" title="Clear member" class="text-gray-500 hover:text-red-600 text-xl px-2">&#10006;</button>
      </div>
      <input type="hidden" id="memberid" name="memberid">
      <input type="hidden" id="name" name="name">
    </div>

    <div>
      <label for="PeriodId" class="block text-sm font-medium text-blue-800 mb-1">As At Period</label>
      <select id="PeriodId" name="PeriodId"
        class="block w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 px-3 py-2 text-base">
        <option value="">Select Period</option>
        <?php foreach ($periods as $p): ?>
          <option value="<?= $p['Periodid'] ?>"><?= htmlspecialchars($p['PayrollPeriod']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <button id="getResult" type="submit"
      class="w-full flex justify-center items-center gap-2 bg-blue-600 text-white font-semibold py-2 rounded-lg hover:bg-blue-700 transition focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 text-lg">
      <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"
        viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
      </svg>
      Get Status
    </button>

    <div id="status" class="mt-6"></div>
  </form>
</div>

<script>
$(function() {
    // Autocomplete
    $("#search").autocomplete({
        source: "search_members.php",
        minLength: 2,
        select: function(event, ui) {
            $('#memberid').val(ui.item.value);
            $('#name').val(ui.item.membername);
            $("#search").val(ui.item.membername);
            setTimeout(function() { $('#PeriodId').focus(); }, 150);
            return false;
        }
    });

    // Clear member button
    $('#clearMemberBtn').on('click', function() {
        $('#search').val('');
        $('#memberid').val('');
        $('#name').val('');
        $('#search').focus();
    });

    // Submit handler
    $("#member-status-form").on('submit', function(e) {
        e.preventDefault();
        let memberid = $('#memberid').val().trim();
        let periodid = $('#PeriodId').val();

        if (!memberid) {
            Swal.fire('Select a member from the list.', '', 'warning');
            $('#search').focus();
            return;
        }
        if (!periodid) {
            Swal.fire('Select a period.', '', 'warning');
            $('#PeriodId').focus();
            return;
        }

        // SweetAlert2 loader
        Swal.fire({
            title: '<span class="text-blue-800">Checking status...</span>',
            html: '<div class="flex justify-center my-2"><svg class="animate-spin h-8 w-8 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg></div>',
            allowOutsideClick: false,
            showConfirmButton: false,
            allowEscapeKey: false,
        });

        // AJAX request
        $.ajax({
            url: "getStatus.php",
            type: "GET",
            data: { id: memberid, period: periodid },
            success: function(response) {
                Swal.close();
                $('#status').hide().html(response).fadeIn(180);
            },
            error: function(xhr) {
                Swal.close();
                Swal.fire('Error', 'Could not fetch status. Try again.', 'error');
            }
        });
    });
});
</script>

<?php require_once('footer.php'); ?>
