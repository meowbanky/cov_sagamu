<?php
set_time_limit(300);
require_once('Connections/cov.php'); // Make sure this uses MySQLi

$mysqli = new mysqli('localhost', 'emmaggic_root', 'Oluwaseyi', 'emmaggic_cofv');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Fetch all users
$sql = "SELECT * FROM `tblusers` WHERE PlainPassword IS NULL";
$result = $mysqli->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $userId = $row['UserID'];
        //$plainPassword = $row['PlainPassword']; // Assuming this is the current plaintext or inadequately hashed password
$plainPassword = str_pad(rand(0, pow(10, 6) - 1), 6, '0', STR_PAD_LEFT);
        // Hash the password
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

        // Update the user's password in the database
        $updateStmt = $mysqli->prepare("UPDATE tblusers SET UPassword = ?,PlainPassword = ?  WHERE UserID = ?");
        $updateStmt->bind_param("ssi", $hashedPassword,$plainPassword, $userId);
        $updateStmt->execute();
        $updateStmt->close();
    }
    echo "Passwords updated successfully.";
} else {
    echo "No users found.";
}

$mysqli->close();
