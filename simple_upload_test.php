<?php
// Start session with proper configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

echo "<h1>Simple File Upload Test</h1>";

// Check authentication
$is_logged_in = false;
$session_vars = ['UserID', 'userid', 'SESS_FIRST_NAME', 'FirstName'];

foreach ($session_vars as $var) {
    if (isset($_SESSION[$var])) {
        $is_logged_in = true;
        break;
    }
}

if (!$is_logged_in) {
    echo "<p style='color: red;'>Please log in first!</p>";
    exit();
}

echo "<h2>Authentication Status:</h2>";
echo "<p>Logged in: " . ($is_logged_in ? '✅ Yes' : '❌ No') . "</p>";

// Check if files were uploaded
echo "<h2>File Upload Status:</h2>";
if (isset($_FILES['files'])) {
    echo "<p>Files uploaded: ✅ Yes</p>";
    echo "<p>Number of files: " . count($_FILES['files']['name']) . "</p>";
    
    foreach ($_FILES['files']['name'] as $key => $name) {
        echo "<h3>File $key: $name</h3>";
        echo "<ul>";
        echo "<li><strong>Size:</strong> " . $_FILES['files']['size'][$key] . " bytes</li>";
        echo "<li><strong>Type:</strong> " . $_FILES['files']['type'][$key] . "</li>";
        echo "<li><strong>Temp Path:</strong> " . $_FILES['files']['tmp_name'][$key] . "</li>";
        echo "<li><strong>Error:</strong> " . $_FILES['files']['error'][$key] . "</li>";
        echo "</ul>";
    }
} else {
    echo "<p>No files uploaded</p>";
}

// Check POST data
echo "<h2>POST Data:</h2>";
if (!empty($_POST)) {
    echo "<ul>";
    foreach ($_POST as $key => $value) {
        echo "<li><strong>$key:</strong> " . htmlspecialchars($value) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No POST data received</p>";
}

// Check REQUEST_METHOD
echo "<h2>Request Method:</h2>";
echo "<p>Method: " . $_SERVER['REQUEST_METHOD'] . "</p>";

// Check Content-Type
echo "<h2>Content Type:</h2>";
echo "<p>Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'Not set') . "</p>";

// Check file upload settings
echo "<h2>PHP File Upload Settings:</h2>";
echo "<ul>";
echo "<li><strong>file_uploads:</strong> " . (ini_get('file_uploads') ? 'Enabled' : 'Disabled') . "</li>";
echo "<li><strong>upload_max_filesize:</strong> " . ini_get('upload_max_filesize') . "</li>";
echo "<li><strong>post_max_size:</strong> " . ini_get('post_max_size') . "</li>";
echo "<li><strong>max_file_uploads:</strong> " . ini_get('max_file_uploads') . "</li>";
echo "</ul>";
?>

<h2>Upload Test Form</h2>
<form action="" method="post" enctype="multipart/form-data">
    <div>
        <label for="period">Period:</label>
        <select name="period" id="period" required>
            <option value="">Select Period</option>
            <option value="81">Period 81</option>
            <option value="82">Period 82</option>
        </select>
    </div>
    <div>
        <label for="files">Select Files:</label>
        <input type="file" name="files[]" id="files" multiple accept=".pdf,.xlsx,.xls,.jpg,.jpeg,.png" required>
    </div>
    <div>
        <input type="hidden" name="action" value="upload_and_analyze">
        <button type="submit">Upload and Test</button>
    </div>
</form>

<h2>Test Links:</h2>
<ul>
    <li><a href="ai_bank_statement_upload.php">Go to AI Upload</a></li>
    <li><a href="debug_text_extraction.php">Debug Text Extraction</a></li>
    <li><a href="dashboard.php">Go to Dashboard</a></li>
</ul>