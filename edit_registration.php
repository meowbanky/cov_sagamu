<?php
include('header.php');
require_once('Connections/cov.php');
mysqli_select_db($cov, $database_cov);

// Initialize state query
$query_state2 = "SELECT * FROM state_nigeria";
$state2 = mysqli_query($cov, $query_state2) or die(mysqli_error($cov));
$row_state2 = mysqli_fetch_assoc($state2);
$totalRows_state2 = mysqli_num_rows($state2);

// Initialize edit records query
$col_editRecords = "-1";
if (isset($_GET['memberid'])) {
    $col_editRecords = mysqli_real_escape_string($cov, $_GET['memberid']);
}

$query_editRecords = "SELECT 
    tbl_personalinfo.memberid, 
    tbl_personalinfo.sfxname, 
    tbl_personalinfo.Fname, 
    tbl_personalinfo.Mname, 
    tbl_personalinfo.passport,
    tbl_personalinfo.Lname,
    tbl_personalinfo.interest, 
    tbl_personalinfo.MaidenName, 
    tbl_personalinfo.Mothersname, 
    tbl_personalinfo.gender, 
    tbl_personalinfo.bloodGroup, 
    tbl_personalinfo.Status, 
    tbl_personalinfo.DOB, 
    tbl_personalinfo.Address, 
    tbl_personalinfo.Address2, 
    tbl_personalinfo.State, 
    tbl_personalinfo.City, 
    tbl_personalinfo.countryOrigin, 
    tbl_personalinfo.StateOfOrigin, 
    tbl_personalinfo.Tribe, 
    tbl_personalinfo.EducationLevel, 
    tbl_personalinfo.Occupation, 
    tbl_personalinfo.Religion, 
    tbl_personalinfo.MobilePhone, 
    tbl_personalinfo.EmailAddress, 
    tbl_personalinfo.DateOfReg, 
    tbl_nok.NOkName, 
    tbl_nok.NOKRelationship, 
    tbl_nok.NOKPhone, 
    tbl_nok.NOKAddress 
FROM tbl_personalinfo 
LEFT JOIN tbl_nok ON tbl_nok.memberid = tbl_personalinfo.memberid 
WHERE tbl_personalinfo.memberid = '" . $col_editRecords . "'";

// Initialize default values first
$row_editRecords = array(
    'memberid' => '',
    'sfxname' => '',
    'Fname' => '',
    'Mname' => '',
    'passport' => '',
    'Lname' => '',
    'interest' => '',
    'MaidenName' => '',
    'Mothersname' => '',
    'gender' => '',
    'bloodGroup' => '',
    'Status' => '',
    'DOB' => '',
    'Address' => '',
    'Address2' => '',
    'State' => '',
    'City' => '',
    'countryOrigin' => '',
    'StateOfOrigin' => '',
    'Tribe' => '',
    'EducationLevel' => '',
    'Occupation' => '',
    'Religion' => '',
    'MobilePhone' => '',
    'EmailAddress' => '',
    'DateOfReg' => '',
    'NOkName' => '',
    'NOKRelationship' => '',
    'NOKPhone' => '',
    'NOKAddress' => ''
);

$editRecords = mysqli_query($cov, $query_editRecords) or die(mysqli_error($cov));
$totalRows_editRecords = mysqli_num_rows($editRecords);

// Override with actual data if record found
if ($totalRows_editRecords > 0) {
    $fetched = mysqli_fetch_assoc($editRecords);
    if ($fetched && is_array($fetched)) {
        // Merge with defaults to ensure all keys exist
        $row_editRecords = array_merge($row_editRecords, $fetched);
    }
    // If fetch fails or returns null, keep the default values
}

// Reset state query pointer
if ($totalRows_state2 > 0) {
    mysqli_data_seek($state2, 0);
    $row_state2 = mysqli_fetch_assoc($state2);
}

// Form action (you may need to adjust this based on your actual form handler)
$editFormAction = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : 'edit_registration_action.php';
?>
<div class="max-w-2xl mx-auto py-10">
    <form action="<?php echo $editFormAction; ?>" method="POST" name="eduEntry" onSubmit="return(validate());"
        autocomplete="off">
        <div class="bg-white rounded-2xl shadow-lg p-8 space-y-8">
            <h2 class="text-2xl font-bold text-blue-700 mb-4">Personal Information</h2>

            <!-- Membership No (readonly) -->
            <div>
                <label for="new_mrn" class="block text-sm font-semibold text-gray-700 mb-1">Membership No:</label>
                <input name="new_mrn" id="new_mrn" type="text"
                    value="<?php echo htmlspecialchars($row_editRecords['memberid'] ?? ''); ?>" readonly
                    class="w-full bg-gray-100 rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" />
                <input name="initial_mrn" type="hidden"
                    value="<?php echo htmlspecialchars($row_editRecords['memberid'] ?? ''); ?>">
            </div>

            <!-- Title -->
            <div>
                <label for="sfxname" class="block text-sm font-semibold text-gray-700 mb-1">Title <span
                        class="text-red-500">*</span></label>
                <select name="sfxname" id="sfxname"
                    class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                    <option value="na" <?php if (!strcmp("na", $row_editRecords['sfxname'] ?? '')) echo 'selected'; ?>>
                        -Select-</option>
                    <option value="Mr" <?php if (!strcmp("Mr", $row_editRecords['sfxname'] ?? '')) echo 'selected'; ?>>
                        Mr</option>
                    <option value="Miss"
                        <?php if (!strcmp("Miss", $row_editRecords['sfxname'] ?? '')) echo 'selected'; ?>>Miss</option>
                    <option value="Mrs"
                        <?php if (!strcmp("Mrs", $row_editRecords['sfxname'] ?? '')) echo 'selected'; ?>>Mrs</option>
                    <option value="Dr" <?php if (!strcmp("Dr", $row_editRecords['sfxname'] ?? '')) echo 'selected'; ?>>
                        Dr</option>
                    <option value="Baby"
                        <?php if (!strcmp("Baby", $row_editRecords['sfxname'] ?? '')) echo 'selected'; ?>>Baby</option>
                    <option value="Master"
                        <?php if (!strcmp("Master", $row_editRecords['sfxname'] ?? '')) echo 'selected'; ?>>Master
                    </option>
                </select>
            </div>

            <!-- Passport Photo -->
            <div class="flex flex-col items-center">
                <img src="<?php echo htmlspecialchars($row_editRecords['passport'] ?? ''); ?>" alt="passport"
                    class="w-32 h-32 rounded-xl object-cover border" />
            </div>

            <!-- Name Fields -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="Fname" class="block text-sm font-semibold text-gray-700 mb-1">First Name <span
                            class="text-red-500">*</span></label>
                    <input name="Fname" id="Fname" type="text"
                        value="<?php echo htmlspecialchars($row_editRecords['Fname'] ?? ''); ?>"
                        class="w-full rounded-lg border-gray-300 px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>
                <div>
                    <label for="Mname" class="block text-sm font-semibold text-gray-700 mb-1">Middle Name</label>
                    <input name="Mname" id="Mname" type="text"
                        value="<?php echo htmlspecialchars($row_editRecords['Mname'] ?? ''); ?>"
                        class="w-full rounded-lg border-gray-300 px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>
                <div>
                    <label for="Lname" class="block text-sm font-semibold text-gray-700 mb-1">Last Name <span
                            class="text-red-500">*</span></label>
                    <input name="Lname" id="Lname" type="text"
                        value="<?php echo htmlspecialchars($row_editRecords['Lname'] ?? ''); ?>"
                        class="w-full rounded-lg border-gray-300 px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>
            </div>

            <!-- Gender -->
            <div>
                <span class="block text-sm font-semibold text-gray-700 mb-1">Gender <span
                        class="text-red-500">*</span></span>
                <div class="flex gap-6">
                    <label class="inline-flex items-center">
                        <input type="radio" name="gender" value="Male"
                            <?php if(($row_editRecords['gender'] ?? '')=="Male") echo 'checked'; ?>
                            class="form-radio text-blue-600">
                        <span class="ml-2">Male</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="gender" value="Female"
                            <?php if(($row_editRecords['gender'] ?? '')=="Female") echo 'checked'; ?>
                            class="form-radio text-blue-600">
                        <span class="ml-2">Female</span>
                    </label>
                </div>
            </div>

            <!-- Date of Birth -->
            <div>
                <label for="DOB" class="block text-sm font-semibold text-gray-700 mb-1">Date of Birth <span
                        class="text-red-500">*</span></label>
                <input name="DOB" id="DOB" type="text"
                    value="<?php echo htmlspecialchars($row_editRecords['DOB'] ?? ''); ?>" readonly
                    class="w-full rounded-lg border-gray-300 px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
                <!-- You can use a JS datepicker here if you want -->
            </div>

            <!-- Address Fields (House No., Address2, City, State, Mobile, Email) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="Address" class="block text-sm font-semibold text-gray-700 mb-1">House No. <span
                            class="text-red-500">*</span></label>
                    <input name="Address" id="Address" type="text"
                        value="<?php echo htmlspecialchars($row_editRecords['Address'] ?? ''); ?>"
                        class="w-full rounded-lg border-gray-300 px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>
                <div>
                    <label for="Address2" class="block text-sm font-semibold text-gray-700 mb-1">Address 2</label>
                    <input name="Address2" id="Address2" type="text"
                        value="<?php echo htmlspecialchars($row_editRecords['Address2'] ?? ''); ?>"
                        class="w-full rounded-lg border-gray-300 px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>
                <div>
                    <label for="City" class="block text-sm font-semibold text-gray-700 mb-1">City <span
                            class="text-red-500">*</span></label>
                    <input name="City" id="City" type="text"
                        value="<?php echo htmlspecialchars($row_editRecords['City'] ?? ''); ?>"
                        class="w-full rounded-lg border-gray-300 px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>
                <div>
                    <label for="State" class="block text-sm font-semibold text-gray-700 mb-1">State <span
                            class="text-red-500">*</span></label>
                    <select name="State" id="State"
                        class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select State ...</option>
                        <?php 
            if ($totalRows_state2 > 0) {
                do { 
                    $selected = (!empty($row_editRecords['State']) && isset($row_state2['State']) && !strcmp($row_state2['State'], $row_editRecords['State'])) ? 'selected' : '';
            ?>
                        <option value="<?php echo htmlspecialchars($row_state2['State'] ?? ''); ?>"
                            <?php echo $selected; ?>><?php echo htmlspecialchars($row_state2['State'] ?? ''); ?>
                        </option>
                        <?php 
                } while ($row_state2 = mysqli_fetch_assoc($state2)); 
            }
            ?>
                    </select>
                </div>
                <div>
                    <label for="MobilePhone" class="block text-sm font-semibold text-gray-700 mb-1">Mobile Phone <span
                            class="text-red-500">*</span></label>
                    <input name="MobilePhone" id="MobilePhone" type="text"
                        value="<?php echo htmlspecialchars($row_editRecords['MobilePhone'] ?? ''); ?>"
                        class="w-full rounded-lg border-gray-300 px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>
                <div>
                    <label for="EmailAddress" class="block text-sm font-semibold text-gray-700 mb-1">E-mail
                        Address</label>
                    <input name="EmailAddress" id="EmailAddress" type="email"
                        value="<?php echo htmlspecialchars($row_editRecords['EmailAddress'] ?? ''); ?>"
                        class="w-full rounded-lg border-gray-300 px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>
            </div>

            <!-- Status & Charge Interest -->
            <div class="flex flex-wrap gap-8">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="status" id="status"
                        <?php if (!strcmp($row_editRecords['Status'] ?? '',"Active")) echo "checked"; ?>
                        class="form-checkbox text-blue-600">
                    <span class="ml-2">Active</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="interest" id="interest"
                        <?php if (!strcmp($row_editRecords['interest'] ?? '',"1")) echo "checked"; ?>
                        class="form-checkbox text-blue-600">
                    <span class="ml-2">Charge Interest?</span>
                </label>
            </div>

            <!-- Submit Button -->
            <div class="text-center pt-4">
                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded-lg shadow transition">
                    Save
                </button>
            </div>
        </div>
    </form>
</div>
<?php include('footer.php');?>