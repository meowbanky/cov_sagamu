<?php
// Start session with proper configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

echo "<h1>Simple Authentication Test</h1>";

// Check authentication
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

echo "<h2>Authentication Status:</h2>";
echo "<p>Logged in: " . ($is_logged_in ? '✅ Yes' : '❌ No') . "</p>";

if ($is_logged_in) {
    echo "<p><strong>Found variable:</strong> $found_var = $found_value</p>";
    echo "<p style='color: green; font-weight: bold;'>✅ Authentication successful!</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Authentication failed!</p>";
    echo "<p>No valid session variables found.</p>";
}

echo "<h2>Session Information:</h2>";
echo "<ul>";
echo "<li><strong>Session ID:</strong> " . session_id() . "</li>";
echo "<li><strong>Session Name:</strong> " . session_name() . "</li>";
echo "<li><strong>Session Status:</strong> " . session_status() . "</li>";
echo "</ul>";

echo "<h2>All Session Variables:</h2>";
if (empty($_SESSION)) {
    echo "<p style='color: red;'>No session variables found!</p>";
} else {
    echo "<ul>";
    foreach ($_SESSION as $key => $value) {
        echo "<li><strong>$key:</strong> " . htmlspecialchars($value) . "</li>";
    }
    echo "</ul>";
}

echo "<h2>Test Links:</h2>";
echo "<ul>";
echo "<li><a href='ai_bank_statement_upload.php'>Go to AI Upload</a></li>";
echo "<li><a href='debug_upload.php'>Debug Upload Test</a></li>";
echo "<li><a href='dashboard.php'>Go to Dashboard</a></li>";
echo "</ul>";
?> 