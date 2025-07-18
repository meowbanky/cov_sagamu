<?php  
ini_set('max_execution_time','0');
 $connect = mysqli_connect("localhost", "root", "oluwaseyi", "salary"); 
 include_once('functions.php'); 
 include_once('model.php'); 
 session_start();

 $j = 0;
 $percent;
 $period = $_SESSION['currentactiveperiod'];
 global $conn;
              $query = $conn->prepare('SELECT * FROM employee WHERE STATUSCD = ?');
              $res = $query->execute(array('A'));
              $out = $query->fetchAll(PDO::FETCH_ASSOC);
               //get employee info                                          
             while ($row = array_shift($out)) 
             {	//$percent = '';
             	
             	
             	if(lastPayCheck($row['staff_id'],$period) == '1')
										{
											echo 'dfd';
											echo lastPayCheck($row['staff_id'],$period);
												
										}else{
											
										//	echo lastPayCheck($row['staff_id'],$period).'<br>';
										}
							}
							
?>