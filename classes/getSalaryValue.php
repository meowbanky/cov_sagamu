<?php  
 //load_data.php  
 $connect = mysqli_connect("localhost", "root", "oluwaseyi", "salary");  
 $output = '';  
 if(isset($_POST["grade_level"]))
 { 
  
  		
      $sql_source = "SELECT tbl_earning_deduction.source, tbl_earning_deduction.ed_id FROM tbl_earning_deduction WHERE ed_id = '". $_POST['newearningcode'] ."'";  
  	     
      
      $result_source = mysqli_query($connect, $sql_source);
      $row_source = mysqli_fetch_assoc($result_source);
	  	 
 
 	if($row_source['source'] == 1)
  {  
   	if ( $_POST['newearningcode'] == 21){
           $sql = "SELECT allowancetable.`value` FROM allowancetable WHERE allowancetable.grade = '".$_POST['grade_level']."' AND allowancetable.step = '".$_POST['step']."' AND allowcode = ". $_POST['newearningcode']." AND category = '". $_POST['callType']."'";  
       }else {
   				$sql = "SELECT allowancetable.`value` FROM allowancetable WHERE allowancetable.grade = '".$_POST['grade_level']."' AND allowancetable.step = '".$_POST['step']."' AND allowcode = ". $_POST['newearningcode']."";  
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
 
 ?>  

