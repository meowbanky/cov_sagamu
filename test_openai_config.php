<?php
require_once('config/EnvConfig.php');

echo "<h1>OpenAI Configuration Test</h1>";

echo "<h2>Configuration Status:</h2>";

// Test if config file exists
$config_file = __DIR__ . '/config.env';
echo "<p><strong>Config file exists:</strong> " . (file_exists($config_file) ? '✅ Yes' : '❌ No') . "</p>";

// Test OpenAI key retrieval
$openai_key = EnvConfig::getOpenAIKey();
echo "<p><strong>OpenAI Key retrieved:</strong> " . (!empty($openai_key) ? '✅ Yes' : '❌ No') . "</p>";

// Test hasOpenAIKey method
$has_key = EnvConfig::hasOpenAIKey();
echo "<p><strong>hasOpenAIKey() returns:</strong> " . ($has_key ? '✅ True' : '❌ False') . "</p>";

// Show key details (masked for security)
if (!empty($openai_key)) {
    $masked_key = substr($openai_key, 0, 10) . '...' . substr($openai_key, -10);
    echo "<p><strong>Key (masked):</strong> $masked_key</p>";
    echo "<p><strong>Key length:</strong> " . strlen($openai_key) . " characters</p>";
} else {
    echo "<p><strong>Key:</strong> Not found</p>";
}

// Test raw config loading
echo "<h2>Raw Configuration Data:</h2>";
$all_config = EnvConfig::getAll();
echo "<pre>";
print_r($all_config);
echo "</pre>";

// Test specific keys
echo "<h2>Specific Configuration Values:</h2>";
echo "<ul>";
echo "<li><strong>DB_HOST:</strong> " . EnvConfig::getDBHost() . "</li>";
echo "<li><strong>DB_NAME:</strong> " . EnvConfig::getDBName() . "</li>";
echo "<li><strong>APP_NAME:</strong> " . EnvConfig::getAppName() . "</li>";
echo "<li><strong>OPENAI_API_KEY:</strong> " . (EnvConfig::hasOpenAIKey() ? 'Configured' : 'Not Configured') . "</li>";
echo "</ul>";

echo "<h2>Test Results:</h2>";
if (EnvConfig::hasOpenAIKey()) {
    echo "<p style='color: green; font-weight: bold;'>✅ OpenAI configuration is working correctly!</p>";
    echo "<p>The system should now recognize your OpenAI API key.</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ OpenAI configuration is not working.</p>";
    echo "<p>Please check your config.env file and ensure the OPENAI_API_KEY is set correctly.</p>";
}

echo "<h3>Next Steps:</h3>";
echo "<ul>";
echo "<li><a href='ai_bank_statement_upload.php'>Go to AI Upload Page</a></li>";
echo "<li><a href='session_test.php'>Test Session</a></li>";
echo "</ul>";
?> 