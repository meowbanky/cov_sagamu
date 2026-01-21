<?php
require_once('header.php');
if (!isset($_SESSION['UserID'])) {
    header("Location:index.php");
    exit;
}
require_once('Connections/cov.php');

// Fetch all periods
$periods = [];
$res = $cov->query("SELECT Periodid, PayrollPeriod FROM tbpayrollperiods ORDER BY Periodid DESC");
if ($res) $periods = $res->fetch_all(MYSQLI_ASSOC);

// Get current period (first in the list usually)
$current_period_id = count($periods) ? $periods[0]['Periodid'] : '';
$current_period_name = count($periods) ? $periods[0]['PayrollPeriod'] : '';
?>

<div class="container mt-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold mb-4 text-purple-700">Property Liquidation</h2>
    </div>

    <!-- Info Alert -->
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 shadow-sm rounded-r">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    Use this form to record a property value used to liquidate a member's loan. This will behave as a loan repayment transaction for the <strong>selected period</strong>.
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Liquidation Form -->
        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-100">
            <h3 class="text-lg font-medium mb-4 text-gray-800 border-b pb-2">New Liquidation Entry</h3>
            
            <form id="liquidationForm" autocomplete="off" class="space-y-4">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="property_id" id="propertyId" value="0">
                
                <div>
                    <label for="period_id" class="block text-sm font-medium text-gray-700 mb-1">Transaction Period</label>
                    <select id="period_id" name="period_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50 p-2 border" required>
                        <?php foreach($periods as $period): ?>
                            <option value="<?= htmlspecialchars($period['Periodid']) ?>" <?= $period['Periodid'] == $current_period_id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($period['PayrollPeriod']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Member Search -->
                <div>
                    <label for="CoopName" class="block text-sm font-medium text-gray-700 mb-1">Search Member</label>
                    <div class="relative">
                        <input type="text" id="CoopName" name="CoopName" class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50 p-2 border" placeholder="Type name or number..." required>
                        <input type="hidden" id="member_id" name="member_id" required>
                        <button type="button" id="clearMemberBtn" class="absolute right-2 top-2 text-gray-400 hover:text-red-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                    <p id="memberDetailHelp" class="mt-1 text-sm text-gray-500 min-h-[20px]"></p>
                </div>

                <!-- Current Loan Status -->
                <div id="loanStatusArea" class="hidden bg-gray-50 p-3 rounded border border-gray-200 mb-2">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Current Loan Balance:</span>
                        <span class="font-bold text-red-600 text-lg" id="currentLoanBalance">₦0.00</span>
                    </div>
                </div>

                <!-- Property Description -->
                <div>
                    <label for="property_name" class="block text-sm font-medium text-gray-700 mb-1">Property Name / Title</label>
                    <input type="text" id="property_name" name="property_name" class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50 p-2 border" placeholder="e.g. Plot at Abuja Estate" required>
                </div>

                <div>
                    <label for="property_description" class="block text-sm font-medium text-gray-700 mb-1">Description / Details (Optional)</label>
                    <textarea id="property_description" name="property_description" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50 p-2 border" placeholder="Additional details about the property..."></textarea>
                </div>

                <!-- Value -->
                <div>
                    <label for="property_value" class="block text-sm font-medium text-gray-700 mb-1">Property Value (Loan Reduction Amount)</label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">₦</span>
                        </div>
                        <input type="text" name="property_value" id="property_value" class="focus:ring-purple-500 focus:border-purple-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md p-2 border" placeholder="0.00" pattern="^\d*(\.\d{0,2})?$" required>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">This amount will be deducted from the member's loan balance.</p>
                </div>

                <div class="pt-4 flex gap-2">
                    <button type="submit" id="submitBtn" class="flex-1 flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        Process Liquidation
                    </button>
                    <button type="button" id="cancelEditBtn" class="hidden px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        Cancel Edit
                    </button>
                </div>
            </form>
        </div>

        <!-- Recent Liquidations -->
        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-100">
            <h3 class="text-lg font-medium mb-4 text-gray-800 border-b pb-2">Recent Liquidations</h3>
            <div class="overflow-y-auto max-h-[500px]" id="recentList">
                <div class="text-center text-gray-400 py-8">Loading recent records...</div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    
    // Load recent liquidations on page load
    loadRecentLiquidations();

    // Autocomplete for Member Search
    $("#CoopName").autocomplete({
        source: "search_members.php",
        minLength: 2,
        select: function(event, ui) {
            $('#member_id').val(ui.item.memberid);
            $('#CoopName').val(ui.item.label);
            $('#memberDetailHelp').text(ui.item.membername + (ui.item.mobile ? ' (' + ui.item.mobile + ')' : ''));
            fetchLoanBalance(ui.item.memberid);
            return false;
        }
    }).autocomplete("instance")._renderItem = function(ul, item) {
        return $("<li>")
            .append("<div>" + item.label + "<br><span style='font-size:0.9em;color:#888'>" + item.membername + "</span></div>")
            .appendTo(ul);
    };

    // Clear Member
    $('#clearMemberBtn').on('click', function() {
        resetForm();
    });
    
    // Cancel Edit
    $('#cancelEditBtn').on('click', function() {
        resetForm();
    });

    // Handle Edit Click
    $(document).on('click', '.edit-btn', function() {
        const data = $(this).data('liquidation');
        
        // Populate form
        $('#formAction').val('update');
        $('#propertyId').val(data.property_id);
        $('#member_id').val(data.memberid);
        $('#CoopName').val(data.Lname + ' ' + data.Fname);
        $('#memberDetailHelp').text(data.Lname + ' ' + data.Fname + (data.mobile ? ' (' + data.mobile + ')' : ''));
        $('#period_id').val(data.periodid); // Ensure periodid is returned by backend
        $('#property_name').val(data.property_name);
        $('#property_description').val(data.property_description);
        $('#property_value').val(data.property_value);
        
        // UI Updates
        $('.bg-white h3').first().text('Edit Liquidation Entry');
        $('#submitBtn').text('Update Liquidation');
        $('#cancelEditBtn').removeClass('hidden');
        $('#loanStatusArea').addClass('hidden'); // Hide balance during edit as it might be confusing or need re-fetch
        
        // Scroll to form
        $('html, body').animate({
            scrollTop: $("#liquidationForm").offset().top - 100
        }, 500);
    });

    // Handle Delete Click
    $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "This will delete the property record AND the loan repayment transaction. This cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('process_property_liquidation.php', { action: 'delete', property_id: id }, function(response) {
                    if(response.success) {
                        Swal.fire('Deleted!', response.message, 'success');
                        if($('#propertyId').val() == id) resetForm(); // If deleting currently editing item
                        loadRecentLiquidations();
                    } else {
                        Swal.fire('Error', response.error, 'error');
                    }
                }, 'json').fail(function() {
                    Swal.fire('Error', 'Server communication failed', 'error');
                });
            }
        });
    });

    function resetForm() {
        $('#liquidationForm')[0].reset();
        $('#formAction').val('create');
        $('#propertyId').val('0');
        $('#member_id').val('');
        $('#memberDetailHelp').text('');
        $('#loanStatusArea').addClass('hidden');
        
        $('.bg-white h3').first().text('New Liquidation Entry');
        $('#submitBtn').text('Process Liquidation');
        $('#cancelEditBtn').addClass('hidden');
    }

    // Form Submission
    $('#liquidationForm').on('submit', function(e) {
        e.preventDefault();
        
        const memberId = $('#member_id').val();
        if(!memberId) {
            Swal.fire('Error', 'Please select a valid member first.', 'error');
            return;
        }

        const formData = $(this).serialize();
        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.text();

        submitBtn.prop('disabled', true).text('Processing...');

        $.ajax({
            url: 'process_property_liquidation.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#7e22ce'
                    });
                    
                    resetForm();
                    loadRecentLiquidations();
                } else {
                    Swal.fire('Error', response.error || 'An unknown error occurred.', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to communicate with the server.', 'error');
            },
            complete: function() {
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });

    // Helper: Fetch Loan Balance
    function fetchLoanBalance(memberId) {
        $('#loanStatusArea').removeClass('hidden');
        $('#currentLoanBalance').text('Loading...');
        
        $.get('api_get_loan_balance.php', { id: memberId }, function(data) {
            let balance = 0;
            if(data && typeof data.balance !== 'undefined') {
                balance = parseFloat(data.balance);
            }
            
            const formattedBalance = '₦' + balance.toLocaleString('en-US', {minimumFractionDigits: 2});
            $('#currentLoanBalance').text(formattedBalance);
        }).fail(function() {
            $('#currentLoanBalance').text('Error fetching balance');
        });
    }

    // Helper: Load Recent Liquidations
    function loadRecentLiquidations() {
        $.get('process_property_liquidation.php?action=get_recent', function(data) {
            $('#recentList').html(data);
        });
    }
});
</script>

<?php require_once('footer.php'); ?>
