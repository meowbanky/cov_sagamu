<?php
require_once('Connections/cov.php');
mysqli_select_db($cov, $database_cov);
header('Content-Type: application/json');

// Collect & validate all POST values (server-side validation)
$fields = [
    'sfxname','Fname','Mname','Lname','gender','DOB','Address','Address2','City','State',
    'MobilePhone','EmailAddress','status','NOkName','NOKRelationship','NOKPhone','NOKAddress','passwordGen'
];
$data = [];
foreach($fields as $f) $data[$f] = trim($_POST[$f] ?? '');

if(!$data['Fname'] || !$data['Lname'] || !$data['sfxname'] || !$data['gender'] || !$data['Address'] || !$data['City'] || !$data['State'] || !$data['MobilePhone'] || !$data['NOkName'] || !$data['NOKRelationship'] || !$data['NOKPhone'] || !$data['NOKAddress'] || !$data['passwordGen']) {
    echo json_encode(['error'=>'Please fill all required fields.']); exit;
}

$status = ($data['status'] === 'Active') ? 'Active' : 'In-Active';

$cov->autocommit(false);

try {
    // Personal Info insert
    $stmt = $cov->prepare("INSERT INTO tbl_personalinfo (sfxname, Fname, Mname, Lname, gender, DOB, Address, Address2, City, State, MobilePhone, EmailAddress, DateOfReg, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
    $stmt->bind_param('sssssssssssss', $data['sfxname'], $data['Fname'], $data['Mname'], $data['Lname'], $data['gender'], $data['DOB'], $data['Address'], $data['Address2'], $data['City'], $data['State'], $data['MobilePhone'], $data['EmailAddress'], $status);
    $stmt->execute();
    $memberId = $stmt->insert_id ? $stmt->insert_id : $cov->insert_id;
    $stmt->close();

    // Users Table insert
    $hash = password_hash($data['passwordGen'], PASSWORD_DEFAULT);
    $stmt = $cov->prepare("INSERT INTO tblusers (UserID, firstname, middlename, lastname, Username, UPassword, CPassword, PlainPassword, dateofRegistration) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $username = $memberId;
    $stmt->bind_param('isssssss', $memberId, $data['Fname'], $data['Mname'], $data['Lname'], $username, $hash, $hash, $data['passwordGen']);
    $stmt->execute();
    $stmt->close();

    // Next of Kin insert
    $stmt = $cov->prepare("INSERT INTO tbl_nok (memberid, NOkName, NOKRelationship, NOKPhone, NOKAddress) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('issss', $memberId, $data['NOkName'], $data['NOKRelationship'], $data['NOKPhone'], $data['NOKAddress']);
    $stmt->execute();
    $stmt->close();

    // (Optional) Send email if EmailAddress provided
    if($data['EmailAddress']) {
        $to = $data['EmailAddress'];
        $subject = "Login Credential";
        $message = "Your login details:\nUsername: $username\nPassword: ".$data['passwordGen'];
        @mail($to, $subject, $message, "From: noreply@covcoop.com");
    }

    $cov->commit();
    echo json_encode(['success'=>'Registration saved successfully!']);
} catch(Exception $e) {
    $cov->rollback();
    echo json_encode(['error'=>'Database error: ' . $e->getMessage()]);
}
