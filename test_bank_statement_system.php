<?php
/**
 * Test file for Bank Statement Upload & Analysis System
 * This file helps verify that all components are working correctly
 */

require_once('Connections/coop.php');
require_once('config/EnvConfig.php');
require_once 'vendor/autoload.php';

echo "<h2>Bank Statement System Test</h2>";

// Test 1: Database Connection
echo "<h3>1. Database Connection Test</h3>";
try {
    if ($coop) {
        echo "✅ Database connection successful<br>";
        
        // Test if required tables exist
        $tables = ['bank_statement_files', 'tbl_debits', 'unmatched_transactions'];
        foreach ($tables as $table) {
            $result = mysqli_query($coop, "SHOW TABLES LIKE '$table'");
            if (mysqli_num_rows($result) > 0) {
                echo "✅ Table '$table' exists<br>";
            } else {
                echo "❌ Table '$table' does not exist<br>";
            }
        }
    } else {
        echo "❌ Database connection failed<br>";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 2: Required PHP Extensions
echo "<h3>2. PHP Extensions Test</h3>";
$required_extensions = ['mysqli', 'json', 'fileinfo', 'zip'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ Extension '$ext' is loaded<br>";
    } else {
        echo "❌ Extension '$ext' is not loaded<br>";
    }
}

// Test 3: Composer Dependencies
echo "<h3>3. Composer Dependencies Test</h3>";
try {
    // Test PhpSpreadsheet
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    echo "✅ PhpSpreadsheet is working<br>";
} catch (Exception $e) {
    echo "❌ PhpSpreadsheet error: " . $e->getMessage() . "<br>";
}

try {
    // Test GuzzleHttp
    $client = new \GuzzleHttp\Client();
    echo "✅ GuzzleHttp is working<br>";
} catch (Exception $e) {
    echo "❌ GuzzleHttp error: " . $e->getMessage() . "<br>";
}

// Test 4: File System Permissions
echo "<h3>4. File System Permissions Test</h3>";
$upload_dir = 'uploads/bank_statements/';
if (is_dir($upload_dir)) {
    echo "✅ Upload directory exists<br>";
    if (is_writable($upload_dir)) {
        echo "✅ Upload directory is writable<br>";
    } else {
        echo "❌ Upload directory is not writable<br>";
    }
} else {
    echo "❌ Upload directory does not exist<br>";
}

// Test 5: Employee Data
echo "<h3>5. Employee Data Test</h3>";
try {
    $result = mysqli_query($coop, "SELECT COUNT(*) as count FROM tblemployees");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "✅ Employee table accessible. Found " . $row['count'] . " employees<br>";
    } else {
        echo "❌ Cannot access employee table<br>";
    }
} catch (Exception $e) {
    echo "❌ Employee data error: " . $e->getMessage() . "<br>";
}

// Test 6: Payroll Periods
echo "<h3>6. Payroll Periods Test</h3>";
try {
    $result = mysqli_query($coop, "SELECT COUNT(*) as count FROM tbpayrollperiods");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "✅ Payroll periods table accessible. Found " . $row['count'] . " periods<br>";
    } else {
        echo "❌ Cannot access payroll periods table<br>";
    }
} catch (Exception $e) {
    echo "❌ Payroll periods error: " . $e->getMessage() . "<br>";
}

// Test 7: Session Management
echo "<h3>7. Session Management Test</h3>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✅ Sessions are active<br>";
} else {
    echo "❌ Sessions are not active<br>";
}

// Test 8: File Upload Limits
echo "<h3>8. File Upload Limits Test</h3>";
echo "Upload max filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "Post max size: " . ini_get('post_max_size') . "<br>";
echo "Max file uploads: " . ini_get('max_file_uploads') . "<br>";

// Test 9: Memory Limits
echo "<h3>9. Memory Limits Test</h3>";
echo "Memory limit: " . ini_get('memory_limit') . "<br>";
echo "Max execution time: " . ini_get('max_execution_time') . " seconds<br>";

// Test 10: Environment Configuration
echo "<h3>10. Environment Configuration Test</h3>";
try {
    $config = EnvConfig::load();
    if (!empty($config)) {
        echo "✅ Configuration file loaded successfully<br>";
        echo "Configuration keys found: " . implode(', ', array_keys($config)) . "<br>";
        
        if (EnvConfig::hasOpenAIKey()) {
            echo "✅ OpenAI API key is configured<br>";
        } else {
            echo "❌ OpenAI API key is not configured or is set to default value<br>";
        }
    } else {
        echo "❌ Configuration file is empty or could not be loaded<br>";
    }
} catch (Exception $e) {
    echo "❌ Environment configuration error: " . $e->getMessage() . "<br>";
}

echo "<h3>Test Summary</h3>";
echo "<p>If all tests show ✅, your system is ready to use the Bank Statement Upload & Analysis System.</p>";
echo "<p>If any tests show ❌, please address those issues before using the system.</p>";

echo "<h3>Next Steps</h3>";
echo "<ol>";
echo "<li>Add your OpenAI API key to the <code>config.env</code> file</li>";
echo "<li>Navigate to the 'Bank Statement Upload' menu item</li>";
echo "<li>Upload your first bank statement file</li>";
echo "<li>Review the extracted transactions and match them with employees</li>";
echo "</ol>";
?>