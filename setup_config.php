<?php
/**
 * Setup script for Bank Statement Upload & Analysis System
 * This script helps users configure their system for the first time
 */

echo "<h2>Bank Statement System Setup</h2>";

// Check if config.env exists
if (file_exists('config.env')) {
    echo "<div style='color: green;'>✅ Configuration file already exists</div>";
    echo "<p>Your system is already configured. You can:</p>";
    echo "<ul>";
    echo "<li><a href='config_manager.php'>Manage Configuration</a></li>";
    echo "<li><a href='bank_statement_upload.php'>Upload Bank Statements</a></li>";
    echo "<li><a href='test_bank_statement_system.php'>Test System</a></li>";
    echo "</ul>";
} else {
    echo "<div style='color: orange;'>⚠️ Configuration file not found</div>";
    echo "<p>Let's set up your configuration:</p>";
    
    // Create default config
    $default_config = "# Database Configuration\n";
    $default_config .= "DB_HOST=localhost\n";
    $default_config .= "DB_NAME=emmaggic_coop\n";
    $default_config .= "DB_USER=emmaggic_root\n";
    $default_config .= "DB_PASSWORD=Oluwaseyi\n\n";
    
    $default_config .= "# OpenAI Configuration\n";
    $default_config .= "OPENAI_API_KEY=your_openai_api_key_here\n\n";
    
    $default_config .= "# Application Configuration\n";
    $default_config .= "APP_NAME=Cooperative Management System\n";
    $default_config .= "APP_ENV=production\n";
    $default_config .= "APP_DEBUG=false\n\n";
    
    $default_config .= "# File Upload Configuration\n";
    $default_config .= "MAX_FILE_SIZE=10MB\n";
    $default_config .= "ALLOWED_FILE_TYPES=pdf,xlsx,xls,jpg,jpeg,png\n\n";
    
    $default_config .= "# Security Configuration\n";
    $default_config .= "SESSION_TIMEOUT=3600\n";
    $default_config .= "ENCRYPTION_KEY=your_encryption_key_here\n";
    
    if (file_put_contents('config.env', $default_config)) {
        echo "<div style='color: green;'>✅ Default configuration file created successfully!</div>";
        echo "<p>Next steps:</p>";
        echo "<ol>";
        echo "<li><a href='config_manager.php'>Configure your OpenAI API key</a></li>";
        echo "<li><a href='test_bank_statement_system.php'>Test the system</a></li>";
        echo "<li><a href='bank_statement_upload.php'>Start uploading bank statements</a></li>";
        echo "</ol>";
    } else {
        echo "<div style='color: red;'>❌ Failed to create configuration file</div>";
        echo "<p>Please check file permissions and try again.</p>";
    }
}

echo "<hr>";
echo "<h3>System Requirements Check</h3>";

// Check PHP version
if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
    echo "✅ PHP version: " . PHP_VERSION . " (OK)<br>";
} else {
    echo "❌ PHP version: " . PHP_VERSION . " (Requires 7.4+)<br>";
}

// Check required extensions
$required_extensions = ['mysqli', 'json', 'fileinfo', 'zip'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ Extension '$ext' is loaded<br>";
    } else {
        echo "❌ Extension '$ext' is not loaded<br>";
    }
}

// Check upload directory
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

// Check composer dependencies
if (file_exists('vendor/autoload.php')) {
    echo "✅ Composer dependencies installed<br>";
} else {
    echo "❌ Composer dependencies not found. Run: composer install<br>";
}

echo "<hr>";
echo "<p><strong>Need help?</strong> Check the <a href='BANK_STATEMENT_SYSTEM_README.md'>README file</a> for detailed instructions.</p>";
?>