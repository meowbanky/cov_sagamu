<?php
// Start session with proper configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

require_once('Connections/cov.php');
require_once('config/EnvConfig.php');
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

echo "<h1>Text Extraction & AI Analysis Debug</h1>";

// Check authentication
$is_logged_in = false;
$session_vars = ['UserID', 'userid', 'SESS_FIRST_NAME', 'FirstName'];

foreach ($session_vars as $var) {
    if (isset($_SESSION[$var])) {
        $is_logged_in = true;
        break;
    }
}

if (!$is_logged_in) {
    echo "<p style='color: red;'>Please log in first!</p>";
    exit();
}

// Check OpenAI key
$openai_key = EnvConfig::getOpenAIKey();
echo "<h2>OpenAI Configuration:</h2>";
echo "<p>OpenAI Key: " . (!empty($openai_key) ? '✅ Configured' : '❌ Not configured') . "</p>";

// Check if files were uploaded
echo "<h2>File Upload Test:</h2>";
if (isset($_FILES['files'])) {
    echo "<p>Files uploaded: ✅ Yes</p>";
    echo "<p>Number of files: " . count($_FILES['files']['name']) . "</p>";
    
    foreach ($_FILES['files']['name'] as $key => $name) {
        echo "<hr>";
        echo "<h3>Processing File: $name</h3>";
        echo "<p>Size: " . $_FILES['files']['size'][$key] . " bytes</p>";
        echo "<p>Type: " . $_FILES['files']['type'][$key] . "</p>";
        
        $tmp_name = $_FILES['files']['tmp_name'][$key];
        $file_type = $_FILES['files']['type'][$key];
        
        // Save file temporarily
        $upload_dir = 'uploads/bank_statements/';
        $test_path = $upload_dir . 'debug_' . time() . '_' . $name;
        
        if (move_uploaded_file($tmp_name, $test_path)) {
            echo "<p>File saved: ✅ Yes</p>";
            echo "<p>Saved to: $test_path</p>";
            
            // Step 1: Extract text
            echo "<h4>Step 1: Text Extraction</h4>";
            $extracted_text = '';
            
            try {
                if (strpos($file_type, 'pdf') !== false) {
                    echo "<p>Processing as PDF...</p>";
                    $extracted_text = extractTextFromPDF($test_path);
                } elseif (strpos($file_type, 'excel') !== false || strpos($file_type, 'spreadsheet') !== false) {
                    echo "<p>Processing as Excel...</p>";
                    $extracted_text = extractTextFromExcel($test_path);
                } elseif (strpos($file_type, 'image') !== false) {
                    echo "<p>Processing as Image...</p>";
                    $extracted_text = extractTextFromImage($test_path);
                } else {
                    echo "<p style='color: red;'>Unsupported file type: $file_type</p>";
                }
                
                echo "<p>Text extracted: " . (!empty($extracted_text) ? '✅ Yes' : '❌ No') . "</p>";
                echo "<p>Text length: " . strlen($extracted_text) . " characters</p>";
                
                if (!empty($extracted_text)) {
                    echo "<h5>Extracted Text (first 1000 characters):</h5>";
                    echo "<div style='background: #f5f5f5; padding: 15px; border: 1px solid #ddd; max-height: 300px; overflow-y: auto;'>";
                    echo "<pre>" . htmlspecialchars(substr($extracted_text, 0, 1000)) . "</pre>";
                    echo "</div>";
                    
                    // Step 2: AI Analysis
                    echo "<h4>Step 2: AI Analysis</h4>";
                    $transactions = analyzeWithOpenAI($extracted_text, $openai_key);
                    echo "<p>Transactions found: " . count($transactions) . "</p>";
                    
                    if (!empty($transactions)) {
                        echo "<h5>Extracted Transactions:</h5>";
                        echo "<div style='background: #e8f5e8; padding: 15px; border: 1px solid #28a745;'>";
                        echo "<pre>" . print_r($transactions, true) . "</pre>";
                        echo "</div>";
                    } else {
                        echo "<p style='color: orange;'>⚠️ No transactions found by AI</p>";
                        echo "<p>This could mean:</p>";
                        echo "<ul>";
                        echo "<li>The text doesn't contain recognizable transaction data</li>";
                        echo "<li>The AI prompt needs adjustment</li>";
                        echo "<li>The file format isn't being processed correctly</li>";
                        echo "</ul>";
                    }
                } else {
                    echo "<p style='color: red;'>❌ No text could be extracted from the file</p>";
                    echo "<p>This could mean:</p>";
                    echo "<ul>";
                    echo "<li>The file is corrupted or empty</li>";
                    echo "<li>The file format isn't supported</li>";
                    echo "<li>Text extraction failed</li>";
                    echo "</ul>";
                }
                
            } catch (Exception $e) {
                echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
            }
            
            // Clean up
            unlink($test_path);
            echo "<p>Debug file cleaned up</p>";
            
        } else {
            echo "<p style='color: red;'>Failed to save file</p>";
        }
    }
} else {
    echo "<p>No files uploaded</p>";
    echo "<p>Please upload a file to test text extraction and AI analysis.</p>";
}

// Test functions
function extractTextFromPDF($file_path) {
    echo "<p>Attempting PDF text extraction...</p>";
    
    // Try using pdftotext if available
    if (function_exists('shell_exec')) {
        $output = shell_exec("pdftotext -layout \"$file_path\" - 2>/dev/null");
        if (!empty($output)) {
            echo "<p>✅ Text extracted using pdftotext</p>";
            return $output;
        }
    }
    
    echo "<p>pdftotext not available, trying basic extraction...</p>";
    
    // Fallback: try to extract text using basic methods
    $content = file_get_contents($file_path);
    if ($content === false) {
        echo "<p>❌ Could not read file contents</p>";
        return '';
    }
    
    echo "<p>File size: " . strlen($content) . " bytes</p>";
    
    // Remove PDF headers and extract text content
    $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $content);
    $text = preg_replace('/\/[A-Za-z0-9\s]+\[[^\]]*\]/', '', $text);
    $text = preg_replace('/[^\x20-\x7E\n\r\t]/', '', $text);
    
    echo "<p>✅ Basic text extraction completed</p>";
    return $text;
}

function extractTextFromExcel($file_path) {
    echo "<p>Attempting Excel text extraction...</p>";
    
    try {
        $spreadsheet = IOFactory::load($file_path);
        $text = '';
        
        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $text .= "Sheet: " . $worksheet->getTitle() . "\n";
            foreach ($worksheet->getRowIterator() as $row) {
                $rowData = [];
                foreach ($row->getCellIterator() as $cell) {
                    $rowData[] = $cell->getValue();
                }
                $text .= implode("\t", $rowData) . "\n";
            }
            $text .= "\n";
        }
        
        echo "<p>✅ Excel text extraction completed</p>";
        return $text;
    } catch (Exception $e) {
        echo "<p>❌ Excel extraction error: " . $e->getMessage() . "</p>";
        return '';
    }
}

function extractTextFromImage($file_path) {
    echo "<p>Image processing not implemented yet</p>";
    return '';
}

function analyzeWithOpenAI($text, $api_key) {
    echo "<p>Attempting OpenAI analysis...</p>";
    
    try {
        $client = new \GuzzleHttp\Client();
        
        // Prepare the prompt for Nigerian bank statements
        $prompt = "Extract financial transactions from the following Nigerian bank statement text. 
        Look for transaction names, amounts, and whether they are credits (money received) or debits (money sent).
        Focus on Nigerian names and currency amounts in Naira (₦).
        Return the data in JSON format with this structure:
        [{\"name\": \"Person Name\", \"amount\": 1000.00, \"type\": \"credit\" or \"debit\"}]
        
        Bank statement text:
        " . substr($text, 0, 4000);
        
        echo "<p>Sending request to OpenAI...</p>";
        
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
        
        echo "<p>✅ OpenAI response received</p>";
        
        if (isset($result['choices'][0]['message']['content'])) {
            $content = $result['choices'][0]['message']['content'];
            
            echo "<h5>Raw OpenAI Response:</h5>";
            echo "<div style='background: #fff3cd; padding: 10px; border: 1px solid #ffeaa7;'>";
            echo "<pre>" . htmlspecialchars($content) . "</pre>";
            echo "</div>";
            
            // Extract JSON from response
            preg_match('/\[.*\]/s', $content, $matches);
            if (isset($matches[0])) {
                $transactions = json_decode($matches[0], true);
                if (is_array($transactions)) {
                    echo "<p>✅ JSON parsed successfully</p>";
                    return $transactions;
                } else {
                    echo "<p>❌ JSON parsing failed</p>";
                }
            } else {
                echo "<p>❌ No JSON array found in response</p>";
            }
        } else {
            echo "<p>❌ No content in OpenAI response</p>";
        }
        
        return [];
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ OpenAI Error: " . $e->getMessage() . "</p>";
        return [];
    }
}

echo "<h2>Test Complete</h2>";
echo "<p><a href='ai_bank_statement_upload.php'>Back to AI Upload</a></p>";
?> 