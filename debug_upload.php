<?php
// Start session with proper configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

require_once('Connections/cov.php');
require_once('config/EnvConfig.php');
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

echo "<h1>Debug Upload Test</h1>";

// Check authentication
$is_logged_in = false;
$session_vars = ['UserID', 'userid', 'SESS_FIRST_NAME', 'FirstName'];

foreach ($session_vars as $var) {
    if (isset($_SESSION[$var])) {
        $is_logged_in = true;
        break;
    }
}

echo "<h2>Authentication Status:</h2>";
echo "<p>Logged in: " . ($is_logged_in ? '✅ Yes' : '❌ No') . "</p>";

if (!$is_logged_in) {
    echo "<p style='color: red;'>Please log in first!</p>";
    exit();
}

// Check OpenAI key
$openai_key = EnvConfig::getOpenAIKey();
echo "<h2>OpenAI Configuration:</h2>";
echo "<p>OpenAI Key: " . (!empty($openai_key) ? '✅ Configured' : '❌ Not configured') . "</p>";

// Check upload directory
$upload_dir = 'uploads/bank_statements/';
echo "<h2>Upload Directory:</h2>";
echo "<p>Directory exists: " . (is_dir($upload_dir) ? '✅ Yes' : '❌ No') . "</p>";
echo "<p>Directory writable: " . (is_writable($upload_dir) ? '✅ Yes' : '❌ No') . "</p>";

// Check if files were uploaded
echo "<h2>File Upload Test:</h2>";
if (isset($_FILES['files'])) {
    echo "<p>Files uploaded: ✅ Yes</p>";
    echo "<p>Number of files: " . count($_FILES['files']['name']) . "</p>";
    
    foreach ($_FILES['files']['name'] as $key => $name) {
        echo "<p>File $key: $name</p>";
        echo "<p>Size: " . $_FILES['files']['size'][$key] . " bytes</p>";
        echo "<p>Type: " . $_FILES['files']['type'][$key] . "</p>";
        echo "<p>Temp path: " . $_FILES['files']['tmp_name'][$key] . "</p>";
        
        // Test file extraction
        $tmp_name = $_FILES['files']['tmp_name'][$key];
        $file_type = $_FILES['files']['type'][$key];
        
        echo "<h3>Text Extraction Test for $name:</h3>";
        
        // Save file temporarily
        $test_path = $upload_dir . 'test_' . time() . '_' . $name;
        if (move_uploaded_file($tmp_name, $test_path)) {
            echo "<p>File saved: ✅ Yes</p>";
            
            // Extract text
            $extracted_text = '';
            try {
                if (strpos($file_type, 'pdf') !== false) {
                    $extracted_text = extractTextFromPDF($test_path);
                } elseif (strpos($file_type, 'excel') !== false || strpos($file_type, 'spreadsheet') !== false) {
                    $extracted_text = extractTextFromExcel($test_path);
                } elseif (strpos($file_type, 'image') !== false) {
                    $extracted_text = extractTextFromImage($test_path);
                }
                
                echo "<p>Text extracted: " . (!empty($extracted_text) ? '✅ Yes' : '❌ No') . "</p>";
                echo "<p>Text length: " . strlen($extracted_text) . " characters</p>";
                
                if (!empty($extracted_text)) {
                    echo "<h4>Extracted Text (first 500 chars):</h4>";
                    echo "<pre>" . htmlspecialchars(substr($extracted_text, 0, 500)) . "</pre>";
                    
                    // Test OpenAI analysis
                    echo "<h4>OpenAI Analysis Test:</h4>";
                    $transactions = analyzeWithOpenAI($extracted_text, $openai_key);
                    echo "<p>Transactions found: " . count($transactions) . "</p>";
                    
                    if (!empty($transactions)) {
                        echo "<h4>Transactions:</h4>";
                        echo "<pre>" . print_r($transactions, true) . "</pre>";
                    }
                }
                
            } catch (Exception $e) {
                echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
            }
            
            // Clean up
            unlink($test_path);
        } else {
            echo "<p style='color: red;'>Failed to save file</p>";
        }
    }
} else {
    echo "<p>No files uploaded</p>";
}

// Test functions
function extractTextFromPDF($file_path) {
    if (function_exists('shell_exec')) {
        $output = shell_exec("pdftotext -layout \"$file_path\" - 2>/dev/null");
        if (!empty($output)) {
            return $output;
        }
    }
    
    $content = file_get_contents($file_path);
    if ($content === false) {
        return '';
    }
    
    $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $content);
    $text = preg_replace('/\/[A-Za-z0-9\s]+\[[^\]]*\]/', '', $text);
    $text = preg_replace('/[^\x20-\x7E\n\r\t]/', '', $text);
    
    return $text;
}

function extractTextFromExcel($file_path) {
    try {
        $spreadsheet = IOFactory::load($file_path);
        $text = '';
        
        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $text .= $worksheet->getTitle() . "\n";
            foreach ($worksheet->getRowIterator() as $row) {
                $rowData = [];
                foreach ($row->getCellIterator() as $cell) {
                    $rowData[] = $cell->getValue();
                }
                $text .= implode("\t", $rowData) . "\n";
            }
            $text .= "\n";
        }
        
        return $text;
    } catch (Exception $e) {
        return '';
    }
}

function extractTextFromImage($file_path) {
    // For images, we'll need OCR. For now, return empty
    return '';
}

function analyzeWithOpenAI($text, $api_key) {
    try {
        $client = new \GuzzleHttp\Client();
        
        $prompt = "Extract financial transactions from the following Nigerian bank statement text. 
        Look for transaction names, amounts, and whether they are credits (money received) or debits (money sent).
        Focus on Nigerian names and currency amounts in Naira (₦).
        Return the data in JSON format with this structure:
        [{\"name\": \"Person Name\", \"amount\": 1000.00, \"type\": \"credit\" or \"debit\"}]
        
        Bank statement text:
        " . substr($text, 0, 4000);
        
        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a financial data extraction specialist. Extract transaction details from bank statements accurately.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.1,
                'max_tokens' => 2000
            ]
        ]);
        
        $result = json_decode($response->getBody(), true);
        
        if (isset($result['choices'][0]['message']['content'])) {
            $content = $result['choices'][0]['message']['content'];
            
            preg_match('/\[.*\]/s', $content, $matches);
            if (isset($matches[0])) {
                $transactions = json_decode($matches[0], true);
                if (is_array($transactions)) {
                    return $transactions;
                }
            }
        }
        
        return [];
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>OpenAI Error: " . $e->getMessage() . "</p>";
        return [];
    }
}

echo "<h2>Test Complete</h2>";
echo "<p><a href='ai_bank_statement_upload.php'>Back to AI Upload</a></p>";
?> 