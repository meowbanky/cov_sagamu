<?php
// Test file for AI Bank Statement Upload System

require_once('Connections/cov.php');
require_once('config/EnvConfig.php');

echo "<h1>AI Bank Statement Upload System - Test</h1>";

// Test 1: Database Connection
echo "<h2>1. Database Connection Test</h2>";
try {
    if ($cov) {
        echo "✅ Database connection successful<br>";
        
        // Test if required tables exist
        $tables = ['tbl_personalinfo', 'tbl_contributions', 'tbl_loan', 'tbpayrollperiods'];
        foreach ($tables as $table) {
            $result = mysqli_query($cov, "SHOW TABLES LIKE '$table'");
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

// Test 2: Configuration
echo "<h2>2. Configuration Test</h2>";
try {
    $openai_key = EnvConfig::getOpenAIKey();
    if ($openai_key && $openai_key !== 'your_openai_api_key_here') {
        echo "✅ OpenAI API key configured<br>";
    } else {
        echo "⚠️ OpenAI API key not configured (set in config.env)<br>";
    }
    
    echo "Database Host: " . EnvConfig::getDBHost() . "<br>";
    echo "Database Name: " . EnvConfig::getDBName() . "<br>";
    echo "App Name: " . EnvConfig::getAppName() . "<br>";
} catch (Exception $e) {
    echo "❌ Configuration error: " . $e->getMessage() . "<br>";
}

// Test 3: Directory Permissions
echo "<h2>3. Directory Permissions Test</h2>";
$directories = [
    'uploads/bank_statements' => 'Bank statement uploads',
    'uploads/exports' => 'Export files',
    'js' => 'JavaScript files'
];

foreach ($directories as $dir => $description) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "✅ $description directory exists and is writable<br>";
        } else {
            echo "⚠️ $description directory exists but is not writable<br>";
        }
    } else {
        echo "❌ $description directory does not exist<br>";
    }
}

// Test 4: Required Files
echo "<h2>4. Required Files Test</h2>";
$files = [
    'ai_bank_statement_upload.php' => 'Main upload interface',
    'ai_bank_statement_processor.php' => 'Backend processor',
    'js/ai_bank_statement.js' => 'Frontend JavaScript',
    'config/EnvConfig.php' => 'Configuration class',
    'config.env' => 'Configuration file'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "✅ $description file exists<br>";
    } else {
        echo "❌ $description file does not exist<br>";
    }
}

// Test 5: Dependencies
echo "<h2>5. Dependencies Test</h2>";
if (file_exists('vendor/autoload.php')) {
    echo "✅ Composer autoloader exists<br>";
    
    // Test if required classes can be loaded
    try {
        require_once 'vendor/autoload.php';
        echo "✅ Autoloader working<br>";
        
        // Test PhpSpreadsheet
        if (class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
            echo "✅ PhpSpreadsheet library loaded<br>";
        } else {
            echo "❌ PhpSpreadsheet library not found<br>";
        }
        
        // Test Guzzle
        if (class_exists('GuzzleHttp\Client')) {
            echo "✅ Guzzle HTTP client loaded<br>";
        } else {
            echo "❌ Guzzle HTTP client not found<br>";
        }
    } catch (Exception $e) {
        echo "❌ Autoloader error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Composer autoloader not found. Run 'composer install'<br>";
}

// Test 6: Sample Data
echo "<h2>6. Sample Data Test</h2>";
try {
    // Check if there are any members in the database
    $result = mysqli_query($cov, "SELECT COUNT(*) as count FROM tbl_personalinfo WHERE Status = 'Active'");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "✅ Found " . $row['count'] . " active members in database<br>";
    } else {
        echo "❌ Could not query members table<br>";
    }
    
    // Check if there are any periods
    $result = mysqli_query($cov, "SELECT COUNT(*) as count FROM tbpayrollperiods");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "✅ Found " . $row['count'] . " payroll periods in database<br>";
    } else {
        echo "❌ Could not query periods table<br>";
    }
} catch (Exception $e) {
    echo "❌ Sample data error: " . $e->getMessage() . "<br>";
}

echo "<h2>Test Complete</h2>";
echo "<p>If all tests pass, your AI Bank Statement Upload System is ready to use!</p>";
echo "<p><a href='ai_bank_statement_upload.php'>Go to AI Bank Statement Upload</a></p>";
?> 