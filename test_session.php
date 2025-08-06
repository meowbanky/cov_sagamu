<?php
session_start();

echo "<h2>Session Debug Information</h2>";
echo "<pre>";

echo "Session ID: " . session_id() . "\n";
echo "Session Name: " . session_name() . "\n";
echo "Session Status: " . session_status() . "\n\n";

echo "All Session Variables:\n";
print_r($_SESSION);

echo "\n\nSpecific Session Variables:\n";
echo "UserID: " . (isset($_SESSION['UserID']) ? $_SESSION['UserID'] : 'NOT SET') . "\n";
echo "FirstName: " . (isset($_SESSION['FirstName']) ? $_SESSION['FirstName'] : 'NOT SET') . "\n";
echo "SESS_FIRST_NAME: " . (isset($_SESSION['SESS_FIRST_NAME']) ? $_SESSION['SESS_FIRST_NAME'] : 'NOT SET') . "\n";

echo "\n\nServer Variables:\n";
echo "HTTP_USER_AGENT: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'NOT SET') . "\n";
echo "REMOTE_ADDR: " . ($_SERVER['REMOTE_ADDR'] ?? 'NOT SET') . "\n";

echo "</pre>";
?> 