<?php  
 //load_data.php  
 $connect = mysqli_connect("localhost", "root", "oluwaseyi", "salary");  
 $output = ''; 
 
 $sql_edType = "SELECT tbl_earning_deduction_type.edType FROM tbl_earning_deduction INNER JOIN tbl_earning_deduction_type ON tbl_earning_deduction_type.edType = tbl_earning_deduction.edType WHERE ed_id = '{$_POST['newearningcodeAll']}'";  
       
 $edType = mysqli_query($connect,$sql_edType) or die(mysql_error());
 $row_edType = mysqli_fetch_assoc($edType);
 $totalRows_edType = mysqli_num_rows($edType);

	if($totalRows_edType > 0) {
  	if($row_edType['edType']== 1)	{
			 if(isset($_POST["grade_level"]))
			 { 
			  
			  	
			      $sql_source = "SELECT tbl_earning_deduction.source, tbl_earning_deduction.ed_id FROM tbl_earning_deduction WHERE ed_id = '". $_POST['newearningcodeAll'] ."'";  
			  	     
			      
			      $result_source = mysqli_query($connect, $sql_source);
			      $row_source = mysqli_fetch_assoc($result_source);
				  	 
			 
			 	if($row_source['source'] == 1)
			  {  
			   	if ( $_POST['newearningcodeAll'] == 21){
			           $sql = "SELECT allowancetable.`value` FROM allowancetable WHERE allowancetable.grade = '".$_POST['grade_level']."' AND allowancetable.step = '".$_POST['step']."' AND allowcode = ". $_POST['newearningcodeAll']." AND category = '". $_POST['callType']."'";  
			       }else {
			   				$sql = "SELECT allowancetable.`value` FROM allowancetable WHERE allowancetable.grade = '".$_POST['grade_level']."' AND allowancetable.step = '".$_POST['step']."' AND allowcode = ". $_POST['newearningcodeAll']."";  
			       }
			   		
			      $result = mysqli_query($connect, $sql);
			      $row = mysqli_fetch_assoc($result);
				  	$output = number_format($row['value']);
				  	
				  	if($output == 0){
				  		echo "manual";
				  		//echo $sql;
				  }else{
				  		echo $output;
				  	}
				  	 
			    }else {
			     	echo "manual";
			     	
			    }

			 }	  
 
 
 	     }elseif($row_edType['edType']== 2){
 	     	
 	     	if($_POST['newearningcodeAll'] == 50){
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
 					
 	     	}elseif($_POST['newearningcodeAll'] == 41){
 	     		if(isset($_POST['grade_level']))
 				{ 
  			  $sql_consolidated = "SELECT allow_deduc.staff_id, allow_deduc.allow_id,sum(allow_deduc.`value`) as tax
															FROM allow_deduc INNER JOIN tbl_earning_deduction ON tbl_earning_deduction.ed_id = allow_deduc.allow_id
															INNER JOIN employee ON employee.staff_id = allow_deduc.staff_id
															WHERE taxable = 1 AND transcode = 1 and allow_deduc.staff_id = '" .$_POST['curremployee']."' and DEPTCD = '40'";
      		$result_consolidated = mysqli_query($connect, $sql_consolidated);
		      $row_consolidated = mysqli_fetch_assoc($result_consolidated);
		      $total_rowsConsolidated = mysqli_num_rows($result_consolidated);
		      
		      
		      
		      $output = number_format($row_consolidated['tax']*0.05,0,'','');
		      if($output > 0){
		      echo $output;
				}else{
					echo $output = 'manual';
				}
			
 	
 					}
 					
 	     	}else {
 	     	 	echo $output = 'manual';
 	     	}
 	     	
 	     }elseif($row_edType['edType']== 3){
 	     	if(isset($_POST['grade_level']))
 { 
  

			$sql_numberOfRows = "SELECT deductiontable.ded_id, deductiontable.allowcode, deductiontable.grade, deductiontable.step, deductiontable.`value`, deductiontable.category, deductiontable.ratetype, deductiontable.percentage FROM deductiontable WHERE allowcode = '". $_POST['newearningcodeAll'] ."'";  
			$result_numberOfRows = mysqli_query($connect, $sql_numberOfRows);
      $row_numberOfRows = mysqli_fetch_assoc($result_numberOfRows);
      $total_rows = mysqli_num_rows($result_numberOfRows);
          
      if($total_rows == 1){
      	if($row_numberOfRows['ratetype'] == 1){
      		$output = $row_numberOfRows['value'];
      		echo $output;
      	}else{
      		$sql_consolidated = "SELECT allowancetable.allow_id, allowancetable.allowcode, allowancetable.grade, allowancetable.step, allowancetable.`value`, allowancetable.category, allowancetable.ratetype, allowancetable.percentage FROM allowancetable WHERE allowancetable.allowcode = 1 and grade = '". $_POST['grade_level'] ."' and step = '". $_POST['step'] ."'";
      		$result_consolidated = mysqli_query($connect, $sql_consolidated);
		      $row_consolidated = mysqli_fetch_assoc($result_consolidated);
		      $total_rowsConsolidated = mysqli_num_rows($result_consolidated);
		      $output = ($row_numberOfRows['percentage']*$row_consolidated['value'])/100;
		      echo $output;
		      
      	}
      	
      }else if($total_rows > 1) {
       	$sql_mulitple = "SELECT deductiontable.ded_id, deductiontable.allowcode, deductiontable.grade, deductiontable.step, deductiontable.`value`, deductiontable.category, deductiontable.ratetype, deductiontable.percentage FROM deductiontable WHERE allowcode = '". $_POST['newearningcodeAll'] ."' and grade = '". $_POST['grade_level'] ."'"; 
				$result_mulitple = mysqli_query($connect, $sql_mulitple);
	      		$row_mulitple = mysqli_fetch_assoc($result_mulitple);
	     		$total_mulitple = mysqli_num_rows($result_mulitple);
	      
	      if($row_numberOfRows['ratetype'] == 1){
      		$output = $row_mulitple['value'];
      		//echo $sql_mulitple ; 
      	}else{
      		$sql_consolidated = "SELECT allowancetable.allow_id, allowancetable.allowcode, allowancetable.grade, allowancetable.step, allowancetable.`value`, allowancetable.category, allowancetable.ratetype, allowancetable.percentage FROM allowancetable WHERE allowancetable.allowcode = 1 and grade = '". $_POST['grade_level'] ."' and step = '". $_POST['step'] ."'";
      		$result_consolidated = mysqli_query($connect, $sql_consolidated);
		      $row_consolidated = mysqli_fetch_assoc($result_consolidated);
		      $total_rowsConsolidated = mysqli_num_rows($result_consolidated);
		      $output = ceil(($row_mulitple['percentage']*$row_consolidated['value'])/100);
		      echo $output;
	            	}
 			} else if($total_rows == 0){
 				
 				echo 'manual';
 			}	
 	
 }
 	     	
 	     }
 
  }
 ?>  

