<?php
 require_once('../Connections/paymaster.php');
 include_once('../classes/model.php'); 
 //load_data.php  
 session_start();
 $connect = mysqli_connect("localhost", "root", "oluwaseyi", "salary");  
 $recordtime = date('Y-m-d H:i:s');
 
 $code = $_POST['newCashcodeHistory'];
 $staffID = $_POST['curremployee'];
 
try{
$query = $conn->prepare('SELECT
Sum(tbl_repayment.cashPay) as cashPay,
tbl_repayment.staff_id,
tbl_repayment.allow_id,
tbl_repayment.period,
payperiods.description,
payperiods.periodYear,
tbl_repayment.`value`,
tbl_earning_deduction.ed
FROM
tbl_repayment
INNER JOIN payperiods ON payperiods.periodId = tbl_repayment.period
INNER JOIN tbl_earning_deduction ON tbl_earning_deduction.ed_id = tbl_repayment.allow_id
														WHERE staff_id = ? and allow_id = ? and cashPay > ? group by tbl_repayment.period' );
                                           $fin = $query->execute(array($staffID,$code,0));;
                                           $res = $query->fetchAll(PDO::FETCH_ASSOC);
                                           //print_r($res);
                                       
                                       echo ' <table class="table table-bordered table-hover">';
                                      echo ' <thead> ';
                                   echo ' <tr class="earnings-ded-header"> ';
                                    echo '   <th> Loan Type </th> ';
                                     echo '  <th> Payment </th> ';
                                      echo '  <th> Payment Period </th> ';
                                   echo '  </tr> ';
                                 echo '</thead> ';
                                 echo '<tbody> ';
                                           foreach ($res as $row => $link2) {
                                       echo '<tr class="odd gradeX">';
                                         echo '<td>' . $link2['ed'].'</td>'; 
                                          			echo '<td>' . 	$link2['cashPay'].'</td>';
                                          			echo '<td>' . 	$link2['description'].' '.$link2['periodYear'].'</td>';
                                                echo '  </tr> ';
                                            }
                                          			}
                                                catch(PDOException $e){
                                                echo $e->getMessage();
                                                }
                                                
                                              
                                       
                                 
                                          
?>
