<?php
// Test the search functionality
session_start();

// Simulate a logged-in user
$_SESSION['UserID'] = 1;
$_SESSION['SESS_FIRST_NAME'] = 'Test User';

echo "<h1>Search Functionality Test</h1>";

// Test the search endpoint
$test_data = [
    'action' => 'search_employees',
    'search_term' => 'KEHINDE VICTORIA GARET'
];

echo "<h2>Testing Search with: " . $test_data['search_term'] . "</h2>";

// Make a request to the processor
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/bank_statement_processor.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen(json_encode($test_data))
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h3>Response (HTTP $http_code):</h3>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Try to decode the response
$decoded = json_decode($response, true);
if ($decoded) {
    echo "<h3>Decoded Response:</h3>";
    echo "<pre>" . print_r($decoded, true) . "</pre>";
    
    if ($decoded['success']) {
        echo "<p>✅ Search successful!</p>";
        if (isset($decoded['employees']) && is_array($decoded['employees'])) {
            echo "<p>Found " . count($decoded['employees']) . " matches:</p>";
            foreach ($decoded['employees'] as $employee) {
                echo "<p>- " . $employee['name'] . " (ID: " . $employee['member_id'] . ")</p>";
            }
        }
    } else {
        echo "<p>❌ Search failed: " . $decoded['message'] . "</p>";
    }
} else {
    echo "<p>❌ Failed to decode JSON response</p>";
}

echo "<p><a href='ai_bank_statement_upload.php'>Back to AI Upload</a></p>";
?> 