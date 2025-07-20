<?php
require_once('Connections/cov.php');
session_start();
if (!isset($_SESSION['UserID'])) header("Location:index.php");
require_once('header.php');

// Get member ID from query
$memberId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$memberId) {
    echo '<div class="max-w-xl mx-auto mt-10 text-red-600 font-bold">No member selected for editing.</div>';
    require_once('footer.php');
    exit;
}

// Fetch member data
$query_member = "SELECT * FROM tbl_personalinfo WHERE memberid = $memberId";
$row_member = mysqli_fetch_assoc(mysqli_query($cov, $query_member));
if (!$row_member) {
    echo '<div class="max-w-xl mx-auto mt-10 text-red-600 font-bold">Member not found.</div>';
    require_once('footer.php');
    exit;
}

// Fetch NOK data from separate table
$query_nok = "SELECT * FROM tbl_nok WHERE memberid = '$memberId'";
$row_nok = mysqli_fetch_assoc(mysqli_query($cov, $query_nok));

// Fetch all dropdown data
$query_state2 = "SELECT * FROM state_nigeria";
$states = mysqli_query($cov, $query_state2);
$query_nokRelationship = "SELECT relationship FROM nok_relationship";
$nokRels = mysqli_query($cov, $query_nokRelationship);
?>
<div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg mt-8 mb-16 p-6 md:p-10">
    <form id="editForm" class="grid grid-cols-1 md:grid-cols-2 gap-6" autocomplete="off">
        <div class="space-y-4">
            <div>
                <label class="block font-semibold mb-1">Coop No:</label>
                <input name="memberid" id="memberid" type="text" class="w-full border px-3 py-2 rounded" readonly
                    value="<?= htmlspecialchars($row_member['memberid']) ?>">
            </div>
            <div>
                <label class="block font-semibold mb-1">Title<span class="text-red-500">*</span></label>
                <select name="sfxname" id="sfxname" class="w-full border px-3 py-2 rounded" required>
                    <option value="">-Select-</option>
                    <?php $titles = ['Mr','Miss','Mrs','Dr','Baby','Master'];
                    foreach($titles as $t): ?>
                    <option value="<?= $t ?>" <?= ($row_member['sfxname']==$t)?'selected':'' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1">First Name<span class="text-red-500">*</span></label>
                <input name="Fname" id="Fname" type="text" class="w-full border px-3 py-2 rounded" required
                    value="<?= htmlspecialchars($row_member['Fname']) ?>">
            </div>
            <div>
                <label class="block font-semibold mb-1">Middle Name</label>
                <input name="Mname" id="Mname" type="text" class="w-full border px-3 py-2 rounded"
                    value="<?= htmlspecialchars($row_member['Mname']) ?>">
            </div>
            <div>
                <label class="block font-semibold mb-1">Last Name<span class="text-red-500">*</span></label>
                <input name="Lname" id="Lname" type="text" class="w-full border px-3 py-2 rounded" required
                    value="<?= htmlspecialchars($row_member['Lname']) ?>">
            </div>
            <div>
                <label class="block font-semibold mb-1">Gender<span class="text-red-500">*</span></label>
                <select name="gender" id="gender" class="w-full border px-3 py-2 rounded" required>
                    <option value="Male" <?= ($row_member['gender']=='Male')?'selected':'' ?>>Male</option>
                    <option value="Female" <?= ($row_member['gender']=='Female')?'selected':'' ?>>Female</option>
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1">Date of Birth</label>
                <input name="DOB" id="DOB" type="date" class="w-full border px-3 py-2 rounded"
                    value="<?= htmlspecialchars($row_member['DOB']) ?>">
            </div>
            <div>
                <label class="block font-semibold mb-1">House No.<span class="text-red-500">*</span></label>
                <input name="Address" id="Address" type="text" class="w-full border px-3 py-2 rounded" required
                    value="<?= htmlspecialchars($row_member['Address']) ?>">
            </div>
            <div>
                <label class="block font-semibold mb-1">Address 2</label>
                <input name="Address2" id="Address2" type="text" class="w-full border px-3 py-2 rounded"
                    value="<?= htmlspecialchars($row_member['Address2']) ?>">
            </div>
            <div>
                <label class="block font-semibold mb-1">City<span class="text-red-500">*</span></label>
                <input name="City" id="City" type="text" class="w-full border px-3 py-2 rounded" required
                    value="<?= htmlspecialchars($row_member['City']) ?>">
            </div>
            <div>
                <label class="block font-semibold mb-1">State<span class="text-red-500">*</span></label>
                <select name="State" id="State" class="w-full border px-3 py-2 rounded" required>
                    <option value="">Select State...</option>
                    <?php while($row = mysqli_fetch_assoc($states)): ?>
                    <option value="<?= htmlspecialchars($row['State']) ?>"
                        <?= ($row_member['State']==$row['State'])?'selected':'' ?>>
                        <?= htmlspecialchars($row['State']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1">Mobile Phone<span class="text-red-500">*</span></label>
                <input name="MobilePhone" id="MobilePhone" type="text" class="w-full border px-3 py-2 rounded" required
                    value="<?= htmlspecialchars($row_member['MobilePhone']) ?>">
            </div>
            <div>
                <label class="block font-semibold mb-1">Email Address</label>
                <input name="EmailAddress" id="EmailAddress" type="email" class="w-full border px-3 py-2 rounded"
                    value="<?= htmlspecialchars($row_member['EmailAddress']) ?>">
            </div>
            <div>
                <label class="block font-semibold mb-1">Status</label>
                <input name="status" id="status" type="checkbox" value="Active"
                    <?= ($row_member['Status']=='Active')?'checked':'' ?> class="mr-2">Active
            </div>
            <div>
                <label class="block font-semibold mb-1">Interest Waiver</label>
                <div class="flex items-center">
                    <input name="interest" id="interest" type="checkbox" value="1"
                        <?= ($row_member['interest']==1)?'checked':'' ?> class="mr-2">
                    <span class="text-sm text-gray-600">Enable interest calculation</span>
                </div>
            </div>
        </div>
        <div class="space-y-4">
            <fieldset class="border rounded p-4">
                <legend class="font-bold text-blue-900 mb-2">Next of Kin</legend>
                <div>
                    <label class="block font-semibold mb-1">Name<span class="text-red-500">*</span></label>
                    <input name="NOkName" id="NOkName" type="text" class="w-full border px-3 py-2 rounded" required
                        value="<?= htmlspecialchars($row_nok['NOkName'] ?? '') ?>">
                </div>
                <div>
                    <label class="block font-semibold mb-1">Relationship<span class="text-red-500">*</span></label>
                    <select name="NOKRelationship" id="NOKRelationship" class="w-full border px-3 py-2 rounded"
                        required>
                        <option value="">Select...</option>
                        <?php while($rel = mysqli_fetch_assoc($nokRels)): ?>
                        <option value="<?= htmlspecialchars($rel['relationship']) ?>"
                            <?= (($row_nok['NOKRelationship'] ?? '')==$rel['relationship'])?'selected':'' ?>>
                            <?= htmlspecialchars($rel['relationship']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold mb-1">Phone No<span class="text-red-500">*</span></label>
                    <input name="NOKPhone" id="NOKPhone" type="text" class="w-full border px-3 py-2 rounded" required
                        value="<?= htmlspecialchars($row_nok['NOKPhone'] ?? '') ?>">
                </div>
                <div>
                    <label class="block font-semibold mb-1">Address<span class="text-red-500">*</span></label>
                    <input name="NOKAddress" id="NOKAddress" type="text" class="w-full border px-3 py-2 rounded"
                        required value="<?= htmlspecialchars($row_nok['NOKAddress'] ?? '') ?>">
                    <label class="inline-flex items-center mt-2"><input type="checkbox" id="sameAsAbove" class="mr-2">
                        Same as above</label>
                </div>
            </fieldset>
            <fieldset class="border rounded p-4 mt-4">
                <legend class="font-bold text-blue-900 mb-2">Reset User's Password</legend>
                <div class="flex gap-2">
                    <input name="passwordGen" id="passwordGen" type="text" class="w-full border px-3 py-2 rounded"
                        readonly>
                    <button type="button" id="generateBtn"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Generate
                    </button>
                </div>
            </fieldset>
            <div class="flex items-center mt-5">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded">
                    Save Changes
                </button>
            </div>
        </div>
    </form>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$('#generateBtn').click(function() {
    let chars = "abcdefghijklmnopqrstuvwxyz!@#$%^&*()-+<>ABCDEFGHIJKLMNOP1234567890";
    let pass = "";
    for (let i = 0; i < 8; i++) pass += chars.charAt(Math.floor(Math.random() * chars.length));
    $('#passwordGen').val(pass);
});
$('#sameAsAbove').change(function() {
    if (this.checked) $('#NOKAddress').val($('#Address').val());
});
$('#editForm').submit(function(e) {
    e.preventDefault();
    let formData = $(this).serialize();
    $.post('edit_member_action.php', formData, function(resp) {
        if (resp.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: resp.success
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: resp.error
            });
        }
    }, 'json').fail(function() {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Request failed. Please check your connection.'
        });
    });
});
</script>
<?php require_once('footer.php'); ?>