<?php
// register_action.php
header('Content-Type: application/json');
require_once('Connections/cov.php');

$data = json_decode(file_get_contents("php://input"), true);
if(!$data) { echo json_encode(['error'=>'No data.']); exit; }

$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';

// Server-side validation
if(!$name || !$email || !$username || !$password) {
    echo json_encode(['error'=>'All fields required.']); exit;
}
if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error'=>'Invalid email.']); exit;
}
if(!preg_match('/^[a-zA-Z0-9_]{3,}$/', $username)) {
    echo json_encode(['error'=>'Invalid username.']); exit;
}
if(strlen($password) < 6) {
    echo json_encode(['error'=>'Password too short.']); exit;
}

// Check for duplicate email/username
$stmt = $cov->prepare("SELECT 1 FROM tblusers WHERE Username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$stmt->store_result();
if($stmt->num_rows > 0) {
    echo json_encode(['error'=>'Email or Username already registered.']); exit;
}
$stmt->close();

// Insert user
$hashed = password_hash($password, PASSWORD_DEFAULT);
// Separate first/last for demo (you can adjust)
$parts = explode(' ', $name, 2);
$fname = $parts[0];
$lname = $parts[1] ?? '';

$stmt = $cov->prepare("INSERT INTO tblusers (firstname, lastname, Username, UPassword, email, dateofRegistration) VALUES (?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("sssss", $fname, $lname, $username, $hashed, $email);
if($stmt->execute()) {
    echo json_encode(['success'=>'Registration successful!']);
} else {
    echo json_encode(['error'=>'Database error.']);
}
$stmt->close();
?>
