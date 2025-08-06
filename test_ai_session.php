<?php
session_start();

// Simulate the same session check as the AI processor
$is_logged_in = false;
$session_vars = ['UserID', 'userid', 'SESS_FIRST_NAME', 'FirstName'];

foreach ($session_vars as $var) {
    if (isset($_SESSION[$var])) {
        $is_logged_in = true;
        echo "Found session variable: $var = " . $_SESSION[$var] . "\n";
        break;
    }
}

if (!$is_logged_in) {
    echo "No valid session found!\n";
    echo "Available session variables:\n";
    print_r($_SESSION);
} else {
    echo "Session is valid!\n";
}

echo "\nSession ID: " . session_id() . "\n";
echo "Session Name: " . session_name() . "\n";
?> 