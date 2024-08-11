<?php
	session_start();
	ini_set('max_execution_time','0');
	$connect = mysqli_connect("localhost", "root", "oluwaseyi", "salary"); 
	include_once('functions.php');
	include_once('model.php');
	
	
					$payrollquery2 = $conn->prepare('SELECT * FROM tbl_autoinsert WHERE dept_id = ? AND type = 1');
         	$payrollquery2->execute(array(5));
         	$deduc = $payrollquery2->fetchAll(PDO::FETCH_ASSOC);
         	foreach ($deduc as $row => $link2) 
         	{
          	$earningamount = getAmount($link2['allow_id'],'07C','04',1).',';
          	
          	$query = 'INSERT INTO allow_deduc (staff_id, allow_id, value, date_insert, inserted_by,transcode,counter) VALUES (?,?,?,?,?,?,?)';
						$conn->prepare($query)->execute(array($currentempl, $edcode, $earningamount, $recordtime, $_SESSION['SESS_MEMBER_ID'],$transcode,$counter));
					
					
         
         	}
	
	?>