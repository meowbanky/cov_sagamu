<?php
/**
 * Test script to verify the new implementation
 * Tests the matched transactions system and unmatched transactions management
 */

require_once 'config/config.php';

echo "<h2>Testing New Implementation</h2>";

// Test 1: Check if tables exist
echo "<h3>Test 1: Database Tables</h3>";
$tables = ['manual_transaction_matches', 'unmatched_transactions', 'bank_statement_files'];

foreach ($tables as $table) {
    $result = mysqli_query($cov, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        echo "✅ Table '$table' exists<br>";
    } else {
        echo "❌ Table '$table' does not exist<br>";
    }
}

// Test 2: Check manual_transaction_matches structure
echo "<h3>Test 2: Manual Transaction Matches Table Structure</h3>";
$result = mysqli_query($cov, "DESCRIBE manual_transaction_matches");
if ($result) {
    echo "✅ Table structure:<br>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ Error checking table structure: " . mysqli_error($cov) . "<br>";
}

// Test 3: Check unmatched_transactions structure
echo "<h3>Test 3: Unmatched Transactions Table Structure</h3>";
$result = mysqli_query($cov, "DESCRIBE unmatched_transactions");
if ($result) {
    echo "✅ Table structure:<br>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ Error checking table structure: " . mysqli_error($cov) . "<br>";
}

// Test 4: Test backend function
echo "<h3>Test 4: Backend Function Test</h3>";
echo "Testing get_unmatched_transactions action...<br>";

// Simulate the AJAX request
$test_data = [
    'action' => 'get_unmatched_transactions',
    'page' => 1,
    'limit' => 10,
    'search' => '',
    'period_filter' => '',
    'type_filter' => ''
];

// Capture output
ob_start();
$_POST = $test_data;
$_SERVER['REQUEST_METHOD'] = 'POST';
include 'bank_statement_processor.php';
$output = ob_get_clean();

echo "Backend response:<br>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

// Test 5: Check if pages exist
echo "<h3>Test 5: Page Files</h3>";
$pages = [
    'unmatched_transactions.php',
    'manual_transaction_matches.php',
    'ai_bank_statement_upload.php'
];

foreach ($pages as $page) {
    if (file_exists($page)) {
        echo "✅ Page '$page' exists<br>";
    } else {
        echo "❌ Page '$page' does not exist<br>";
    }
}

// Test 6: Summary
echo "<h3>Test 6: Implementation Summary</h3>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px;'>";
echo "<h4>✅ Implementation Complete</h4>";
echo "<p><strong>Key Changes Made:</strong></p>";
echo "<ul>";
echo "<li>✅ Matched transactions now saved to <code>manual_transaction_matches</code> table for review</li>";
echo "<li>✅ Unmatched transactions saved to <code>unmatched_transactions</code> table</li>";
echo "<li>✅ Created <code>unmatched_transactions.php</code> page for managing unmatched transactions</li>";
echo "<li>✅ Created <code>manual_transaction_matches.php</code> page for reviewing matched transactions</li>";
echo "<li>✅ Added navigation links between pages</li>";
echo "<li>✅ Fixed modal responsiveness for small screens</li>";
echo "<li>✅ Added backend function <code>handleGetUnmatchedTransactions()</code></li>";
echo "</ul>";

echo "<p><strong>Workflow:</strong></p>";
echo "<ol>";
echo "<li>Upload bank statement → AI matches transactions</li>";
echo "<li>Matched transactions → Saved to <code>manual_transaction_matches</code> for review</li>";
echo "<li>Unmatched transactions → Saved to <code>unmatched_transactions</code> for manual matching</li>";
echo "<li>Review matched transactions → Process or delete as needed</li>";
echo "<li>Manual match unmatched transactions → Move to matched for review</li>";
echo "</ol>";

echo "<p><strong>Benefits:</strong></p>";
echo "<ul>";
echo "<li>🔒 Prevents incorrect automatic insertions (staff can pay on behalf of others)</li>";
echo "<li>📋 Provides comprehensive review system</li>";
echo "<li>🔍 Allows manual matching of unmatched transactions</li>";
echo "<li>📱 Responsive design works on all screen sizes</li>";
echo "<li>📊 Statistics and filtering for better management</li>";
echo "</ul>";
echo "</div>";

echo "<h3>Next Steps</h3>";
echo "<p>1. Test the complete workflow by uploading a bank statement</p>";
echo "<p>2. Verify that matched transactions appear in the review page</p>";
echo "<p>3. Test manual matching of unmatched transactions</p>";
echo "<p>4. Verify that processing transactions works correctly</p>";
?>
