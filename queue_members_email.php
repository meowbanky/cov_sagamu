<?php
session_start();
if (!isset($_SESSION['UserID'])) {
    header("Location:index.php");
    exit;
}

require_once('Connections/cov.php');
require_once('libs/services/EmailTemplateService.php');

// Initialize services
$emailTemplateService = new EmailTemplateService($cov, $database_cov);

// Handle form submission
if ($_POST) {
    $selectedMembersJson = $_POST['selectedMembers'] ?? '[]';
    $selectedMembers = json_decode($selectedMembersJson, true);
    
    // Handle JSON decode errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        $message = "Error: Invalid member selection data. Please try again.";
        $selectedMembers = [];
    }
    
    $periodId = $_POST['periodId'] ?? null;
    $scheduleType = $_POST['scheduleType'] ?? 'immediate';
    $scheduledDate = $_POST['scheduledDate'] ?? null;
    $scheduledTime = $_POST['scheduledTime'] ?? null;
    
    if (!empty($selectedMembers) && is_array($selectedMembers) && $periodId) {
        $queued = 0;
        $failed = 0;
        $errors = [];
        
        foreach ($selectedMembers as $memberId) {
            try {
                // Generate email data
                $emailData = $emailTemplateService->generateTransactionSummaryEmail($memberId, $periodId);
                
                if ($emailData) {
                    // Calculate scheduled time
                    $scheduledAt = null;
                    if ($scheduleType === 'future' && $scheduledDate && $scheduledTime) {
                        $scheduledAt = $scheduledDate . ' ' . $scheduledTime . ':00';
                    }
                    
                    // Queue the email
                    $queueId = $emailTemplateService->queueEmail(
                        $memberId,
                        $periodId,
                        $emailData['recipient_email'],
                        $emailData['recipient_name'],
                        $emailData['subject'],
                        $emailData['message_body'],
                        $scheduledAt,
                        $emailData['metadata']
                    );
                    
                    if ($queueId) {
                        $queued++;
                    } else {
                        $failed++;
                        $errors[] = "Failed to queue email for member ID: $memberId";
                    }
                } else {
                    $failed++;
                    $errors[] = "No email data generated for member ID: $memberId";
                }
            } catch (Exception $e) {
                $failed++;
                $errors[] = "Error processing member ID $memberId: " . $e->getMessage();
            }
        }
        
        $message = "Email Queue Summary: ✅ Queued: $queued, ❌ Failed: $failed";
        if (!empty($errors)) {
            $message .= "\n\nErrors:\n" . implode("\n", array_slice($errors, 0, 10));
            if (count($errors) > 10) {
                $message .= "\n... and " . (count($errors) - 10) . " more errors";
            }
        }
    } else {
        if (empty($selectedMembers) || !is_array($selectedMembers)) {
            $message = "❌ Error: Please select at least one member.";
        } elseif (!$periodId) {
            $message = "❌ Error: Please select a period.";
        } else {
            $message = "❌ Error: Invalid request. Please try again.";
        }
    }
}

require_once('header.php');
?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">
            <i class="fas fa-envelope-open-text text-blue-600 mr-2"></i>
            Queue Members for Email Schedule
        </h1>

        <?php if (isset($message)): ?>
        <div
            class="mb-6 p-4 rounded-lg <?= strpos($message, '✅') !== false ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300' ?>">
            <pre class="whitespace-pre-wrap"><?= htmlspecialchars($message) ?></pre>
        </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <!-- Period Selection -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="periodId" class="block text-sm font-medium text-gray-700 mb-2">
                        Select Period
                    </label>
                    <select id="periodId" name="periodId" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Select Period --</option>
                        <?php
                        $periodQuery = "SELECT Periodid, PayrollPeriod FROM tbpayrollperiods ORDER BY Periodid DESC LIMIT 12";
                        $periodResult = mysqli_query($cov, $periodQuery);
                        while ($period = mysqli_fetch_assoc($periodResult)) {
                            echo "<option value='{$period['Periodid']}'>{$period['PayrollPeriod']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Schedule Type -->
                <div>
                    <label for="scheduleType" class="block text-sm font-medium text-gray-700 mb-2">
                        Schedule Type
                    </label>
                    <select id="scheduleType" name="scheduleType"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        onchange="toggleScheduleFields()">
                        <option value="immediate">Send Immediately</option>
                        <option value="future">Schedule for Later</option>
                    </select>
                </div>
            </div>

            <!-- Future Schedule Fields -->
            <div id="futureScheduleFields" class="grid grid-cols-1 md:grid-cols-2 gap-6" style="display: none;">
                <div>
                    <label for="scheduledDate" class="block text-sm font-medium text-gray-700 mb-2">
                        Scheduled Date
                    </label>
                    <input type="date" id="scheduledDate" name="scheduledDate"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="scheduledTime" class="block text-sm font-medium text-gray-700 mb-2">
                        Scheduled Time
                    </label>
                    <input type="time" id="scheduledTime" name="scheduledTime"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <!-- Member Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Select Members
                </label>

                <!-- Search Box -->
                <div class="mb-4 flex gap-2">
                    <input type="text" id="memberSearch" placeholder="Filter members by name or ID..."
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="button" id="clearSearch"
                        class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                        <i class="fas fa-times mr-1"></i> Clear Filter
                    </button>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2 mb-4">
                    <button type="button" id="selectAllMembers"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        <i class="fas fa-check-double mr-1"></i> Select All
                    </button>
                    <button type="button" id="selectFiltered"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        <i class="fas fa-check mr-1"></i> Select Filtered
                    </button>
                    <button type="button" id="clearAllMembers"
                        class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                        <i class="fas fa-times mr-1"></i> Clear All
                    </button>
                    <span id="selectedCount" class="px-4 py-2 bg-blue-50 text-blue-700 rounded-md font-semibold">
                        0 selected
                    </span>
                </div>

                <!-- Members List with Checkboxes -->
                <div class="border border-gray-300 rounded-md bg-white" style="max-height: 400px; overflow-y: auto;">
                    <table class="w-full">
                        <thead class="bg-gray-100 sticky top-0">
                            <tr>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider w-12">
                                    <input type="checkbox" id="toggleAll" class="rounded" title="Toggle All Visible">
                                </th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Member Name
                                </th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider w-24">
                                    ID
                                </th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Email
                                </th>
                            </tr>
                        </thead>
                        <tbody id="membersTableBody">
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                    <i class="fas fa-spinner fa-spin text-2xl mb-2 block"></i>
                                    Loading members...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Hidden field for selected members -->
                <input type="hidden" id="selectedMembers" name="selectedMembers">
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit"
                    class="px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-700 font-medium">
                    <i class="fas fa-paper-plane mr-2"></i> Queue Emails
                </button>
            </div>
        </form>

        <!-- Queue Status -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-blue-800 mb-2">
                <i class="fas fa-info-circle mr-2"></i> Queue Information
            </h3>
            <div class="text-sm text-blue-700 space-y-1">
                <p><strong>Immediate Schedule:</strong> Emails will be queued for immediate processing by the cron job.
                </p>
                <p><strong>Future Schedule:</strong> Emails will be queued for processing at the specified date and
                    time.</p>
                <p><strong>Processing:</strong> The cron job runs every 30 minutes and processes up to 50 emails per
                    hour.</p>
                <p><strong>Monitoring:</strong> Check the <a href="email_queue_dashboard.php"
                        class="text-blue-600 hover:underline">Email Queue Dashboard</a> to monitor queue status.</p>
            </div>
        </div>
    </div>
</div>

<script>
let selectedMembersList = [];
let allMembers = [];
let filteredMembers = [];

// Toggle schedule fields
function toggleScheduleFields() {
    const scheduleType = document.getElementById('scheduleType').value;
    const futureFields = document.getElementById('futureScheduleFields');

    if (scheduleType === 'future') {
        futureFields.style.display = 'block';
    } else {
        futureFields.style.display = 'none';
    }
}

// Load all members
function loadAllMembers() {
    fetch('api/get_all_members.php')
        .then(response => response.json())
        .then(data => {
            allMembers = data;
            filteredMembers = data;
            console.log('Loaded members:', allMembers.length);
            displayMembersTable();
        })
        .catch(error => {
            console.error('Error loading members:', error);
            document.getElementById('membersTableBody').innerHTML = `
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-red-500">
                        <i class="fas fa-exclamation-triangle text-2xl mb-2 block"></i>
                        Error loading members. Please refresh the page.
                    </td>
                </tr>
            `;
        });
}

// Display members table
function displayMembersTable() {
    const tbody = document.getElementById('membersTableBody');

    if (filteredMembers.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                    <i class="fas fa-search text-2xl mb-2 block"></i>
                    No members found matching your filter.
                </td>
            </tr>
        `;
        return;
    }

    const html = filteredMembers.map(member => {
        const isSelected = selectedMembersList.some(selected => selected.id === member.memberid);
        return `
            <tr class="border-b border-gray-200 hover:bg-gray-50 ${isSelected ? 'bg-blue-50' : ''}">
                <td class="px-4 py-2">
                    <input type="checkbox" 
                           data-member-id="${member.memberid}"
                           data-member-name="${member.name.replace(/"/g, '&quot;')}"
                           ${isSelected ? 'checked' : ''}
                           onchange="toggleMemberSelection(this)"
                           class="member-checkbox rounded">
                </td>
                <td class="px-4 py-2 font-medium">${member.name}</td>
                <td class="px-4 py-2 text-gray-600">${member.memberid}</td>
                <td class="px-4 py-2 text-gray-600 text-sm">${member.email || 'No email'}</td>
            </tr>
        `;
    }).join('');

    tbody.innerHTML = html;
    updateSelectedCount();
}

// Toggle member selection
function toggleMemberSelection(checkbox) {
    const memberId = parseInt(checkbox.dataset.memberId);
    const memberName = checkbox.dataset.memberName;

    if (checkbox.checked) {
        if (!selectedMembersList.some(member => member.id === memberId)) {
            selectedMembersList.push({
                id: memberId,
                name: memberName
            });
        }
    } else {
        selectedMembersList = selectedMembersList.filter(member => member.id !== memberId);
    }

    updateSelectedMembersField();
    updateSelectedCount();
}

// Update selected count
function updateSelectedCount() {
    const count = selectedMembersList.length;
    document.getElementById('selectedCount').textContent = `${count} selected`;
}

// Update hidden field
function updateSelectedMembersField() {
    const memberIds = selectedMembersList.map(member => member.id);
    document.getElementById('selectedMembers').value = JSON.stringify(memberIds);
}

// Filter members
document.getElementById('memberSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase().trim();

    if (searchTerm === '') {
        filteredMembers = allMembers;
    } else {
        filteredMembers = allMembers.filter(member =>
            member.name.toLowerCase().includes(searchTerm) ||
            member.memberid.toString().includes(searchTerm) ||
            (member.email && member.email.toLowerCase().includes(searchTerm))
        );
    }

    displayMembersTable();
});

// Clear search
document.getElementById('clearSearch').addEventListener('click', function() {
    document.getElementById('memberSearch').value = '';
    filteredMembers = allMembers;
    displayMembersTable();
});

// Toggle all visible checkboxes
document.getElementById('toggleAll').addEventListener('change', function(e) {
    const checkboxes = document.querySelectorAll('.member-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = e.target.checked;
        toggleMemberSelection(checkbox);
    });
    displayMembersTable();
});

// Select all members
document.getElementById('selectAllMembers').addEventListener('click', function() {
    allMembers.forEach(member => {
        if (!selectedMembersList.some(selected => selected.id === member.memberid)) {
            selectedMembersList.push({
                id: member.memberid,
                name: member.name
            });
        }
    });
    updateSelectedMembersField();
    displayMembersTable();
});

// Select filtered members
document.getElementById('selectFiltered').addEventListener('click', function() {
    filteredMembers.forEach(member => {
        if (!selectedMembersList.some(selected => selected.id === member.memberid)) {
            selectedMembersList.push({
                id: member.memberid,
                name: member.name
            });
        }
    });
    updateSelectedMembersField();
    displayMembersTable();
});

// Clear all selections
document.getElementById('clearAllMembers').addEventListener('click', function() {
    selectedMembersList = [];
    updateSelectedMembersField();
    displayMembersTable();
});

// Load members on page load
document.addEventListener('DOMContentLoaded', function() {
    loadAllMembers();
});
</script>

<?php require_once('footer.php'); ?>