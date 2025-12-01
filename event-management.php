<?php
session_start();
if (!isset($_SESSION['UserID'])) {
    header("Location:index.php");
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
            <h1 class="text-xl sm:text-2xl font-bold text-blue-900 mb-4 sm:mb-6">Event Management</h1>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 mb-4 items-center sm:items-end">
                <button onclick="loadEvents()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded w-full sm:w-auto">
                    <i class="fas fa-sync-alt mr-2"></i>Refresh Events
                </button>
                <button onclick="showCreateEventModal()"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded w-full sm:w-auto">
                    <i class="fas fa-plus mr-2"></i>Create Event
                </button>
            </div>

            <!-- Loader -->
            <div id="wait" style="display:none;" class="mb-2">
                <div class="flex items-center gap-2">
                    <img src="images/pageloading.gif" class="h-6 w-6"> <span>Please wait...</span>
                </div>
            </div>

            <!-- Events Table -->
            <div id="eventsDisplay" class="rounded shadow bg-white p-3 overflow-x-auto">
                <p class="text-gray-500 text-center py-8">Click "Refresh Events" to load events</p>
            </div>
        </div>
    </main>
</div>

<!-- Create/Edit Event Modal -->
<div id="eventModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center">
            <h2 class="text-xl font-bold text-blue-900" id="modalTitle">Create Event</h2>
            <button onclick="closeEventModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        <form id="eventForm" class="p-6 space-y-4">
            <input type="hidden" id="eventId">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Event Title <span
                        class="text-red-500">*</span></label>
                <input type="text" id="eventTitle" required
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="eventDescription" rows="3"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Time <span
                            class="text-red-500">*</span></label>
                    <input type="datetime-local" id="eventStartTime" required
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Time <span
                            class="text-red-500">*</span></label>
                    <input type="datetime-local" id="eventEndTime" required
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <!-- Location Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Event Location <span
                        class="text-red-500">*</span></label>
                <div class="flex gap-2 mb-2">
                    <button type="button" onclick="useCurrentLocation()"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm">
                        <i class="fas fa-map-marker-alt mr-1"></i>Use My Location
                    </button>
                    <button type="button" onclick="openMapPicker()"
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm">
                        <i class="fas fa-map mr-1"></i>Select on Map
                    </button>
                </div>
                <div id="mapContainer" class="hidden h-64 mb-2 border rounded">
                    <div id="mapPicker" style="height: 100%; width: 100%;"></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Latitude</label>
                        <input type="number" id="eventLat" step="0.00000001" required readonly
                            class="w-full border rounded px-3 py-2 bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Longitude</label>
                        <input type="number" id="eventLng" step="0.00000001" required readonly
                            class="w-full border rounded px-3 py-2 bg-gray-100">
                    </div>
                </div>
            </div>

            <!-- Geofence Radius -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Geofence Radius (meters) <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center gap-4">
                    <input type="range" id="eventRadius" min="10" max="500" value="50" step="10" class="flex-1"
                        oninput="document.getElementById('radiusValue').textContent = this.value + 'm'">
                    <span id="radiusValue" class="text-lg font-semibold text-blue-600 w-20 text-right">50m</span>
                </div>
            </div>

            <!-- Grace Period -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Grace Period (minutes after event ends) <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center gap-4">
                    <input type="range" id="eventGracePeriod" min="0" max="120" value="20" step="5" class="flex-1"
                        oninput="document.getElementById('graceValue').textContent = this.value + ' min'">
                    <span id="graceValue" class="text-lg font-semibold text-blue-600 w-24 text-right">20 min</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">Users can check in up to this many minutes after the event ends
                </p>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="closeEventModal()"
                    class="px-4 py-2 border rounded hover:bg-gray-100">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save
                    Event</button>
            </div>
        </form>
    </div>
</div>

<!-- Attendance Modal -->
<div id="attendanceModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-5xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center">
            <h2 class="text-xl font-bold text-blue-900">Event Attendance</h2>
            <button onclick="closeAttendanceModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        <div class="p-6">
            <div id="attendanceContent"></div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>

<script src="https://maps.googleapis.com/maps/api/js?key=<?= htmlspecialchars($googleMapsApiKey) ?>&libraries=places">
</script>
<script>
let mapPicker;
let mapMarker;
let currentEventId = null;

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

function loadEvents() {
    showBlockingLoader('Loading events...');
    $('#wait').show();
    $('#eventsDisplay').html('');

    $.get('api/admin/events.php', function(response) {
        hideBlockingLoader();
        $('#wait').hide();

        if (response.success) {
            displayEvents(response.data);
        } else {
            $('#eventsDisplay').html('<p class="text-red-500 text-center py-8">' + response.message + '</p>');
        }
    }).fail(function() {
        hideBlockingLoader();
        $('#wait').hide();
        $('#eventsDisplay').html('<p class="text-red-500 text-center py-8">Failed to load events</p>');
    });
}

function displayEvents(events) {
    if (events.length === 0) {
        $('#eventsDisplay').html(
            '<p class="text-gray-500 text-center py-8">No events found. Create your first event!</p>');
        return;
    }

    let html = '<table class="min-w-full divide-y divide-gray-200"><thead class="bg-blue-500 text-white"><tr>';
    html += '<th class="px-4 py-2 text-left">Title</th>';
    html += '<th class="px-4 py-2 text-left">Start Time</th>';
    html += '<th class="px-4 py-2 text-left">End Time</th>';
    html += '<th class="px-4 py-2 text-left">Grace Period</th>';
    html += '<th class="px-4 py-2 text-left">Status</th>';
    html += '<th class="px-4 py-2 text-left">Attendance</th>';
    html += '<th class="px-4 py-2 text-left">Actions</th>';
    html += '</tr></thead><tbody>';

    events.forEach(event => {
        const statusClass = event.status === 'active' ? 'bg-green-100 text-green-800' :
            event.status === 'upcoming' ? 'bg-blue-100 text-blue-800' :
            'bg-gray-100 text-gray-800';

        html += '<tr class="hover:bg-gray-50">';
        html += '<td class="px-4 py-2">' + escapeHtml(event.title) + '</td>';
        html += '<td class="px-4 py-2">' + formatDateTime(event.start_time) + '</td>';
        html += '<td class="px-4 py-2">' + formatDateTime(event.end_time) + '</td>';
        html += '<td class="px-4 py-2">' + event.grace_period_minutes + ' min</td>';
        html += '<td class="px-4 py-2"><span class="px-2 py-1 rounded text-xs ' + statusClass + '">' + event
            .status.toUpperCase() + '</span></td>';
        html += '<td class="px-4 py-2"><button onclick="viewAttendance(' + event.id +
            ')" class="text-blue-600 hover:underline">' + event.attendance_count + ' attendees</button></td>';
        html += '<td class="px-4 py-2">';
        html += '<div class="flex items-center gap-3">';
        html += '<a href="event-details.php?id=' + event.id +
            '" class="text-blue-600 hover:text-blue-800" title="View Event Details">';
        html += '<i class="fas fa-eye text-lg"></i></a>';
        html += '<button onclick="editEvent(' + event.id +
            ')" class="text-blue-600 hover:text-blue-800" title="Edit Event">';
        html += '<i class="fas fa-edit text-lg"></i></button>';
        html += '<button onclick="deleteEvent(' + event.id +
            ')" class="text-red-600 hover:text-red-800" title="Delete Event">';
        html += '<i class="fas fa-trash text-lg"></i></button>';
        html += '</div>';
        html += '</td>';
        html += '</tr>';
    });

    html += '</tbody></table>';
    $('#eventsDisplay').html(html);
}

function formatDateTime(datetime) {
    const d = new Date(datetime);
    return d.toLocaleString();
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function showCreateEventModal() {
    currentEventId = null;
    $('#modalTitle').text('Create Event');
    $('#eventForm')[0].reset();
    $('#eventId').val('');
    $('#eventRadius').val(50);
    $('#radiusValue').text('50m');
    $('#eventGracePeriod').val(20);
    $('#graceValue').text('20 min');
    $('#eventLat').val('');
    $('#eventLng').val('');
    $('#mapContainer').addClass('hidden');
    $('#eventModal').removeClass('hidden');
}

function editEvent(eventId) {
    currentEventId = eventId;
    showBlockingLoader('Loading event details...');

    $.get('api/admin/events.php?id=' + eventId, function(response) {
        hideBlockingLoader();

        if (response.success) {
            const event = response.data;
            $('#modalTitle').text('Edit Event');
            $('#eventId').val(event.id);
            $('#eventTitle').val(event.title);
            $('#eventDescription').val(event.description || '');

            // Format datetime for input fields
            $('#eventStartTime').val(event.start_time.substring(0, 16));
            $('#eventEndTime').val(event.end_time.substring(0, 16));

            $('#eventLat').val(event.location_lat);
            $('#eventLng').val(event.location_lng);
            $('#eventRadius').val(event.geofence_radius);
            $('#radiusValue').text(event.geofence_radius + 'm');
            $('#eventGracePeriod').val(event.grace_period_minutes);
            $('#graceValue').text(event.grace_period_minutes + ' min');

            $('#eventModal').removeClass('hidden');
        } else {
            Swal.fire('Error', response.message, 'error');
        }
    });
}

function deleteEvent(eventId) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This will delete the event and all attendance records',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            showBlockingLoader('Deleting event...');

            $.ajax({
                url: 'api/admin/events.php?id=' + eventId,
                type: 'DELETE',
                success: function(response) {
                    hideBlockingLoader();
                    if (response.success) {
                        Swal.fire('Deleted!', response.message, 'success');
                        loadEvents();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    hideBlockingLoader();
                    Swal.fire('Error', 'Failed to delete event', 'error');
                }
            });
        }
    });
}

function closeEventModal() {
    $('#eventModal').addClass('hidden');
    if (mapMarker) {
        mapMarker.setMap(null);
        mapMarker = null;
    }
}

function useCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            position => {
                $('#eventLat').val(position.coords.latitude);
                $('#eventLng').val(position.coords.longitude);
                if (mapPicker) {
                    const pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    mapPicker.setCenter(pos);
                    if (mapMarker) {
                        mapMarker.setPosition(pos);
                    } else {
                        mapMarker = new google.maps.Marker({
                            position: pos,
                            map: mapPicker,
                            draggable: true
                        });
                    }
                }
            },
            error => {
                Swal.fire('Error', 'Unable to get your location', 'error');
            }
        );
    } else {
        Swal.fire('Error', 'Geolocation is not supported by your browser', 'error');
    }
}

function openMapPicker() {
    $('#mapContainer').removeClass('hidden');

    if (!mapPicker) {
        const lat = parseFloat($('#eventLat').val()) || 6.5244;
        const lng = parseFloat($('#eventLng').val()) || 3.3792;
        const center = {
            lat,
            lng
        };

        mapPicker = new google.maps.Map(document.getElementById('mapPicker'), {
            center: center,
            zoom: 15
        });

        mapMarker = new google.maps.Marker({
            position: center,
            map: mapPicker,
            draggable: true
        });

        mapPicker.addListener('click', (e) => {
            const pos = {
                lat: e.latLng.lat(),
                lng: e.latLng.lng()
            };
            mapMarker.setPosition(pos);
            $('#eventLat').val(pos.lat);
            $('#eventLng').val(pos.lng);
        });

        mapMarker.addListener('dragend', (e) => {
            const pos = e.latLng;
            $('#eventLat').val(pos.lat());
            $('#eventLng').val(pos.lng());
        });
    }
}

function viewAttendance(eventId) {
    currentEventId = eventId;
    showBlockingLoader('Loading attendance...');

    $.get('api/admin/events.php?id=' + eventId, function(response) {
        hideBlockingLoader();

        if (response.success && response.data.attendance) {
            displayAttendance(response.data);
            $('#attendanceModal').removeClass('hidden');
        } else {
            Swal.fire('Error', 'Failed to load attendance', 'error');
        }
    });
}

function displayAttendance(event) {
    let html = '<div class="mb-4"><h3 class="text-lg font-bold mb-2">' + escapeHtml(event.title) + '</h3>';
    html += '<p class="text-sm text-gray-600">' + formatDateTime(event.start_time) + ' - ' + formatDateTime(event
        .end_time) + '</p></div>';

    if (event.attendance.length === 0) {
        html += '<p class="text-gray-500 text-center py-8">No attendance records yet</p>';
    } else {
        html += '<div class="flex justify-between items-center mb-4">';
        html += '<span class="font-semibold">Total: ' + event.attendance.length + ' attendees</span>';
        html += '<button onclick="exportAttendance(' + event.id +
            ')" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">';
        html += '<i class="fas fa-file-excel mr-1"></i>Export to Excel</button>';
        html += '</div>';

        html +=
            '<div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200"><thead class="bg-blue-500 text-white"><tr>';
        html += '<th class="px-4 py-2 text-left">Member Name</th>';
        html += '<th class="px-4 py-2 text-left">Member ID</th>';
        html += '<th class="px-4 py-2 text-left">Check-in Time</th>';
        html += '<th class="px-4 py-2 text-left">Distance (m)</th>';
        html += '<th class="px-4 py-2 text-left">Device ID</th>';
        html += '<th class="px-4 py-2 text-left">Status</th>';
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
            html += '<td class="px-4 py-2"><span class="font-mono text-xs">' + escapeHtml(att.device_id ||
                'N/A') + '</span></td>';
            html +=
                '<td class="px-4 py-2"><span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">' +
                att.status + '</span></td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
    }

    $('#attendanceContent').html(html);
}

function closeAttendanceModal() {
    $('#attendanceModal').addClass('hidden');
}

function exportAttendance(eventId) {
    window.location.href = 'api/admin/export-attendance.php?event_id=' + eventId;
}

$('#eventForm').on('submit', function(e) {
    e.preventDefault();

    const eventData = {
        title: $('#eventTitle').val().trim(),
        description: $('#eventDescription').val().trim(),
        start_time: $('#eventStartTime').val(),
        end_time: $('#eventEndTime').val(),
        location_lat: parseFloat($('#eventLat').val()),
        location_lng: parseFloat($('#eventLng').val()),
        geofence_radius: parseInt($('#eventRadius').val()),
        grace_period_minutes: parseInt($('#eventGracePeriod').val())
    };

    if (!eventData.location_lat || !eventData.location_lng) {
        Swal.fire('Error', 'Please select event location', 'error');
        return;
    }

    showBlockingLoader('Saving event...');

    const method = currentEventId ? 'PUT' : 'POST';
    const url = 'api/admin/events.php' + (currentEventId ? '?id=' + currentEventId : '');

    $.ajax({
        url: url,
        type: method,
        contentType: 'application/json',
        data: JSON.stringify(eventData),
        success: function(response) {
            hideBlockingLoader();
            if (response.success) {
                Swal.fire('Success!', response.message || 'Event saved successfully', 'success');
                closeEventModal();
                loadEvents();
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function(xhr) {
            hideBlockingLoader();
            const response = xhr.responseJSON || {};
            Swal.fire('Error', response.message || 'Failed to save event', 'error');
        }
    });
});

// Load events on page load
$(document).ready(function() {
    loadEvents();
});
</script>