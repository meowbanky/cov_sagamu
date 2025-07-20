<?php
session_start();
header('Content-Type: application/json');
require_once('Connections/cov.php');

function respond($arr) {
    echo json_encode($arr);
    exit;
}

if (!isset($_SESSION['UserID'])) {
    respond(['error' => 'Session expired. Please log in again.']);
}

$memberid = isset($_POST['memberid']) ? intval($_POST['memberid']) : 0;
if (!$memberid) respond(['error' => 'Invalid member ID.']);

// Collect and sanitize fields
$sfxname = trim($_POST['sfxname'] ?? '');
$Fname = trim($_POST['Fname'] ?? '');
$Mname = trim($_POST['Mname'] ?? '');
$Lname = trim($_POST['Lname'] ?? '');
$gender = trim($_POST['gender'] ?? '');
$DOB = trim($_POST['DOB'] ?? '');
$Address = trim($_POST['Address'] ?? '');
$Address2 = trim($_POST['Address2'] ?? '');
$City = trim($_POST['City'] ?? '');
$State = trim($_POST['State'] ?? '');
$MobilePhone = trim($_POST['MobilePhone'] ?? '');
$EmailAddress = trim($_POST['EmailAddress'] ?? '');
$status = isset($_POST['status']) && $_POST['status'] === 'Active' ? 'Active' : 'In-Active';
$interest = isset($_POST['interest']) && $_POST['interest'] === '1' ? 1 : 0;

// NOK fields
$NOkName = trim($_POST['NOkName'] ?? '');
$NOKRelationship = trim($_POST['NOKRelationship'] ?? '');
$NOKPhone = trim($_POST['NOKPhone'] ?? '');
$NOKAddress = trim($_POST['NOKAddress'] ?? '');

// Password (optional)
$passwordGen = trim($_POST['passwordGen'] ?? '');

// Validate required fields
if (!$sfxname || !$Fname || !$Lname || !$gender || !$Address || !$City || !$State || !$MobilePhone || !$NOkName || !$NOKRelationship || !$NOKPhone || !$NOKAddress) {
    respond(['error' => 'Please fill all required fields.']);
}

// Update member info in tbl_personalinfo
$stmt = $cov->prepare("UPDATE tbl_personalinfo SET sfxname=?, Fname=?, Mname=?, Lname=?, gender=?, DOB=?, Address=?, Address2=?, City=?, State=?, MobilePhone=?, EmailAddress=?, Status=?, interest=?, UpdatedBy=? WHERE memberid=?");
$updatedBy = $_SESSION['FirstName'] ?? 'Admin';
$stmt->bind_param('sssssssssssssssi', $sfxname, $Fname, $Mname, $Lname, $gender, $DOB, $Address, $Address2, $City, $State, $MobilePhone, $EmailAddress, $status, $interest, $updatedBy, $memberid);
$ok = $stmt->execute();
$stmt->close();

if (!$ok) {
    respond(['error' => 'Failed to update member information.']);
}

// Update or insert NOK information in tbl_nok
$stmt2 = $cov->prepare("INSERT INTO tbl_nok (memberid, NOkName, NOKRelationship, NOKPhone, NOKAddress) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE NOkName=?, NOKRelationship=?, NOKPhone=?, NOKAddress=?");
$stmt2->bind_param('sssssssss', $memberid, $NOkName, $NOKRelationship, $NOKPhone, $NOKAddress, $NOkName, $NOKRelationship, $NOKPhone, $NOKAddress);
$ok2 = $stmt2->execute();
$stmt2->close();

if (!$ok2) {
    respond(['error' => 'Failed to update NOK information.']);
}

// Optionally update password if provided
if ($passwordGen) {
    $hashed = password_hash($passwordGen, PASSWORD_DEFAULT);
    $stmt3 = $cov->prepare("UPDATE tbl_personalinfo SET password=? WHERE memberid=?");
    $stmt3->bind_param('si', $hashed, $memberid);
    $stmt3->execute();
    $stmt3->close();
}

respond(['success' => 'Member information updated successfully.']); 