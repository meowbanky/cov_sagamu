<?php  
 //load_data.php  
 $connect = mysqli_connect("localhost", "root", "oluwaseyi", "salary");  
 $output = ''; 
 
if(isset($_POST['grade_level']))
 { 
  			$sql_consolidated = "SELECT allowancetable.`value` FROM allowancetable WHERE allowancetable.allowcode = 1 and grade = '". $_POST['grade_level'] ."' and step = '". $_POST['step'] ."'";
      		$result_consolidated = mysqli_query($connect, $sql_consolidated);
		      $row_consolidated = mysqli_fetch_assoc($result_consolidated);
		      $total_rowsConsolidated = mysqli_num_rows($result_consolidated);
		      
		      $sql_pensionRate = "SELECT (pension.PENSON/100) as rate FROM pension WHERE grade = '". $_POST['grade_level'] ."' and step = '". $_POST['step'] ."'";
      		$result_pensionRate = mysqli_query($connect, $sql_pensionRate);
		      $row_pensionRate = mysqli_fetch_assoc($result_pensionRate);
		      $total_pensionRate = mysqli_num_rows($result_pensionRate);
		      
		      $output = ceil($row_consolidated['value']*$row_pensionRate['rate']);
		      echo $output;

			
 	
 }
?>
