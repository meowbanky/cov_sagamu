<?php
session_start();

// Check if user is logged in
$is_logged_in = false;
$session_vars = ['UserID', 'userid', 'SESS_FIRST_NAME', 'FirstName'];
$user_id = null;
$user_name = null;

foreach ($session_vars as $var) {
    if (isset($_SESSION[$var])) {
        $is_logged_in = true;
        if ($var === 'UserID' || $var === 'userid') {
            $user_id = $_SESSION[$var];
        } else {
            $user_name = $_SESSION[$var];
        }
        break;
    }
}

echo json_encode([
    'success' => $is_logged_in,
    'message' => $is_logged_in ? 'User is logged in' : 'User is not logged in',
    'session_data' => $_SESSION,
    'user_id' => $user_id,
    'user_name' => $user_name
]);
?> 