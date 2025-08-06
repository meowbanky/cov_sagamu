<?php
require_once('config/EnvConfig.php');
require_once 'vendor/autoload.php';

echo "<h1>Simple OpenAI API Test</h1>";

$openai_key = EnvConfig::getOpenAIKey();
echo "<p>OpenAI Key: " . (!empty($openai_key) ? '✅ Configured (' . strlen($openai_key) . ' chars)' : '❌ Not configured') . "</p>";

if (empty($openai_key)) {
    echo "<p style='color: red;'>No OpenAI key found!</p>";
    exit();
}

echo "<h2>Testing OpenAI API Connection...</h2>";

try {
    echo "<p>🔍 Creating HTTP client...</p>";
    $client = new \GuzzleHttp\Client([
        'timeout' => 30,
        'connect_timeout' => 10
    ]);
    
    echo "<p>📤 Making simple test request...</p>";
    
    $response = $client->post('https://api.openai.com/v1/chat/completions', [
        'headers' => [
            'Authorization' => 'Bearer ' . $openai_key,
            'Content-Type' => 'application/json',
        ],
        'json' => [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => 'Say "Hello, API is working!" and return it as JSON: {"message": "Hello, API is working!"}'
                ]
            ],
            'temperature' => 0.1,
            'max_tokens' => 100
        ]
    ]);
    
    echo "<p>✅ Response received!</p>";
    echo "<p>Status Code: " . $response->getStatusCode() . "</p>";
    
    $result = json_decode($response->getBody(), true);
    
    if (isset($result['choices'][0]['message']['content'])) {
        $content = $result['choices'][0]['message']['content'];
        echo "<p>📄 AI Response:</p>";
        echo "<div style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc;'>";
        echo htmlspecialchars($content);
        echo "</div>";
        
        // Try to extract JSON
        preg_match('/\{.*\}/s', $content, $matches);
        if (isset($matches[0])) {
            $json_data = json_decode($matches[0], true);
            if ($json_data) {
                echo "<p>✅ JSON extracted successfully!</p>";
                echo "<pre>" . print_r($json_data, true) . "</pre>";
            }
        }
    } else {
        echo "<p>❌ No content in response</p>";
        echo "<pre>" . print_r($result, true) . "</pre>";
    }
    
} catch (\GuzzleHttp\Exception\ConnectException $e) {
    echo "<p style='color: red;'>❌ Connection Error: " . $e->getMessage() . "</p>";
    echo "<p>This might be a network connectivity issue.</p>";
} catch (\GuzzleHttp\Exception\ClientException $e) {
    echo "<p style='color: red;'>❌ Client Error (4xx): " . $e->getMessage() . "</p>";
    echo "<p>Response: " . $e->getResponse()->getBody() . "</p>";
} catch (\GuzzleHttp\Exception\ServerException $e) {
    echo "<p style='color: red;'>❌ Server Error (5xx): " . $e->getMessage() . "</p>";
    echo "<p>Response: " . $e->getResponse()->getBody() . "</p>";
} catch (\GuzzleHttp\Exception\RequestException $e) {
    echo "<p style='color: red;'>❌ Request Error: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ General Error: " . $e->getMessage() . "</p>";
}

echo "<h2>System Information:</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>cURL Extension: " . (function_exists('curl_init') ? '✅ Available' : '❌ Not available') . "</p>";
echo "<p>OpenSSL Extension: " . (extension_loaded('openssl') ? '✅ Available' : '❌ Not available') . "</p>";
echo "<p>JSON Extension: " . (extension_loaded('json') ? '✅ Available' : '❌ Not available') . "</p>";

echo "<h2>Network Test:</h2>";
$test_urls = [
    'https://api.openai.com' => 'OpenAI API',
    'https://httpbin.org/get' => 'HTTP Test Service',
    'https://www.google.com' => 'Google'
];

foreach ($test_urls as $url => $name) {
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'method' => 'GET'
        ]
    ]);
    
    $result = @file_get_contents($url, false, $context);
    if ($result !== false) {
        echo "<p>✅ $name ($url): Connected successfully</p>";
    } else {
        echo "<p>❌ $name ($url): Connection failed</p>";
    }
}
?>