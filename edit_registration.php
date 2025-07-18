<?php
include('header.php');
require_once('Connections/cov.php');
?>
<div class="max-w-2xl mx-auto py-10">
  <form action="<?php echo $editFormAction; ?>" method="POST" name="eduEntry" onSubmit="return(validate());" autocomplete="off">
    <div class="bg-white rounded-2xl shadow-lg p-8 space-y-8">
      <h2 class="text-2xl font-bold text-blue-700 mb-4">Personal Information</h2>
      
      <!-- Membership No (readonly) -->
      <div>
        <label for="new_mrn" class="block text-sm font-semibold text-gray-700 mb-1">Membership No:</label>
        <input name="new_mrn" id="new_mrn" type="text" value="<?php echo $row_editRecords['memberid']; ?>" readonly
               class="w-full bg-gray-100 rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" />
        <input name="initial_mrn" type="hidden" value="<?php echo $row_editRecords['memberid']; ?>">
      </div>
      
      <!-- Title -->
      <div>
        <label for="sfxname" class="block text-sm font-semibold text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
        <select name="sfxname" id="sfxname" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
          <option value="na" <?php if (!(strcmp("na", $row_editRecords['sfxname']))) echo 'selected'; ?>>-Select-</option>
          <option value="Mr" <?php if (!(strcmp("Mr", $row_editRecords['sfxname']))) echo 'selected'; ?>>Mr</option>
          <option value="Miss" <?php if (!(strcmp("Miss", $row_editRecords['sfxname']))) echo 'selected'; ?>>Miss</option>
          <option value="Mrs" <?php if (!(strcmp("Mrs", $row_editRecords['sfxname']))) echo 'selected'; ?>>Mrs</option>
          <option value="Dr" <?php if (!(strcmp("Dr", $row_editRecords['sfxname']))) echo 'selected'; ?>>Dr</option>
          <option value="Baby" <?php if (!(strcmp("Baby", $row_editRecords['sfxname']))) echo 'selected'; ?>>Baby</option>
          <option value="Master" <?php if (!(strcmp("Master", $row_editRecords['sfxname']))) echo 'selected'; ?>>Master</option>
        </select>
      </div>

      <!-- Passport Photo -->
      <div class="flex flex-col items-center">
        <img src="<?php echo $row_editRecords['passport']; ?>" alt="passport" class="w-32 h-32 rounded-xl object-cover border" />
      </div>

      <!-- Name Fields -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label for="Fname" class="block text-sm font-semibold text-gray-700 mb-1">First Name <span class="text-red-500">*</span></label>
          <input name="Fname" id="Fname" type="text" value="<?php echo $row_editRecords['Fname']; ?>"
                 class="w-full rounded-lg border-gray-300 px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
        </div>
        <div>
          <label for="Mname" class="block text-sm font-semibold text-gray-700 mb-1">Middle Name</label>
          <input name="Mname" id="Mname" type="text" value="<?php echo $row_editRecords['Mname']; ?>"
                 class="w-full rounded-lg border-gray-300 px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
        </div>
        <div>
          <label for="Lname" class="block text-sm font-semibold text-gray-700 mb-1">Last Name <span class="text-red-500">*</span></label>
          <input name="Lname" id="Lname" type="text" value="<?php echo $row_editRecords['Lname']; ?>"
                 class="w-full rounded-lg border-gray-300 px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
        </div>
      </div>

      <!-- Gender -->
      <div>
        <span class="block text-sm font-semibold text-gray-700 mb-1">Gender <span class="text-red-500">*</span></span>
        <div class="flex gap-6">
          <label class="inline-flex items-center">
            <input type="radio" name="gender" value="Male" <?php if($row_editRecords['gender']=="Male") echo 'checked'; ?> class="form-radio text-blue-600">
            <span class="ml-2">Male</span>
          </label>
          <label class="inline-flex items-center">
            <input type="radio" name="gender" value="Female" <?php if($row_editRecords['gender']=="Female") echo 'checked'; ?> class="form-radio text-blue-600">
            <span class="ml-2">Female</span>
          </label>
        </div>
      </div>

      <!-- Date of Birth -->
      <div>
        <label for="DOB" class="block text-sm font-semibold text-gray-700 mb-1">Date of Birth <span class="text-red-500">*</span></label>
        <input name="DOB" id="DOB" type="text" value="<?php echo $row_editRecords['DOB']; ?>" readonly
               class="w-full rounded-lg border-gray-300 px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
        <!-- You can use a JS datepicker here if you want -->
      </div>

      <!-- Address Fields (House No., Address2, City, State, Mobile, Email) -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="Address" class="block text-sm font-semibold text-gray-700 mb-1">House No. <span class="text-red-500">*</span></label>
          <input name="Address" id="Address" type="text" value="<?php echo $row_editRecords['Address']; ?>"
                 class="w-full rounded-lg border-gray-300 px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
        </div>
        <div>
          <label for="Address2" class="block text-sm font-semibold text-gray-700 mb-1">Address 2</label>
          <input name="Address2" id="Address2" type="text" value="<?php echo $row_editRecords['Address2']; ?>"
                 class="w-full rounded-lg border-gray-300 px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
        </div>
        <div>
          <label for="City" class="block text-sm font-semibold text-gray-700 mb-1">City <span class="text-red-500">*</span></label>
          <input name="City" id="City" type="text" value="<?php echo $row_editRecords['City']; ?>"
                 class="w-full rounded-lg border-gray-300 px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
        </div>
        <div>
          <label for="State" class="block text-sm font-semibold text-gray-700 mb-1">State <span class="text-red-500">*</span></label>
          <select name="State" id="State" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Select State ...</option>
            <?php do { ?>
              <option value="<?php echo $row_state2['State']?>" <?php if($totalRows_editRecords > 0) {if (!(strcmp($row_state2['State'], $row_editRecords['State']))) echo "selected"; }?>><?php echo $row_state2['State']?></option>
            <?php } while ($row_state2 = mysqli_fetch_assoc($state2)); ?>
          </select>
        </div>
        <div>
          <label for="MobilePhone" class="block text-sm font-semibold text-gray-700 mb-1">Mobile Phone <span class="text-red-500">*</span></label>
          <input name="MobilePhone" id="MobilePhone" type="text" value="<?php echo $row_editRecords['MobilePhone']; ?>"
                 class="w-full rounded-lg border-gray-300 px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
        </div>
        <div>
          <label for="EmailAddress" class="block text-sm font-semibold text-gray-700 mb-1">E-mail Address</label>
          <input name="EmailAddress" id="EmailAddress" type="email" value="<?php echo $row_editRecords['EmailAddress']; ?>"
                 class="w-full rounded-lg border-gray-300 px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
        </div>
      </div>

      <!-- Status & Charge Interest -->
      <div class="flex flex-wrap gap-8">
        <label class="inline-flex items-center">
          <input type="checkbox" name="status" id="status" <?php if (!(strcmp($row_editRecords['Status'],"Active"))) echo "checked"; ?> class="form-checkbox text-blue-600">
          <span class="ml-2">Active</span>
        </label>
        <label class="inline-flex items-center">
          <input type="checkbox" name="interest" id="interest" <?php if (!(strcmp($row_editRecords['interest'],"1"))) echo "checked"; ?> class="form-checkbox text-blue-600">
          <span class="ml-2">Charge Interest?</span>
        </label>
      </div>

      <!-- Submit Button -->
      <div class="text-center pt-4">
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded-lg shadow transition">
          Save
        </button>
      </div>
    </div>
  </form>
</div>
<?php include('footer.php');?>