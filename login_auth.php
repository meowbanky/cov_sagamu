<?php
session_start();
require_once('Connections/cov.php'); // Ensure this file uses MySQLi for database connection

// Create a MySQLi connection
$mysqli = new mysqli('localhost', 'emmaggic_root', 'Oluwaseyi', 'emmaggic_cofv');
if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}

if (isset($_POST['uname']) && isset($_POST['passwd'])) {
	$loginUsername = $mysqli->real_escape_string(trim($_POST['uname']));
	$password = trim($_POST['passwd']);

	// Prepare statement to prevent SQL injection
	$stmt = $mysqli->prepare("SELECT * FROM tblusers WHERE Username = ? AND status = 'Active' AND access = 1");
	$stmt->bind_param("s", $loginUsername);
	$stmt->execute();
	$result = $stmt->get_result();

	if ($result->num_rows > 0) {
		$row = $result->fetch_assoc();
		// Verify password
		if (password_verify($password, $row['UPassword'])) {
			// Password is correct, start session
			
			session_regenerate_id();

			$_SESSION['FirstName'] = $row['lastname'] . ", " . $row['firstname'];
			$_SESSION['UserID'] = $row['UserID'];
		
			header("Location: dashboard.php");
			exit;
		} else {

			// Password is not correct
			//echo "No password";
		    header("Location: index.php");
			exit;
		}
	} else {
		//echo  "No user found";

	    header("Location: index.php");
		exit;
	}

	$stmt->close();
}
$mysqli->close();
