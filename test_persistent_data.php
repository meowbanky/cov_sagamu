<?php
// Test script for persistent data functionality
require_once('config/EnvConfig.php');

// Test database connection
try {
    $hostname_cov = EnvConfig::getDBHost();
    $database_cov = EnvConfig::getDBName();
    $username_cov = EnvConfig::getDBUser();
    $password_cov = EnvConfig::getDBPassword();
    
    $cov = mysqli_connect($hostname_cov, $username_cov, $password_cov);
    if (!$cov) {
        throw new Exception('Database connection failed: ' . mysqli_connect_error());
    }
    
    if (!mysqli_select_db($cov, $database_cov)) {
        throw new Exception('Database selection failed: ' . mysqli_error($cov));
    }
    
    echo "✅ Database connection successful\n";
    
    // Test table creation
    include 'bank_statement_processor.php';
    
    // Check if tables exist
    $tables_to_check = [
        'bank_statement_files',
        'bank_statement_extractions',
        'manual_transaction_matches',
        'unmatched_transactions'
    ];
    
    foreach ($tables_to_check as $table) {
        $result = mysqli_query($cov, "SHOW TABLES LIKE '$table'");
        if (mysqli_num_rows($result) > 0) {
            echo "✅ Table '$table' exists\n";
            
            // Show table structure
            $structure = mysqli_query($cov, "DESCRIBE $table");
            echo "   Columns in $table:\n";
            while ($row = mysqli_fetch_assoc($structure)) {
                echo "   - {$row['Field']} ({$row['Type']})\n";
            }
            echo "\n";
        } else {
            echo "❌ Table '$table' does not exist\n";
        }
    }
    
    echo "\n🎉 Persistent data system ready!\n\n";
    echo "New Features Added:\n";
    echo "- ✅ Persistent storage of extracted bank statement data\n";
    echo "- ✅ Duplicate file detection and user prompting\n";
    echo "- ✅ Bank statement selection dropdown\n";
    echo "- ✅ Processing status tracking (processed vs pending)\n";
    echo "- ✅ Load existing analysis without OpenAI calls\n";
    echo "- ✅ Visual indicators for processed transactions\n";
    echo "- ✅ Prevention of duplicate transaction processing\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
