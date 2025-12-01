<?php
session_start();
if (!isset($_SESSION['UserID'])) {
    header("Location:index.php");
    exit;
}

$eventId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$eventId) {
    header("Location:event-management.php");
    exit;
}

require_once('header.php');
require_once('Connections/cov.php');
require_once('config/EnvConfig.php');

// Get Google Maps API key with fallback
if (method_exists('EnvConfig', 'getGoogleMapsApiKey')) {
    $googleMapsApiKey = EnvConfig::getGoogleMapsApiKey();
} else {
    // Fallback: Read directly from config.env
    $configFile = __DIR__ . '/config.env';
    $googleMapsApiKey = '';
    if (file_exists($configFile)) {
        $lines = file($configFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, 'GOOGLE_MAPS_API_KEY=') === 0) {
                $googleMapsApiKey = trim(substr($line, strlen('GOOGLE_MAPS_API_KEY=')));
                // Remove quotes if present
                if ((substr($googleMapsApiKey, 0, 1) === '"' && substr($googleMapsApiKey, -1) === '"') ||
                    (substr($googleMapsApiKey, 0, 1) === "'" && substr($googleMapsApiKey, -1) === "'")) {
                    $googleMapsApiKey = substr($googleMapsApiKey, 1, -1);
                }
                break;
            }
        }
    }
}
?>
<div class="flex min-h-screen">
    <main class="flex-1 py-8 px-2 md:px-10 bg-gray-50">
        <div class="max-w-6xl mx-auto">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-xl sm:text-2xl font-bold text-blue-900">Event Details</h1>
                <a href="event-management.php" class="text-blue-600 hover:underline">
                    <i class="fas fa-arrow-left mr-1"></i>Back to Events
                </a>
            </div>

            <!-- Loader -->
            <div id="wait" style="display:none;" class="mb-2">
                <div class="flex items-center gap-2">
                    <img src="images/pageloading.gif" class="h-6 w-6"> <span>Please wait...</span>
                </div>
            </div>

            <!-- Event Details -->
            <div id="eventDetails" class="rounded shadow bg-white p-4 mb-4">
                <p class="text-gray-500 text-center py-8">Loading event details...</p>
            </div>

            <!-- Map -->
            <div id="mapContainer" class="hidden rounded shadow bg-white p-4 mb-4">
                <h2 class="text-lg font-bold mb-2">Event Location</h2>
                <div id="map" style="height: 400px; width: 100%;"></div>
            </div>

            <!-- Attendance List -->
            <div id="attendanceList" class="rounded shadow bg-white p-4">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold">Attendance List</h2>
                    <div class="flex gap-2">
                        <button onclick="exportAttendance()"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">
                            <i class="fas fa-file-excel mr-1"></i>Export to Excel
                        </button>
                        <button onclick="showManualCheckInModal()"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                            <i class="fas fa-user-plus mr-1"></i>Manual Check-in
                        </button>
                    </div>
                </div>
                <div id="attendanceContent" class="overflow-x-auto">
                    <p class="text-gray-500 text-center py-8">Loading attendance...</p>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Manual Check-in Modal -->
<div id="manualCheckInModal"
    class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
        <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center">
            <h2 class="text-xl font-bold text-blue-900">Manual Check-in</h2>
            <button onclick="closeManualCheckInModal()"
                class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        <form id="manualCheckInForm" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search Member <span
                        class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="text" id="manualMemberSearch" autocomplete="off"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Search by name or member ID...">
                    <button type="button" id="clearMemberSearch" onclick="clearMemberSearch()"
                        class="hidden absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <input type="hidden" id="manualUserCoopId">
                <div id="memberSearchResults"
                    class="hidden absolute z-10 bg-white border rounded shadow-lg mt-1 w-full max-h-60 overflow-y-auto">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Device ID (Optional)</label>
                <input type="text" id="manualDeviceId"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Leave empty to auto-generate">
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="skipLocationCheck" class="mr-2">
                <label for="skipLocationCheck" class="text-sm text-gray-700">Skip location validation</label>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="closeManualCheckInModal()"
                    class="px-4 py-2 border rounded hover:bg-gray-100">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Check
                    In</button>
            </div>
        </form>
    </div>
</div>

<?php require_once('footer.php'); ?>

<script src="https://maps.googleapis.com/maps/api/js?key=<?= htmlspecialchars($googleMapsApiKey) ?>&libraries=places">
</script>
<script>
let eventMap;
let eventMarker;
let eventCircle;
let currentEventId = <?= $eventId ?>;
let memberSearchTimeout;

function showBlockingLoader(msg = 'Loading...') {
    Swal.fire({
        title: '<div class="flex flex-col items-center gap-4"><svg class="animate-spin h-10 w-10 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg><span class="mt-2 text-blue-800 font-semibold">' +
            msg + '</span></div>',
        html: '',
        allowOutsideClick: false,
        showConfirmButton: false,
        backdrop: true
    });
}

function hideBlockingLoader() {
    Swal.close();
}

function loadEventDetails() {
    showBlockingLoader('Loading event details...');
    $('#wait').show();

    $.get('api/admin/events.php?id=' + currentEventId, function(response) {
        hideBlockingLoader();
        $('#wait').hide();

        if (response.success) {
            displayEventDetails(response.data);
            displayAttendance(response.data);
            initializeMap(response.data);
        } else {
            $('#eventDetails').html('<p class="text-red-500 text-center py-8">' + response.message + '</p>');
        }
    }).fail(function() {
        hideBlockingLoader();
        $('#wait').hide();
        $('#eventDetails').html('<p class="text-red-500 text-center py-8">Failed to load event details</p>');
    });
}

function displayEventDetails(event) {
    const statusClass = event.status === 'active' ? 'bg-green-100 text-green-800' :
        event.status === 'upcoming' ? 'bg-blue-100 text-blue-800' :
        'bg-gray-100 text-gray-800';

    let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
    html += '<div><h2 class="text-2xl font-bold mb-2">' + escapeHtml(event.title) + '</h2>';
    if (event.description) {
        html += '<p class="text-gray-600 mb-4">' + escapeHtml(event.description) + '</p>';
    }
    html += '</div>';
    html += '<div class="text-right">';
    html += '<span class="px-3 py-1 rounded text-sm ' + statusClass + '">' + (event.status || 'UNKNOWN').toUpperCase() +
        '</span>';
    html += '</div></div>';

    html += '<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 pt-4 border-t">';
    html += '<div><label class="text-sm text-gray-600">Start Time</label><p class="font-semibold">' + formatDateTime(
        event.start_time) + '</p></div>';
    html += '<div><label class="text-sm text-gray-600">End Time</label><p class="font-semibold">' + formatDateTime(event
        .end_time) + '</p></div>';
    html += '<div><label class="text-sm text-gray-600">Grace Period</label><p class="font-semibold">' + event
        .grace_period_minutes + ' minutes</p></div>';
    html += '<div><label class="text-sm text-gray-600">Location</label><p class="font-semibold">' + event.location_lat +
        ', ' + event.location_lng + '</p></div>';
    html += '<div><label class="text-sm text-gray-600">Geofence Radius</label><p class="font-semibold">' + event
        .geofence_radius + ' meters</p></div>';
    html += '<div><label class="text-sm text-gray-600">Attendance</label><p class="font-semibold">' + (event
        .attendance_count || 0) + ' attendees</p></div>';
    html += '</div>';

    $('#eventDetails').html(html);
}

function initializeMap(event) {
    if (!event.location_lat || !event.location_lng) return;

    const center = {
        lat: parseFloat(event.location_lat),
        lng: parseFloat(event.location_lng)
    };

    eventMap = new google.maps.Map(document.getElementById('map'), {
        center: center,
        zoom: 15
    });

    eventMarker = new google.maps.Marker({
        position: center,
        map: eventMap,
        title: event.title
    });

    eventCircle = new google.maps.Circle({
        center: center,
        radius: event.geofence_radius || 50,
        fillColor: '#4285F4',
        fillOpacity: 0.2,
        strokeColor: '#4285F4',
        strokeOpacity: 0.8,
        strokeWeight: 2,
        map: eventMap
    });

    $('#mapContainer').removeClass('hidden');
}

function displayAttendance(event) {
    if (!event.attendance || event.attendance.length === 0) {
        $('#attendanceContent').html('<p class="text-gray-500 text-center py-8">No attendance records yet</p>');
        return;
    }

    let html = '<table class="min-w-full divide-y divide-gray-200"><thead class="bg-blue-500 text-white"><tr>';
    html += '<th class="px-4 py-2 text-left">Member Name</th>';
    html += '<th class="px-4 py-2 text-left">Member ID</th>';
    html += '<th class="px-4 py-2 text-left">Check-in Time</th>';
    html += '<th class="px-4 py-2 text-left">Distance (m)</th>';
    html += '<th class="px-4 py-2 text-left">Device ID</th>';
    html += '<th class="px-4 py-2 text-left">Status</th>';
    html += '<th class="px-4 py-2 text-left">Actions</th>';
    html += '</tr></thead><tbody>';

    event.attendance.forEach(att => {
        html += '<tr class="hover:bg-gray-50">';
        html += '<td class="px-4 py-2">' + escapeHtml(att.member_name);
        if (att.admin_override) {
            html += ' <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs">Admin</span>';
        }
        html += '</td>';
        html += '<td class="px-4 py-2">' + att.user_coop_id + '</td>';
        html += '<td class="px-4 py-2">' + formatDateTime(att.check_in_time) + '</td>';
        html += '<td class="px-4 py-2">' + parseFloat(att.distance_from_event).toFixed(2) + '</td>';
        html += '<td class="px-4 py-2"><span class="font-mono text-xs">' + escapeHtml(att.device_id || 'N/A');
        if (att.device_id) {
            html += ' <button onclick="resetDeviceLock(\'' + escapeHtml(att.device_id) +
                '\')" class="ml-2 text-red-600 hover:underline" title="Reset device lock">';
            html += '<i class="fas fa-unlock"></i></button>';
        }
        html += '</span></td>';
        html += '<td class="px-4 py-2"><span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">' +
            att.status + '</span></td>';
        html += '<td class="px-4 py-2"></td>';
        html += '</tr>';
    });

    html += '</tbody></table>';
    $('#attendanceContent').html(html);
}

function formatDateTime(datetime) {
    const d = new Date(datetime);
    return d.toLocaleString();
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

function exportAttendance() {
    window.location.href = 'api/admin/export-attendance.php?event_id=' + currentEventId;
}

function showManualCheckInModal() {
    $('#manualCheckInForm')[0].reset();
    $('#manualUserCoopId').val('');
    $('#memberSearchResults').addClass('hidden').html('');
    $('#clearMemberSearch').addClass('hidden');
    $('#manualCheckInModal').removeClass('hidden');
}

function closeManualCheckInModal() {
    $('#manualCheckInModal').addClass('hidden');
}

function clearMemberSearch() {
    $('#manualMemberSearch').val('');
    $('#manualUserCoopId').val('');
    $('#memberSearchResults').addClass('hidden').html('');
    $('#clearMemberSearch').addClass('hidden');
}

$('#manualMemberSearch').on('input', function() {
    const query = $(this).val().trim();

    if (query.length < 2) {
        $('#memberSearchResults').addClass('hidden').html('');
        $('#clearMemberSearch').addClass('hidden');
        return;
    }

    $('#clearMemberSearch').removeClass('hidden');

    clearTimeout(memberSearchTimeout);
    memberSearchTimeout = setTimeout(() => {
        searchMembers(query);
    }, 300);
});

function searchMembers(query) {
    $.get('api/admin/search-members.php?q=' + encodeURIComponent(query), function(response) {
        if (response.success && response.data.length > 0) {
            let html = '';
            response.data.forEach(member => {
                html += '<div class="px-4 py-2 hover:bg-blue-50 cursor-pointer member-result-item"';
                html += ' data-coop-id="' + member.memberid + '" data-name="' + escapeHtml(member
                    .full_name) + '">';
                html += '<div class="font-medium">' + escapeHtml(member.full_name) + '</div>';
                html += '<div class="text-sm text-gray-500">Member ID: ' + member.memberid + '</div>';
                html += '</div>';
            });
            $('#memberSearchResults').html(html).removeClass('hidden');
        } else {
            $('#memberSearchResults').html('<div class="px-4 py-2 text-gray-500">No members found</div>')
                .removeClass('hidden');
        }
    });
}

$(document).on('click', '.member-result-item', function() {
    const coopId = $(this).data('coop-id');
    const name = $(this).data('name');

    $('#manualMemberSearch').val(name);
    $('#manualUserCoopId').val(coopId);
    $('#memberSearchResults').addClass('hidden');
});

$('#manualCheckInForm').on('submit', function(e) {
    e.preventDefault();

    const userCoopId = $('#manualUserCoopId').val();
    if (!userCoopId) {
        Swal.fire('Error', 'Please search and select a member', 'error');
        return;
    }

    const checkInData = {
        event_id: currentEventId,
        user_coop_id: parseInt(userCoopId),
        device_id: $('#manualDeviceId').val().trim() || undefined,
        skip_location_check: $('#skipLocationCheck').is(':checked')
    };

    showBlockingLoader('Checking in member...');

    $.ajax({
        url: 'api/admin/manual-checkin.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(checkInData),
        success: function(response) {
            hideBlockingLoader();
            if (response.success) {
                Swal.fire('Success!', response.message, 'success');
                closeManualCheckInModal();
                loadEventDetails();
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function(xhr) {
            hideBlockingLoader();
            const response = xhr.responseJSON || {};
            Swal.fire('Error', response.message || 'Failed to check in member', 'error');
        }
    });
});

function resetDeviceLock(deviceId) {
    Swal.fire({
        title: 'Reset Device Lock?',
        text: 'This will remove check-in records for this device, allowing it to be used by another member.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, reset it!'
    }).then((result) => {
        if (result.isConfirmed) {
            showBlockingLoader('Resetting device lock...');

            $.ajax({
                url: 'api/admin/reset-device-lock.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    event_id: currentEventId,
                    device_id: deviceId
                }),
                success: function(response) {
                    hideBlockingLoader();
                    if (response.success) {
                        Swal.fire('Success!', response.message, 'success');
                        loadEventDetails();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    hideBlockingLoader();
                    Swal.fire('Error', 'Failed to reset device lock', 'error');
                }
            });
        }
    });
}

// Load event details on page load
$(document).ready(function() {
    loadEventDetails();
});
</script>