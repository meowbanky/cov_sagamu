<?php
// Start session with proper configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

require_once('Connections/cov.php');
require_once('config/EnvConfig.php');

echo "<h1>Client-Side PDF Text Extraction Test</h1>";

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

// Check if text was submitted
if (isset($_POST['extracted_text']) && !empty($_POST['extracted_text'])) {
    echo "<h2>Text Extraction Results:</h2>";
    $extracted_text = $_POST['extracted_text'];
    echo "<p>Text length: " . strlen($extracted_text) . " characters</p>";
    
    echo "<h3>Extracted Text (first 2000 characters):</h3>";
    echo "<div style='background: #f5f5f5; padding: 15px; border: 1px solid #ddd; max-height: 400px; overflow-y: auto; font-family: monospace; font-size: 12px;'>";
    echo "<pre>" . htmlspecialchars(substr($extracted_text, 0, 2000)) . "</pre>";
    echo "</div>";
    
    // Test AI analysis
    echo "<h3>AI Analysis Test:</h3>";
    echo "<p>Starting AI analysis...</p>";
    
    try {
        $transactions = analyzeWithOpenAI($extracted_text, $openai_key);
        echo "<p>AI analysis completed.</p>";
        echo "<p>Transactions found: " . count($transactions) . "</p>";
        
        if (!empty($transactions)) {
            echo "<h4>Extracted Transactions:</h4>";
            echo "<div style='background: #e8f5e8; padding: 15px; border: 1px solid #28a745;'>";
            echo "<pre>" . print_r($transactions, true) . "</pre>";
            echo "</div>";
        } else {
            echo "<p style='color: orange;'>⚠️ No transactions found by AI</p>";
            echo "<p>This could mean:</p>";
            echo "<ul>";
            echo "<li>The AI prompt needs adjustment</li>";
            echo "<li>The text format needs preprocessing</li>";
            echo "<li>OpenAI API returned an empty response</li>";
            echo "</ul>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ AI Analysis Error: " . $e->getMessage() . "</p>";
    }
}

function analyzeWithOpenAI($text, $api_key) {
    try {
        echo "<p>🔍 Making OpenAI API request...</p>";
        
        $client = new \GuzzleHttp\Client();
        
        $prompt = "Extract financial transactions from the following Nigerian bank statement text. 
        Look for transaction names, amounts, and whether they are credits (money received) or debits (money sent).
        Focus on Nigerian names and currency amounts in Naira (₦).
        Return the data in JSON format with this structure:
        [{\"name\": \"Person Name\", \"amount\": 1000.00, \"type\": \"credit\" or \"debit\"}]
        
        Bank statement text:
        " . substr($text, 0, 4000);
        
        echo "<p>📤 Sending request to OpenAI...</p>";
        
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
        
        echo "<p>✅ OpenAI response received.</p>";
        
        $result = json_decode($response->getBody(), true);
        
        if (isset($result['choices'][0]['message']['content'])) {
            $content = $result['choices'][0]['message']['content'];
            
            echo "<p>📄 Raw AI response (first 500 chars):</p>";
            echo "<div style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc; font-family: monospace; font-size: 12px;'>";
            echo htmlspecialchars(substr($content, 0, 500));
            echo "</div>";
            
            preg_match('/\[.*\]/s', $content, $matches);
            if (isset($matches[0])) {
                echo "<p>🔍 JSON array found in response.</p>";
                $transactions = json_decode($matches[0], true);
                if (is_array($transactions)) {
                    echo "<p>✅ JSON parsed successfully.</p>";
                    return $transactions;
                } else {
                    echo "<p>❌ JSON parsing failed.</p>";
                }
            } else {
                echo "<p>❌ No JSON array found in response.</p>";
            }
        } else {
            echo "<p>❌ No content in OpenAI response.</p>";
        }
        
        return [];
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ OpenAI Error: " . $e->getMessage() . "</p>";
        return [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client-Side PDF Test</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .upload-area { border: 2px dashed #ccc; padding: 20px; text-align: center; margin: 20px 0; }
        .upload-area.dragover { border-color: #007bff; background-color: #f8f9fa; }
        .progress { width: 100%; height: 20px; background-color: #f0f0f0; border-radius: 10px; overflow: hidden; margin: 10px 0; }
        .progress-bar { height: 100%; background-color: #007bff; width: 0%; transition: width 0.3s; }
        .text-display { background: #f5f5f5; padding: 15px; border: 1px solid #ddd; max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 12px; white-space: pre-wrap; }
        .button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .button:disabled { background: #ccc; cursor: not-allowed; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Client-Side PDF Text Extraction</h2>
        <p>This test uses client-side PDF processing to avoid server timeouts.</p>
        
        <div class="upload-area" id="uploadArea">
            <p>Drag and drop a PDF file here, or click to select</p>
            <input type="file" id="fileInput" accept=".pdf" style="display: none;">
            <button class="button" onclick="document.getElementById('fileInput').click()">Select PDF File</button>
        </div>
        
        <div id="progressContainer" style="display: none;">
            <p>Processing PDF...</p>
            <div class="progress">
                <div class="progress-bar" id="progressBar"></div>
            </div>
        </div>
        
        <div id="resultsContainer" style="display: none;">
            <h3>Extracted Text:</h3>
            <div class="text-display" id="extractedText"></div>
            
            <form id="textForm" method="post">
                <input type="hidden" name="extracted_text" id="textInput">
                <button type="submit" class="button">Send to Server for AI Analysis</button>
            </form>
        </div>
    </div>

    <script>
        // Initialize PDF.js
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const progressContainer = document.getElementById('progressContainer');
        const progressBar = document.getElementById('progressBar');
        const resultsContainer = document.getElementById('resultsContainer');
        const extractedTextDiv = document.getElementById('extractedText');
        const textInput = document.getElementById('textInput');
        
        // Drag and drop functionality
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                processPDF(files[0]);
            }
        });
        
        uploadArea.addEventListener('click', () => {
            fileInput.click();
        });
        
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                processPDF(e.target.files[0]);
            }
        });
        
        async function processPDF(file) {
            if (file.type !== 'application/pdf') {
                alert('Please select a PDF file');
                return;
            }
            
            progressContainer.style.display = 'block';
            resultsContainer.style.display = 'none';
            
            try {
                const arrayBuffer = await readFileAsArrayBuffer(file);
                progressBar.style.width = '30%';
                
                const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
                progressBar.style.width = '50%';
                
                let fullText = '';
                const totalPages = pdf.numPages;
                
                for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
                    const page = await pdf.getPage(pageNum);
                    const textContent = await page.getTextContent();
                    const pageText = textContent.items.map(item => item.str).join(' ');
                    fullText += pageText + '\n';
                    
                    const progress = 50 + (pageNum / totalPages) * 50;
                    progressBar.style.width = progress + '%';
                }
                
                progressBar.style.width = '100%';
                
                // Display results
                extractedTextDiv.textContent = fullText;
                textInput.value = fullText;
                resultsContainer.style.display = 'block';
                progressContainer.style.display = 'none';
                
            } catch (error) {
                console.error('PDF processing error:', error);
                alert('Error processing PDF: ' + error.message);
                progressContainer.style.display = 'none';
            }
        }
        
        function readFileAsArrayBuffer(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.onerror = () => reject(reader.error);
                reader.readAsArrayBuffer(file);
            });
        }
    </script>
</body>
</html> 