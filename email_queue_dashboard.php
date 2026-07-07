<?php
session_start();
if (!isset($_SESSION['UserID'])) {
    header("Location: index.php");
    exit();
}

require_once('Connections/cov.php');
mysqli_select_db($cov, $database_cov);
require_once('libs/services/EmailQueueManager.php');

$queueManager = new EmailQueueManager($cov, $database_cov);

require_once('header.php');

// Handle manual queue processing (for testing)
if (isset($_POST['action']) && $_POST['action'] === 'process_queue') {
    $result = $queueManager->processQueue();
    $message = "Processed: {$result['processed']}, Failed: {$result['failed']}";
    if (isset($result['skipped'])) {
        $message .= " (Skipped: {$result['skipped']})";
    }
}

// Get queue statistics
$stats = $queueManager->getQueueStats();

// Get recent queue items
$recentQuery = "SELECT eq.*, 
                CONCAT(p.Lname, ', ', p.Fname, ' ', IFNULL(p.Mname, '')) as member_name,
                pp.PayrollPeriod
                FROM tbl_email_queue eq
                LEFT JOIN tbl_personalinfo p ON eq.member_id = p.memberid
                LEFT JOIN tbpayrollperiods pp ON eq.period_id = pp.Periodid
                ORDER BY eq.created_at DESC 
                LIMIT 50";
$recentResult = mysqli_query($cov, $recentQuery);
?>

<div class="max-w-7xl mx-auto bg-white rounded-xl shadow-lg mt-8 mb-16 p-6 md:p-10">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-blue-900 flex items-center gap-2">
            <i class="fa fa-envelope text-blue-600"></i>
            Email Queue Management
        </h1>
        <div class="flex gap-2">
            <form method="POST" class="inline">
                <input type="hidden" name="action" value="process_queue">
                <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">
                    <i class="fa fa-play mr-2"></i>Process Queue Now
                </button>
            </form>
            <a href="dashboard.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold">
                <i class="fa fa-arrow-left mr-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Success Message -->
    <?php if (isset($message)): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <i class="fa fa-check-circle mr-2"></i><?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600">Pending</p>
                    <p class="text-2xl font-bold text-blue-600"><?= $stats['pending']['count'] ?? 0 ?></p>
                </div>
                <div class="text-blue-500">
                    <i class="fa fa-clock text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600">Sent Today</p>
                    <p class="text-2xl font-bold text-green-600"><?= $stats['sent']['count'] ?? 0 ?></p>
                </div>
                <div class="text-green-500">
                    <i class="fa fa-check text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600">Failed</p>
                    <p class="text-2xl font-bold text-red-600"><?= $stats['failed']['count'] ?? 0 ?></p>
                </div>
                <div class="text-red-500">
                    <i class="fa fa-times text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600">Processing</p>
                    <p class="text-2xl font-bold text-yellow-600"><?= $stats['processing']['count'] ?? 0 ?></p>
                </div>
                <div class="text-yellow-500">
                    <i class="fa fa-spinner text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Queue Items -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
            <h3 class="text-white text-lg font-bold flex items-center gap-2">
                <i class="fa fa-list-alt"></i>
                Recent Queue Items (Last 50)
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table id="queueTable" class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr class="border-b border-gray-200">
                        <th data-type="number" class="sortable px-4 py-3 text-left font-semibold text-gray-700 cursor-pointer select-none hover:bg-gray-100">ID <span class="sort-arrow ml-1 text-gray-400"></span></th>
                        <th data-type="text" class="sortable px-4 py-3 text-left font-semibold text-gray-700 cursor-pointer select-none hover:bg-gray-100">Member <span class="sort-arrow ml-1 text-gray-400"></span></th>
                        <th data-type="text" class="sortable px-4 py-3 text-left font-semibold text-gray-700 cursor-pointer select-none hover:bg-gray-100">Period <span class="sort-arrow ml-1 text-gray-400"></span></th>
                        <th data-type="text" class="sortable px-4 py-3 text-left font-semibold text-gray-700 cursor-pointer select-none hover:bg-gray-100">Type <span class="sort-arrow ml-1 text-gray-400"></span></th>
                        <th data-type="text" class="sortable px-4 py-3 text-left font-semibold text-gray-700 cursor-pointer select-none hover:bg-gray-100">Recipient <span class="sort-arrow ml-1 text-gray-400"></span></th>
                        <th data-type="text" class="sortable px-4 py-3 text-left font-semibold text-gray-700 cursor-pointer select-none hover:bg-gray-100">Subject <span class="sort-arrow ml-1 text-gray-400"></span></th>
                        <th data-type="text" class="sortable px-4 py-3 text-left font-semibold text-gray-700 cursor-pointer select-none hover:bg-gray-100">Status <span class="sort-arrow ml-1 text-gray-400"></span></th>
                        <th data-type="number" class="sortable px-4 py-3 text-left font-semibold text-gray-700 cursor-pointer select-none hover:bg-gray-100">Priority <span class="sort-arrow ml-1 text-gray-400"></span></th>
                        <th data-type="date" class="sortable px-4 py-3 text-left font-semibold text-gray-700 cursor-pointer select-none hover:bg-gray-100">Created <span class="sort-arrow ml-1 text-gray-400"></span></th>
                        <th data-type="date" class="sortable px-4 py-3 text-left font-semibold text-gray-700 cursor-pointer select-none hover:bg-gray-100">Scheduled <span class="sort-arrow ml-1 text-gray-400"></span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (mysqli_num_rows($recentResult) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($recentResult)): ?>
                    <?php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'processing' => 'bg-blue-100 text-blue-800',
                                'sent' => 'bg-green-100 text-green-800',
                                'failed' => 'bg-red-100 text-red-800',
                                'cancelled' => 'bg-gray-100 text-gray-800'
                            ];
                            $priorityColors = [
                                1 => 'bg-red-100 text-red-800',
                                2 => 'bg-blue-100 text-blue-800',
                                3 => 'bg-gray-100 text-gray-800'
                            ];
                            ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-gray-600" data-sort="<?= (int)$row['id'] ?>"><?= $row['id'] ?></td>
                        <td class="px-4 py-3" data-sort="<?= htmlspecialchars($row['member_name'] ?? '') ?>">
                            <div>
                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($row['member_name']) ?></p>
                                <p class="text-xs text-gray-500">ID: <?= $row['member_id'] ?></p>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600" data-sort="<?= htmlspecialchars($row['PayrollPeriod'] ?? '') ?>"><?= htmlspecialchars($row['PayrollPeriod']) ?></td>
                        <td class="px-4 py-3" data-sort="<?= htmlspecialchars($row['email_type'] ?? '') ?>">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                <?= ucfirst(str_replace('_', ' ', $row['email_type'])) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600" data-sort="<?= htmlspecialchars($row['recipient_email'] ?? '') ?>"><?= htmlspecialchars($row['recipient_email']) ?></td>
                        <td class="px-4 py-3 text-gray-800" data-sort="<?= htmlspecialchars($row['subject'] ?? '') ?>"><?= htmlspecialchars(substr($row['subject'], 0, 50)) ?>...
                        </td>
                        <td class="px-4 py-3" data-sort="<?= htmlspecialchars($row['status'] ?? '') ?>">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusColors[$row['status']] ?>">
                                <?= ucfirst($row['status']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3" data-sort="<?= (int)$row['priority'] ?>">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $priorityColors[$row['priority']] ?>">
                                <?= $row['priority'] === 1 ? 'High' : ($row['priority'] === 2 ? 'Normal' : 'Low') ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600" data-sort="<?= htmlspecialchars($row['created_at'] ?? '') ?>">
                            <span class="utc-time" data-utc="<?= $row['created_at'] ?>"><?= $row['created_at'] ?></span>
                        </td>
                        <td class="px-4 py-3 text-gray-600" data-sort="<?= htmlspecialchars($row['scheduled_at'] ?? '') ?>">
                            <span class="utc-time" data-utc="<?= $row['scheduled_at'] ?>"><?= $row['scheduled_at'] ?></span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr data-empty="1">
                        <td colspan="10" class="px-4 py-8 text-center text-gray-500">
                            <i class="fa fa-inbox text-4xl mb-4"></i>
                            <p>No email queue items found.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- System Information -->
    <div class="mt-8 bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">System Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <p><strong>Rate Limit:</strong> 80 emails per hour</p>
                <p><strong>Batch Size:</strong> 40 emails per batch</p>
                <p><strong>Retry Delay:</strong> 5 minutes between retries</p>
            </div>
            <div>
                <p><strong>Max Retries:</strong> 3 attempts per email</p>
                <p><strong>Cron Schedule:</strong> Every 30 minutes</p>
                <p><strong>Log File:</strong> logs/email_queue.log</p>
            </div>
        </div>
    </div>
</div>

<script>
// Convert all UTC timestamps to user's local time
document.addEventListener('DOMContentLoaded', function() {
    const utcElements = document.querySelectorAll('.utc-time');
    
    utcElements.forEach(function(element) {
        const utcTime = element.getAttribute('data-utc');
        if (utcTime && utcTime !== 'NULL' && utcTime !== '') {
            // Parse UTC time and convert to local
            const date = new Date(utcTime + ' UTC'); // Treat as UTC
            
            // Format: Oct 23, 2025 11:52
            const options = {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            };
            
            const localTime = date.toLocaleString('en-US', options);
            element.textContent = localTime;
            
            // Add tooltip showing original UTC time
            element.title = 'UTC: ' + utcTime;
        }
    });
});

// Click any header to sort the table (toggles ascending/descending)
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('queueTable');
    if (!table) return;
    const tbody = table.querySelector('tbody');
    const headers = Array.from(table.querySelectorAll('thead th.sortable'));
    let sortCol = -1, sortDir = 1;

    function cellValue(td, type) {
        if (!td) return type === 'number' ? 0 : '';
        const raw = td.dataset.sort !== undefined ? td.dataset.sort : td.textContent.trim();
        if (type === 'number') {
            const n = parseFloat(String(raw).replace(/[^0-9.\-]/g, ''));
            return isNaN(n) ? -Infinity : n;
        }
        if (type === 'date') {
            if (!raw || raw === 'NULL') return -Infinity;
            const t = Date.parse(raw.replace(' ', 'T') + 'Z');
            return isNaN(t) ? -Infinity : t;
        }
        return String(raw).toLowerCase();
    }

    headers.forEach(function(th, idx) {
        th.addEventListener('click', function() {
            const type = th.dataset.type || 'text';
            sortDir = (sortCol === idx) ? -sortDir : 1;
            sortCol = idx;

            const rows = Array.from(tbody.querySelectorAll('tr')).filter(r => !r.dataset.empty);
            rows.sort(function(a, b) {
                const av = cellValue(a.children[idx], type);
                const bv = cellValue(b.children[idx], type);
                if (av < bv) return -1 * sortDir;
                if (av > bv) return 1 * sortDir;
                return 0;
            });
            rows.forEach(r => tbody.appendChild(r));

            headers.forEach(function(h) {
                const arrow = h.querySelector('.sort-arrow');
                if (arrow) { arrow.textContent = ''; }
            });
            const arrow = th.querySelector('.sort-arrow');
            if (arrow) { arrow.textContent = sortDir === 1 ? '▲' : '▼'; }
        });
    });
});
</script>

<?php require_once('footer.php'); ?>