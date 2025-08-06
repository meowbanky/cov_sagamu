<?php
// Start session with proper configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

require_once('Connections/cov.php');

echo "<h1>Session Authentication Test</h1>";
echo "<h2>Current Session Status</h2>";

echo "<h3>Session Information:</h3>";
echo "<ul>";
echo "<li><strong>Session ID:</strong> " . session_id() . "</li>";
echo "<li><strong>Session Name:</strong> " . session_name() . "</li>";
echo "<li><strong>Session Status:</strong> " . session_status() . "</li>";
echo "<li><strong>Session Save Path:</strong> " . session_save_path() . "</li>";
echo "</ul>";

echo "<h3>Session Variables:</h3>";
if (empty($_SESSION)) {
    echo "<p style='color: red;'>No session variables found!</p>";
} else {
    echo "<ul>";
    foreach ($_SESSION as $key => $value) {
        echo "<li><strong>$key:</strong> " . htmlspecialchars($value) . "</li>";
    }
    echo "</ul>";
}

echo "<h3>Authentication Check:</h3>";
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

if ($is_logged_in) {
    echo "<p style='color: green;'>✅ Authentication successful!</p>";
    echo "<p><strong>Found variable:</strong> $found_var = $found_value</p>";
} else {
    echo "<p style='color: red;'>❌ Authentication failed!</p>";
    echo "<p>No valid session variables found.</p>";
}

echo "<h3>Database Connection Test:</h3>";
if ($cov) {
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
    // Test if we can query the users table
    $test_query = "SELECT COUNT(*) as count FROM tblusers WHERE status = 'Active'";
    $test_result = mysqli_query($cov, $test_query);
    if ($test_result) {
        $row = mysqli_fetch_assoc($test_result);
        echo "<p><strong>Active users in database:</strong> " . $row['count'] . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Database query failed</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Database connection failed</p>";
}

echo "<h3>Test Links:</h3>";
echo "<ul>";
echo "<li><a href='test_ai_auth.php' target='_blank'>Test AI Auth (JSON)</a></li>";
echo "<li><a href='dashboard.php'>Go to Dashboard</a></li>";
echo "<li><a href='ai_bank_statement_upload.php'>Go to AI Upload</a></li>";
echo "</ul>";

echo "<h3>Debug Information:</h3>";
echo "<pre>";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Session Cookie Params:\n";
print_r(session_get_cookie_params());
echo "\nServer Variables:\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'NOT SET') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NOT SET') . "\n";
echo "HTTP_USER_AGENT: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'NOT SET') . "\n";
echo "</pre>";
?> 