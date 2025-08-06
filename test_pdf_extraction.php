<?php
// Test PDF text extraction functionality
session_start();

// Simulate a logged-in user
$_SESSION['UserID'] = 1;
$_SESSION['SESS_FIRST_NAME'] = 'Test User';

echo "<h1>PDF Text Extraction Test</h1>";

// Test the PDF text extractor
echo "<h2>Testing PDF Text Extractor</h2>";

// Check if the PDF text extractor file exists
if (file_exists('js/pdf-text-extractor.js')) {
    echo "<p>✅ PDF text extractor file exists</p>";
} else {
    echo "<p>❌ PDF text extractor file missing</p>";
}

// Check if the main upload file exists
if (file_exists('ai_bank_statement_upload.php')) {
    echo "<p>✅ Main upload file exists</p>";
} else {
    echo "<p>❌ Main upload file missing</p>";
}

// Check if the processor file exists
if (file_exists('bank_statement_processor.php')) {
    echo "<p>✅ Processor file exists</p>";
} else {
    echo "<p>❌ Processor file missing</p>";
}

// Test database connection
try {
    require_once('config/EnvConfig.php');
    $hostname_cov = EnvConfig::getDBHost();
    $database_cov = EnvConfig::getDBName();
    $username_cov = EnvConfig::getDBUser();
    $password_cov = EnvConfig::getDBPassword();
    
    $cov = mysqli_connect($hostname_cov, $username_cov, $password_cov, $database_cov);
    if ($cov) {
        echo "<p>✅ Database connection successful</p>";
        mysqli_close($cov);
    } else {
        echo "<p>❌ Database connection failed</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Database connection error: " . $e->getMessage() . "</p>";
}

// Check OpenAI configuration
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

// Check upload directory
$upload_dir = 'uploads/bank_statements/';
if (is_dir($upload_dir)) {
    echo "<p>✅ Upload directory exists</p>";
} else {
    echo "<p>❌ Upload directory missing</p>";
}

echo "<h2>Test Instructions</h2>";
echo "<ol>";
echo "<li>Go to <a href='ai_bank_statement_upload.php'>AI Bank Statement Upload</a></li>";
echo "<li>Upload a PDF bank statement file</li>";
echo "<li>Check the browser console for any JavaScript errors</li>";
echo "<li>Check the server error logs for any PHP errors</li>";
echo "</ol>";

echo "<h2>Expected Behavior</h2>";
echo "<ul>";
echo "<li>PDF should be processed client-side using PDF.js</li>";
echo "<li>Extracted text should be sent to the server</li>";
echo "<li>Server should process the text with OpenAI</li>";
echo "<li>Results should be displayed in the UI</li>";
echo "</ul>";

echo "<p><a href='ai_bank_statement_upload.php'>Go to AI Bank Statement Upload</a></p>";
?> 