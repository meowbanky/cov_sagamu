<?php
// Start session with proper configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

header('Content-Type: application/json');

// Check if user is logged in - try multiple session variables
$is_logged_in = false;
$session_vars = ['UserID', 'userid', 'SESS_FIRST_NAME', 'FirstName'];
$found_var = null;
$found_value = null;

foreach ($session_vars as $var) {
    if (isset($_SESSION[$var])) {
        $is_logged_in = true;
        $found_var = $var;
        $found_value = $_SESSION[$var];
        break;
    }
}

$response = [
    'success' => $is_logged_in,
    'message' => $is_logged_in ? 'Authenticated' : 'Unauthorized',
    'debug' => [
        'session_id' => session_id(),
        'session_name' => session_name(),
        'session_status' => session_status(),
        'found_var' => $found_var,
        'found_value' => $found_value,
        'all_session_vars' => array_keys($_SESSION),
        'session_data' => $_SESSION
    ]
];

echo json_encode($response, JSON_PRETTY_PRINT);
?> 