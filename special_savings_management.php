<?php
session_start();
if (!isset($_SESSION['UserID'])) {
    header("Location: index.php");
    exit();
}
require_once('Connections/cov.php');
require_once('header.php');

mysqli_select_db($cov, $database_cov);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $memberid = intval($_POST['memberid']);
            $amount = floatval($_POST['amount']);
            $notes = mysqli_real_escape_string($cov, $_POST['notes']);
            $added_by = $_SESSION['FirstName'] ?? 'Admin';
            
            // Check if member already exists
            $check_query = "SELECT id FROM tbl_special_savings WHERE memberid = $memberid";
            $check_result = mysqli_query($cov, $check_query);
            
            if (mysqli_num_rows($check_result) > 0) {
                $error_message = "Member already exists in special savings list!";
            } else {
                $insert_query = "INSERT INTO tbl_special_savings (memberid, special_savings_amount, notes, added_by) VALUES ($memberid, $amount, '$notes', '$added_by')";
                if (mysqli_query($cov, $insert_query)) {
                    $success_message = "Member added to special savings successfully!";
                } else {
                    $error_message = "Error adding member: " . mysqli_error($cov);
                }
            }
        }
        
        if ($_POST['action'] == 'delete') {
            $id = intval($_POST['id']);
            $delete_query = "DELETE FROM tbl_special_savings WHERE id = $id";
            if (mysqli_query($cov, $delete_query)) {
                $success_message = "Member removed from special savings successfully!";
            } else {
                $error_message = "Error removing member: " . mysqli_error($cov);
            }
        }
        
        if ($_POST['action'] == 'update') {
            $id = intval($_POST['id']);
            $amount = floatval($_POST['amount']);
            $notes = mysqli_real_escape_string($cov, $_POST['notes']);
            
            $update_query = "UPDATE tbl_special_savings SET special_savings_amount = $amount, notes = '$notes' WHERE id = $id";
            if (mysqli_query($cov, $update_query)) {
                $success_message = "Special savings updated successfully!";
            } else {
                $error_message = "Error updating record: " . mysqli_error($cov);
            }
        }
    }
}

// Fetch current special savings members
$query_special_savings = "
    SELECT ss.*, 
           CONCAT(p.Lname, ', ', p.Fname, ' ', IFNULL(p.Mname, '')) as member_name,
           p.memberid
    FROM tbl_special_savings ss
    INNER JOIN tbl_personalinfo p ON ss.memberid = p.memberid
    WHERE ss.status = 'active'
    ORDER BY ss.date_added DESC
";
$result_special_savings = mysqli_query($cov, $query_special_savings);
?>

<div class="max-w-7xl mx-auto bg-white rounded-xl shadow-lg mt-8 mb-16 p-6 md:p-10">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-blue-900 flex items-center gap-2">
            <i class="fa fa-star text-yellow-500"></i>
            Special Savings Management
        </h1>
        <a href="dashboard.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold">
            <i class="fa fa-arrow-left mr-2"></i>Back to Dashboard
        </a>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($success_message)): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <i class="fa fa-check-circle mr-2"></i><?= htmlspecialchars($success_message) ?>
    </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <i class="fa fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error_message) ?>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Add New Member Form -->
        <div class="bg-gray-50 rounded-lg p-6">
            <h2 class="text-xl font-bold text-blue-900 mb-4 flex items-center gap-2">
                <i class="fa fa-plus-circle text-green-600"></i>
                Add Member to Special Savings
            </h2>

            <form id="addSpecialSavingsForm" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add">

                <div>
                    <label class="block font-semibold mb-2">Select Member</label>
                    <input type="text" id="memberSearch" class="w-full border px-3 py-2 rounded-lg"
                        placeholder="Type member name or ID..." autocomplete="off">
                    <div id="memberSuggestions" class="bg-white border rounded-lg mt-1 max-h-40 overflow-y-auto"
                        style="display:none;">
                        <div id="memberSuggestionsList"></div>
                    </div>
                    <input type="hidden" name="memberid" id="selectedMemberId" required>
                    <div id="selectedMemberInfo" class="mt-2 p-2 bg-blue-50 rounded hidden">
                        <span class="text-blue-800 font-semibold"></span>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold mb-2">Special Savings Amount</label>
                    <input type="number" name="amount" step="0.01" min="0" class="w-full border px-3 py-2 rounded-lg"
                        placeholder="0.00" required>
                </div>

                <div>
                    <label class="block font-semibold mb-2">Notes (Optional)</label>
                    <textarea name="notes" rows="3" class="w-full border px-3 py-2 rounded-lg"
                        placeholder="Additional notes..."></textarea>
                </div>

                <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg">
                    <i class="fa fa-plus mr-2"></i>Add to Special Savings
                </button>
            </form>
        </div>

        <!-- Current Special Savings Members -->
        <div class="bg-gray-50 rounded-lg p-6">
            <h2 class="text-xl font-bold text-blue-900 mb-4 flex items-center gap-2">
                <i class="fa fa-list text-blue-600"></i>
                Current Special Savings Members (<?= mysqli_num_rows($result_special_savings) ?>)
            </h2>

            <div class="max-h-96 overflow-y-auto">
                <?php if (mysqli_num_rows($result_special_savings) > 0): ?>
                <div class="space-y-3">
                    <?php while ($row = mysqli_fetch_assoc($result_special_savings)): ?>
                    <div class="bg-white rounded-lg p-4 border border-gray-200 hover:shadow-md transition">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-800"><?= htmlspecialchars($row['member_name']) ?></h3>
                                <p class="text-sm text-gray-600">ID: <?= $row['memberid'] ?></p>
                                <p class="text-lg font-semibold text-green-600">
                                    ₦<?= number_format($row['special_savings_amount'], 2) ?>
                                </p>
                                <?php if ($row['notes']): ?>
                                <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($row['notes']) ?></p>
                                <?php endif; ?>
                                <p class="text-xs text-gray-400 mt-1">
                                    Added: <?= date('M j, Y', strtotime($row['date_added'])) ?> by
                                    <?= htmlspecialchars($row['added_by']) ?>
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <button
                                    onclick="editSpecialSavings(<?= $row['id'] ?>, '<?= htmlspecialchars($row['member_name']) ?>', <?= $row['special_savings_amount'] ?>, '<?= htmlspecialchars($row['notes']) ?>')"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <form method="POST" class="inline"
                                    onsubmit="return confirm('Are you sure you want to remove this member from special savings?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="submit"
                                        class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-8 text-gray-500">
                    <i class="fa fa-inbox text-4xl mb-4"></i>
                    <p>No members in special savings yet.</p>
                    <p class="text-sm">Add members using the form on the left.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-bold mb-4">Edit Special Savings</h3>
            <form id="editForm" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="editId">

                <div class="mb-4">
                    <label class="block font-semibold mb-2">Member</label>
                    <input type="text" id="editMemberName" class="w-full border px-3 py-2 rounded-lg" readonly>
                </div>

                <div class="mb-4">
                    <label class="block font-semibold mb-2">Special Savings Amount</label>
                    <input type="number" name="amount" id="editAmount" step="0.01" min="0"
                        class="w-full border px-3 py-2 rounded-lg" required>
                </div>

                <div class="mb-4">
                    <label class="block font-semibold mb-2">Notes</label>
                    <textarea name="notes" id="editNotes" rows="3"
                        class="w-full border px-3 py-2 rounded-lg"></textarea>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg">
                        Update
                    </button>
                    <button type="button" onclick="closeEditModal()"
                        class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Member search functionality
$('#memberSearch').keyup(function() {
    let query = $(this).val();
    if (query.length < 2) {
        $('#memberSuggestions').hide();
        return;
    }

    $.post('api/member_search.php', {
        q: query
    }, function(data) {
        if (data.length > 0) {
            $('#memberSuggestions').show();
            $('#memberSuggestionsList').html(data);
        } else {
            $('#memberSuggestions').hide();
        }
    });
});

// Handle member selection
$(document).on('click', '.suggestionList li', function() {
    let memberId = $(this).attr('data-id');
    let memberName = $(this).text();

    $('#selectedMemberId').val(memberId);
    $('#memberSearch').val(memberName);
    $('#memberSuggestions').hide();
    $('#selectedMemberInfo span').text(memberName);
    $('#selectedMemberInfo').removeClass('hidden');
});

// Edit modal functions
function editSpecialSavings(id, memberName, amount, notes) {
    $('#editId').val(id);
    $('#editMemberName').val(memberName);
    $('#editAmount').val(amount);
    $('#editNotes').val(notes);
    $('#editModal').removeClass('hidden');
}

function closeEditModal() {
    $('#editModal').addClass('hidden');
}

// Close modal when clicking outside
$('#editModal').click(function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});
</script>

<?php require_once('footer.php'); ?>