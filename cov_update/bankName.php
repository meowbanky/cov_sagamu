<?php 
session_start();
include_once('classes/functions.php');

mysqli_select_db($conn2,$db_name);
$sql = "SELECT tblbankcode.bank,tblbankcode.bankcode FROM tblbankcode";
$result = mysqli_query($conn2, $sql);
 $row = mysqli_fetch_assoc($result);

?>


<select class="selection-2" name="bank" id="bank">
									<option value="">Select Bank</option>
<?php do { ?>
									<option value="<?php echo $row['bankcode'] ; ?>"><?php echo $row['bank']; ?></option>
									
								
								 <?php }while($row = mysqli_fetch_assoc($result))   ?>
   </select>

   <script src="vendor/select2/select2.min.js"></script>
   <script>
			$(".selection-2").select2({
				minimumResultsForSearch: 20,
				dropdownParent: $("#dropDownSelect1"),
			});
		</script>