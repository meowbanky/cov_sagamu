<?php
// Start session with proper configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

require_once('Connections/cov.php');
require_once('config/EnvConfig.php');
require_once 'vendor/autoload.php';

echo "<h1>Text Extraction Debug Test</h1>";

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
echo "<p>OpenAI Key: " . (!empty($openai_key) ? '✅ Configured (' . strlen($openai_key) . ' chars)' : '❌ Not configured') . "</p>";

// Check if files were uploaded
if (isset($_FILES['files']) && !empty($_FILES['files']['name'][0])) {
    echo "<h2>File Upload Debug:</h2>";
    echo "<p>Files uploaded: ✅ Yes</p>";
    echo "<p>Number of files: " . count($_FILES['files']['name']) . "</p>";
    
    foreach ($_FILES['files']['name'] as $key => $filename) {
        echo "<h3>Processing File: " . htmlspecialchars($filename) . "</h3>";
        echo "<p>Size: " . $_FILES['files']['size'][$key] . " bytes</p>";
        echo "<p>Type: " . $_FILES['files']['type'][$key] . "</p>";
        
        $tmp_name = $_FILES['files']['tmp_name'][$key];
        
        // Save file
        $file_path = 'uploads/bank_statements/debug_' . time() . '_' . $filename;
        if (move_uploaded_file($tmp_name, $file_path)) {
            echo "<p>File saved: ✅ Yes</p>";
            echo "<p>Saved to: " . $file_path . "</p>";
        } else {
            echo "<p>File saved: ❌ Failed</p>";
            continue;
        }
        
        // Check for client-side extracted text
        echo "<h4>Step 1: Client-Side Text Check</h4>";
        if (isset($_POST['pdf_texts']) && isset($_POST['pdf_names'])) {
            $client_texts = $_POST['pdf_texts'];
            $client_names = $_POST['pdf_names'];
            
            echo "<p>Client-side texts received: " . count($client_texts) . "</p>";
            echo "<p>Client-side names received: " . count($client_names) . "</p>";
            
            $client_text_index = array_search($filename, $client_names);
            if ($client_text_index !== false && isset($client_texts[$client_text_index])) {
                $extracted_text = $client_texts[$client_text_index];
                echo "<p>✅ Client-side text found for this file!</p>";
                echo "<p>Text length: " . strlen($extracted_text) . " characters</p>";
                echo "<h5>Extracted Text (first 1000 chars):</h5>";
                echo "<div style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc; font-family: monospace; font-size: 12px; max-height: 300px; overflow-y: auto;'>";
                echo htmlspecialchars(substr($extracted_text, 0, 1000));
                echo "</div>";
            } else {
                echo "<p>❌ No client-side text found for this file</p>";
                $extracted_text = '';
            }
        } else {
            echo "<p>❌ No client-side texts received in POST data</p>";
            $extracted_text = '';
        }
        
        // If no client-side text, try server-side extraction
        if (empty($extracted_text)) {
            echo "<h4>Step 2: Server-Side Text Extraction</h4>";
            $file_type = $_FILES['files']['type'][$key];
            
            if ($file_type === 'application/pdf') {
                echo "<p>Processing as PDF...</p>";
                $extracted_text = extractTextFromPDF($file_path);
            } elseif (in_array($file_type, ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])) {
                echo "<p>Processing as Excel...</p>";
                $extracted_text = extractTextFromExcel($file_path);
            } elseif (in_array($file_type, ['image/jpeg', 'image/png', 'image/jpg'])) {
                echo "<p>Processing as Image...</p>";
                $extracted_text = extractTextFromImage($file_path);
            } else {
                echo "<p>❌ Unsupported file type: " . $file_type . "</p>";
                continue;
            }
            
            if (!empty($extracted_text)) {
                echo "<p>✅ Server-side text extraction completed</p>";
                echo "<p>Text length: " . strlen($extracted_text) . " characters</p>";
                echo "<h5>Extracted Text (first 1000 chars):</h5>";
                echo "<div style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc; font-family: monospace; font-size: 12px; max-height: 300px; overflow-y: auto;'>";
                echo htmlspecialchars(substr($extracted_text, 0, 1000));
                echo "</div>";
            } else {
                echo "<p>❌ Server-side text extraction failed</p>";
            }
        }
        
        // Test AI analysis if we have text
        if (!empty($extracted_text)) {
            echo "<h4>Step 3: AI Analysis Test</h4>";
            echo "<p>Text length being sent to OpenAI: " . strlen($extracted_text) . " characters</p>";
            
            $start_time = microtime(true);
            $transactions = analyzeWithOpenAI($extracted_text, $openai_key);
            $ai_time = round(microtime(true) - $start_time, 2);
            
            echo "<p>AI analysis completed in {$ai_time} seconds</p>";
            echo "<p>Transactions found: " . count($transactions) . "</p>";
            
            if (!empty($transactions)) {
                echo "<h5>Extracted Transactions:</h5>";
                echo "<div style='background: #e8f5e8; padding: 10px; border: 1px solid #28a745;'>";
                echo "<pre>" . print_r($transactions, true) . "</pre>";
                echo "</div>";
            } else {
                echo "<p>⚠️ No transactions found by AI</p>";
            }
        } else {
            echo "<h4>Step 3: AI Analysis Test</h4>";
            echo "<p>❌ Cannot test AI analysis - no text extracted</p>";
        }
        
        echo "<hr>";
    }
} else {
    echo "<h2>File Upload Form</h2>";
    echo "<p>No files uploaded. Please upload a file to test.</p>";
    
    echo "<form method='post' enctype='multipart/form-data'>";
    echo "<p><label>Select Files: <input type='file' name='files[]' accept='.pdf,.xlsx,.xls,.jpg,.jpeg,.png' multiple></label></p>";
    echo "<p><input type='submit' value='Upload and Test'></p>";
    echo "</form>";
}

// Include the extraction functions
function extractTextFromPDF($file_path) {
    // Try pdftotext first
    if (function_exists('shell_exec')) {
        $output = shell_exec("which pdftotext");
        if (!empty($output)) {
            $text = shell_exec("pdftotext -q \"$file_path\" -");
            if (!empty($text) && strlen($text) > 100) {
                return $text;
            }
        }
    }
    
    // Try Ghostscript
    if (function_exists('shell_exec')) {
        $timeout = 30;
        $command = "timeout $timeout gs -sDEVICE=txtwrite -dNOPAUSE -dBATCH -dSAFER -sOutputFile=- \"$file_path\" 2>/dev/null";
        $output = shell_exec($command);
        if (!empty($output) && strlen($output) > 100) {
            return $output;
        }
        
        $command = "timeout $timeout gs -sDEVICE=ps2ascii -dNOPAUSE -dBATCH -dSAFER -sOutputFile=- \"$file_path\" 2>/dev/null";
        $output = shell_exec($command);
        if (!empty($output) && strlen($output) > 100) {
            return $output;
        }
    }
    
    // Basic PHP extraction
    $content = file_get_contents($file_path);
    if (strpos($content, '%PDF') === 0) {
        // Extract text streams
        preg_match_all('/\(([^)]+)\)/s', $content, $matches);
        $text = implode(' ', $matches[1]);
        return $text;
    }
    
    return '';
}

function extractTextFromExcel($file_path) {
    try {
        $spreadsheet = IOFactory::load($file_path);
        $text = '';
        
        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            foreach ($worksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                
                foreach ($cellIterator as $cell) {
                    $text .= $cell->getValue() . ' ';
                }
                $text .= "\n";
            }
        }
        
        return $text;
    } catch (Exception $e) {
        return '';
    }
}

function extractTextFromImage($file_path) {
    // Basic image text extraction would require OCR
    // For now, return empty
    return '';
}

function analyzeWithOpenAI($text, $api_key) {
    try {
        $client = new \GuzzleHttp\Client([
            'timeout' => 60,
            'connect_timeout' => 15
        ]);
        
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
?> 