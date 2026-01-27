<?php
require_once('Connections/cov.php');
if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_SESSION['UserID'])) {
    header("Location: index.php");
    exit();
}
?>
<?php include 'header.php'; ?>
<!-- Sidebar is handled in header.php or separate file depending on structure, but header.php included it in the analysis -->

<div class="flex flex-col flex-1 overflow-y-auto overflow-x-hidden bg-white text-slate-900 min-h-screen transition-colors duration-200">
    
    <main class="w-full flex-grow p-6 md:p-8">
        <header class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-blue-900">Cooperative SMS Center</h2>
                <p class="text-slate-500 mt-1">Send notifications to all cooperative members or specific contacts.</p>
            </div>
             <a href="dashboard.php" class="btn btn-sm bg-blue-600 text-white px-3 py-1 rounded w-fit">Back to Dashboard</a>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- SMS Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Search & Add -->
                <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 shadow-sm mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search & Add Members</label>
                    <div class="relative">
                        <input type="text" id="memberSearch" class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" placeholder="Search by name or member ID...">
                        <span class="fa fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></span>
                    </div>
                </div>

                <!-- Main Form -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                    
                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-sm font-medium text-gray-700">Recipients</label>
                            <div class="flex gap-2">
                                <button onclick="addAllContacts()" class="px-3 py-1.5 text-xs font-semibold bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors flex items-center gap-1">
                                    <i class="fa fa-users"></i> Add All Active
                                </button>
                                <button onclick="clearRecipients()" class="px-3 py-1.5 text-xs font-semibold bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-1">
                                    <i class="fa fa-backspace"></i> Clear
                                </button>
                            </div>
                        </div>
                        <textarea id="recipientList" rows="4" class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 outline-none transition-all resize-none font-mono text-sm" placeholder="Enter mobile numbers separated by commas (e.g. 08012345678, 09087654321)"></textarea>
                        <p class="text-xs text-gray-500 mt-2">Total Recipients: <span id="recipientCount" class="font-bold">0</span></p>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                        <textarea id="smsMessage" rows="5" class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 outline-none transition-all resize-none" placeholder="Type your message here..."></textarea>
                        <div class="flex justify-between items-center mt-2 flex-wrap">
                            <p class="text-xs text-gray-500">
                                Count: <span id="charCount" class="font-bold text-gray-900">0</span> | 
                                Pages: <span id="pageCount" class="font-bold text-gray-900">0</span>
                                <span id="smsTypeDisp" class="ml-2 text-gray-400">(GSM: 160/page)</span>
                            </p>
                            <p class="text-xs text-gray-500 font-medium">
                                Est. Cost: <span id="costEstimate" class="font-bold text-blue-600">₦0.00</span>
                            </p>
                        </div>
                    </div>

                    <button onclick="sendBulkSMS()" id="btnSend" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-600/20 transition-all flex items-center justify-center gap-2">
                        <i class="fa fa-paper-plane"></i>
                        Send Broadcast
                    </button>

                </div>
            </div>

            <!-- Guidelines / Status -->
            <div class="space-y-6">
                <!-- Status Card -->
                <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 shadow-sm">
                    <h3 class="font-bold mb-4 flex items-center gap-2 text-gray-700">
                        <i class="fa fa-info-circle text-gray-500"></i>
                        Usage Guidelines
                    </h3>
                    <ul class="space-y-3 text-sm text-gray-600">
                        <li class="flex items-start gap-2">
                            <i class="fa fa-check-circle text-green-500 mt-0.5"></i>
                            <span>Numbers are automatically formatted (e.g., 080... becomes 23480...).</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa fa-check-circle text-green-500 mt-0.5"></i>
                            <span>Separate multiple numbers with comma.</span>
                        </li>
                        <li class="flex items-start gap-2">
                             <i class="fa fa-exclamation-triangle text-amber-500 mt-0.5"></i>
                            <span><b>Special Characters</b> (e.g. ; ^ { } \ [ ~ ] | € ' ”) reduce limit to <b>70 chars/page</b>. Avoid if possible to save cost.</span>
                        </li>
                    </ul>
                </div>

                <!-- Recent History (Basic View) -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                     <h3 class="font-bold mb-4 flex items-center gap-2 text-gray-700">
                        <i class="fa fa-history text-gray-500"></i>
                        Recent SMS History
                        <button onclick="loadHistory()" class="ml-auto text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded text-gray-600 transition-colors">
                            <i class="fa fa-refresh"></i> Refresh
                        </button>
                    </h3>
                    <div id="smsHistoryLoader" class="text-center py-4 hidden">
                        <i class="fa fa-spinner fa-spin text-blue-500"></i> Loading...
                    </div>
                    <div id="smsHistoryList" class="text-sm text-gray-500 space-y-2">
                        <!-- Populated by JS -->
                    </div>
                </div>

                <!-- API Balance Card -->
                <div class="bg-blue-50 border border-blue-100 rounded-2xl p-6 shadow-sm">
                    <h3 class="font-bold mb-2 flex items-center gap-2 text-blue-900">
                        <i class="fa fa-wallet text-blue-500"></i>
                        SMS Balance
                    </h3>
                    <p class="text-3xl font-bold text-blue-800" id="smsBalanceDisp">---</p>
                    <p class="text-xs text-blue-600 mt-1">Provider: Termii</p>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
    // --- Search Logic (jQuery UI Autocomplete) ---
    // Reusing header.php jQuery
    $(function() {
        $("#memberSearch").autocomplete({
            source: "search_members.php",
            minLength: 2,
            select: function(event, ui) {
                addContact(ui.item.mobile, ui.item.membername);
                // Clear input
                setTimeout(() => $("#memberSearch").val(''), 100);
                return false;
            }
        }).autocomplete("instance")._renderItem = function(ul, item) {
            return $("<li>")
                .append("<div>" + item.label + "<br><span style='font-size:0.9em;color:#888'>" + item.mobile + "</span></div>")
                .appendTo(ul);
        };
    });

    window.addContact = function(phone, name) {
        if (!phone) {
            Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'Member has no phone number', showConfirmButton: false, timer: 3000 });
            return;
        }
        const currentVal = $('#recipientList').val();
        // Check if already exists
        if (currentVal.includes(phone)) {
             Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Number already added', showConfirmButton: false, timer: 2000 });
             return;
        }
        
        const newVal = currentVal ? (currentVal + ', ' + phone) : phone;
        $('#recipientList').val(newVal).trigger('input');
        
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000
        });
        Toast.fire({ icon: 'success', title: `Added ${name}` });
    };

    // --- Counter & Recipient Logic ---

    // --- Counter & Recipient Logic --- //

    // Reusable function to update all counts and cost
    function updateCounts() {
        const messageVal = $('#smsMessage').val();
        const recipientText = $('#recipientList').val();
         // Filter empty strings to get accurate count
        const recipientCount = recipientText.split(',').filter(s => s.trim().length > 0).length;
        
        $('#recipientCount').text(recipientCount);

        // --- Message Logic ---
        const len = messageVal.length;
        $('#charCount').text(len);
        
        // Special Char Check
        const specialRegex = /[;\^\/\{\}\\\\[~\]|€'”]/; 
        const isSpecial = specialRegex.test(messageVal);

        let limit = 160;
        let multiLimit = 153; 
        let typeHtml = '<span class="text-gray-400">(GSM: 160/page)</span>';

        if (isSpecial) {
            limit = 70;
            multiLimit = 67; 
            typeHtml = '<span class="text-amber-600 font-bold"><i class="fa fa-exclamation-triangle"></i> Special Char (70/page)</span>';
        }
        
        let pages = 0;
        if (len > 0) {
            if (len <= limit) pages = 1;
            else pages = 1 + Math.ceil((len - limit) / multiLimit); 
        }
        $('#pageCount').text(pages);
        $('#smsTypeDisp').html(typeHtml);

        // --- Cost Logic ---
        const costPerPage = 5.0; // Fixed cost
        const totalCost = pages * recipientCount * costPerPage;
        const formattedCost = totalCost.toLocaleString('en-NG', { style: 'currency', currency: 'NGN' });
        
        $('#costEstimate').text(formattedCost);
    }

    $('#recipientList').on('input', function() {
        updateCounts(); // triggers everything
    });

    $('#smsMessage').on('input', function() {
        updateCounts();
    });

    window.clearRecipients = function() {
        $('#recipientList').val('').trigger('input');
    }

    window.addAllContacts = function() {
        // Show loading state
        const btn = $(event.currentTarget);
        const originalText = btn.html();
        btn.html('<i class="fa fa-spinner fa-spin"></i> Loading...');
        btn.prop('disabled', true);

        $.post('coop_sms_action.php', { action: 'fetch_all_contacts' }, function(res) {
            if (res.status === 'success') {
                const currentVal = $('#recipientList').val();
                const newNumbers = res.data.join(', ');
                
                // Be smart about comma
                let finalVal = currentVal.trim();
                if (finalVal && !finalVal.endsWith(',')) finalVal += ', ';
                finalVal += newNumbers;
                
                $('#recipientList').val(finalVal).trigger('input');
                
                const count = res.data.length;
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: `Added ${count} active contacts`,
                    showConfirmButton: false,
                    timer: 3000
                });
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }, 'json').always(() => {
            btn.html(originalText);
            btn.prop('disabled', false);
        });
    }

    window.sendBulkSMS = function() {
        const recipients = $('#recipientList').val();
        const message = $('#smsMessage').val();

        if (!recipients.trim()) {
            Swal.fire('Error', 'Please add at least one recipient.', 'warning');
            return;
        }
        if (!message.trim()) {
            Swal.fire('Error', 'Please enter a message.', 'warning');
            return;
        }

        // Check Cost First
        const btn = $('#btnSend');
        const originalText = btn.html();
        btn.html('<i class="fa fa-spinner fa-spin"></i> Checking...');
        btn.prop('disabled', true);

        $.post('coop_sms_action.php', {
            action: 'check_cost',
            recipients: recipients,
            message: message
        }, function(res) {
            btn.html(originalText);
            btn.prop('disabled', false);

            if (res.status === 'success') {
                const cost = parseFloat(res.data.cost);
                const balance = parseFloat(res.data.balance);
                const canSend = res.data.can_send;
                const formattedCost = cost.toLocaleString('en-NG', { style: 'currency', currency: 'NGN' });
                const formattedBalance = balance.toLocaleString('en-NG', { style: 'currency', currency: 'NGN' });

                let confirmTitle = 'Send Broadcast?';
                let confirmText = `Send SMS to ${$('#recipientCount').text()} recipients?\n\nEstimated Cost: ${formattedCost}\nCurrent Balance: ${formattedBalance}`;
                let confirmIcon = 'question';
                let confirmButtonText = 'Yes, Send';
                let confirmButtonColor = '#0ea5e9';

                if (!canSend) {
                     confirmTitle = 'Insufficient Balance';
                     confirmText = `Estimated Cost: ${formattedCost}\nCurrent Balance: ${formattedBalance}\n\nPlease top up your account to send this broadcast.`;
                     confirmIcon = 'error';
                     confirmButtonText = 'Insufficient Balance';
                     confirmButtonColor = '#ef4444'; // Red
                }

                Swal.fire({
                    title: confirmTitle,
                    text: confirmText, // Using text for simple newline, or html for better formatting
                    html: confirmText.replace(/\n/g, '<br>'),
                    icon: confirmIcon,
                    showCancelButton: true,
                    confirmButtonColor: confirmButtonColor,
                    confirmButtonText: confirmButtonText,
                    showLoaderOnConfirm: true,
                    allowOutsideClick: false,
                    preConfirm: () => {
                         if (!canSend) return false; // Block action
                         
                         return $.post('coop_sms_action.php', {
                            action: 'send_bulk_sms',
                            recipients: recipients,
                            message: message
                        }, null, 'json')
                        .then(response => {
                            if (response.status !== 'success') {
                                throw new Error(response.message);
                            }
                            return response;
                        })
                        .catch(error => {
                            Swal.showValidationMessage(`Request failed: ${error}`);
                        });
                    }
                }).then((result) => {
                    if (result.isConfirmed && canSend) {
                        Swal.fire({
                            title: 'Broadcast Sent!',
                            text: result.value.message,
                            icon: 'success'
                        });
                        loadBalance(); // Refresh balance
                        loadHistory(); // Refresh history
                    }
                });

            } else {
                Swal.fire('Error', 'Failed to calculate cost estimate.', 'error');
            }
        }, 'json').fail(function() {
             btn.html(originalText);
             btn.prop('disabled', false);
             Swal.fire('Error', 'Network error checking cost.', 'error');
        });
    }
    // Load data on start
    $(function() {
        loadBalance();
        loadHistory();
    });

    function loadBalance() {
        $.post('coop_sms_action.php', { action: 'get_balance' }, function(res) {
            if (res.status === 'success') {
                // Assuming balance object or float
                let bal = res.data.balance;
                // Format currency if possible, or just raw
                if (!isNaN(bal)) {
                    bal = parseFloat(bal).toLocaleString('en-NG', { style: 'currency', currency: 'NGN' });
                }
                $('#smsBalanceDisp').text(bal);
            }
        }, 'json');
    }

    window.loadHistory = function() {
        const list = $('#smsHistoryList');
        const loader = $('#smsHistoryLoader');
        
        list.empty();
        loader.removeClass('hidden');

        $.post('coop_sms_action.php', { action: 'get_history' }, function(res) {
            loader.addClass('hidden');
            if (res.status === 'success' && res.data.length > 0) {
                // Table Header
                let html = `
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                            <tr>
                                <th class="px-3 py-2">Receiver</th>
                                <th class="px-3 py-2">Message</th>
                                <th class="px-3 py-2">Status</th>
                                <th class="px-3 py-2">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">`;

                res.data.forEach(item => {
                    const status = item.status.toLowerCase();
                    let statusClass = 'bg-gray-100 text-gray-800'; // Default
                    if (status === 'delivered') statusClass = 'bg-green-100 text-green-800';
                    else if (status === 'sent') statusClass = 'bg-blue-100 text-blue-800';
                    else if (status === 'failed' || status === 'rejected') statusClass = 'bg-red-100 text-red-800';
                    else if (status === 'pending') statusClass = 'bg-yellow-100 text-yellow-800';

                    // Use enriched member_name if available, else receiver
                    const displayName = item.member_name || item.receiver;
                    const subText = (displayName !== item.receiver) ? `<div class="text-[10px] text-gray-400 font-mono">${item.receiver}</div>` : '';

                    html += `
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-3 py-2 font-medium text-gray-900 border-none">
                            ${displayName}
                            ${subText}
                        </td>
                        <td class="px-3 py-2 border-none">
                            <div class="truncate max-w-[150px] cursor-help border-b border-dotted border-gray-300" title="${item.message}">
                                ${item.message}
                            </div>
                        </td>
                        <td class="px-3 py-2 border-none">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold ${statusClass}">
                                ${item.status}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-xs text-gray-500 whitespace-nowrap border-none">
                            ${item.created_at}
                        </td>
                    </tr>`;
                });

                html += `</tbody></table></div>`;
                list.html(html);
            } else {
                list.html('<p class="text-center text-gray-400 text-xs">No history found.</p>');
            }
        }, 'json').fail(() => {
            loader.addClass('hidden');
            list.html('<p class="text-center text-red-400 text-xs">Failed to load history.</p>');
        });
    }
</script>

<?php require_once('footer.php'); ?>
