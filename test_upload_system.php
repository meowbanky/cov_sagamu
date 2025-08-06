<?php
// Test the AI Bank Statement Upload System
session_start();

// Simulate a logged-in user
$_SESSION['UserID'] = 1;
$_SESSION['SESS_FIRST_NAME'] = 'Test User';

echo "<h1>AI Bank Statement Upload System - Test</h1>";

// Test 1: Check if all required files exist
echo "<h2>1. File System Check</h2>";
$required_files = [
    'ai_bank_statement_upload.php' => 'Main upload interface',
    'bank_statement_processor.php' => 'Backend processor',
    'js/pdf-text-extractor.js' => 'PDF text extractor',
    'config/EnvConfig.php' => 'Configuration file',
    'uploads/bank_statements/' => 'Upload directory'
];

foreach ($required_files as $file => $description) {
    if (file_exists($file) || is_dir($file)) {
        echo "<p>✅ $description ($file) - EXISTS</p>";
    } else {
        echo "<p>❌ $description ($file) - MISSING</p>";
    }
}

// Test 2: Check database connection
echo "<h2>2. Database Connection Test</h2>";
try {
    require_once('config/EnvConfig.php');
    $hostname_cov = EnvConfig::getDBHost();
    $database_cov = EnvConfig::getDBName();
    $username_cov = EnvConfig::getDBUser();
    $password_cov = EnvConfig::getDBPassword();
    
    $cov = mysqli_connect($hostname_cov, $username_cov, $password_cov, $database_cov);
    if ($cov) {
        echo "<p>✅ Database connection successful</p>";
        
        // Check if required tables exist
        $tables = ['bank_statement_files', 'tbl_personalinfo', 'tbl_contributions', 'tbl_loan'];
        foreach ($tables as $table) {
            $result = mysqli_query($cov, "SHOW TABLES LIKE '$table'");
            if (mysqli_num_rows($result) > 0) {
                echo "<p>✅ Table '$table' exists</p>";
            } else {
                echo "<p>❌ Table '$table' missing</p>";
            }
        }
        
        mysqli_close($cov);
    } else {
        echo "<p>❌ Database connection failed</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}

// Test 3: Check OpenAI configuration
echo "<h2>3. OpenAI Configuration Test</h2>";
try {
    $openai_key = EnvConfig::getOpenAIKey();
    if (!empty($openai_key)) {
        echo "<p>✅ OpenAI API key configured</p>";
    } else {
        echo "<p>❌ OpenAI API key not configured</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ OpenAI configuration error: " . $e->getMessage() . "</p>";
}

// Test 4: Check PHP syntax
echo "<h2>4. PHP Syntax Check</h2>";
$php_files = ['ai_bank_statement_upload.php', 'bank_statement_processor.php'];
foreach ($php_files as $file) {
    $output = shell_exec("php -l $file 2>&1");
    if (strpos($output, 'No syntax errors') !== false) {
        echo "<p>✅ $file - No syntax errors</p>";
    } else {
        echo "<p>❌ $file - Syntax errors found</p>";
        echo "<pre>$output</pre>";
    }
}

// Test 5: Check upload directory permissions
echo "<h2>5. Upload Directory Test</h2>";
$upload_dir = 'uploads/bank_statements/';
if (is_dir($upload_dir)) {
    if (is_writable($upload_dir)) {
        echo "<p>✅ Upload directory is writable</p>";
    } else {
        echo "<p>❌ Upload directory is not writable</p>";
    }
} else {
    echo "<p>❌ Upload directory does not exist</p>";
}

echo "<h2>Test Results</h2>";
echo "<p>If all tests show ✅, your AI Bank Statement Upload System is ready to use!</p>";
echo "<p><a href='ai_bank_statement_upload.php' class='btn btn-primary'>Go to AI Bank Statement Upload</a></p>";

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>Upload a PDF bank statement file</li>";
echo "<li>The system will extract text using PDF.js</li>";
echo "<li>OpenAI will analyze the transactions</li>";
echo "<li>Results will be displayed for review</li>";
echo "<li>You can manually match unmatched transactions</li>";
echo "<li>Process selected transactions to the database</li>";
echo "</ol>";
?> 