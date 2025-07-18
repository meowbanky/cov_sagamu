<?php
//include_once('classes/functions.php');
$dbHost = 'localhost';
$dbUsername = 'emmaggic_root';
$dbPassword = 'Oluwaseyi';
$dbName = 'emmaggic_cofv';
//connect with the database
$return_arr = array();
$db = new mysqli($dbHost,$dbUsername,$dbPassword,$dbName);
//get search term
$searchTerm = $_GET['term'];
//get matched data from skills table
$st_query ="SELECT
tbl_personalinfo.memberid,
tbl_personalinfo.fname, ifnull(tbl_personalinfo.mname,'') as 'mname',tbl_personalinfo.lname,
tblaccountno.accountno,
tbl_personalinfo.mobilephone,
tbl_personalinfo.emailaddress,
tblaccountno.bank_code
FROM
tbl_personalinfo
LEFT JOIN tblaccountno ON tblaccountno.coopno = tbl_personalinfo.memberid
WHERE  (memberid like '%".$searchTerm."%' or Fname like '%".$searchTerm."%' or Mname like '%".$searchTerm."%' or Lname like '%".$searchTerm."%') ORDER BY memberid ASC" ;

$query = $db->query($st_query);
while ($row = $query->fetch_assoc()) {
      $data['id'] = $row['memberid'];
	  $data['label'] = $row['fname'].' '.$row['mname'].' '.$row['lname'];
	  $data['value'] = $row['memberid'];
	  $data['phone'] = $row['mobilephone'];
	  $data['emailaddress'] = $row['emailaddress'];
	  $data['accountno'] = $row['accountno'];
	  $data['bankcode'] = $row['bank_code'];
	  $data['fname'] = $row['fname'];
	  $data['lname'] = $row['lname'];
	  $data['mname'] = $row['mname'];

	  array_push($return_arr,$data);
}
//return json data
echo json_encode($return_arr);
?>

