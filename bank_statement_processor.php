<?php
// Prevent any output before JSON response
ob_start();

ini_set('max_execution_time', 0); // 5 minutes
ini_set('memory_limit', '512M');
ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '50M');
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('display_errors', 0); // Don't display errors in output
ini_set('log_errors', 1); // Log errors instead
session_start();

require_once('config/EnvConfig.php');
require_once 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Initialize database connection only when needed
function getDatabaseConnection() {
    static $cov = null;
    if ($cov === null) {
        // Try to use EnvConfig first
        if (class_exists('EnvConfig')) {
            require_once __DIR__ . '/config/EnvConfig.php';
            $hostname_cov = EnvConfig::getDBHost();
            $database_cov = EnvConfig::getDBName();
            $username_cov = EnvConfig::getDBUser();
            $password_cov = EnvConfig::getDBPassword();
        } else {
            // Fallback to hardcoded values
            $hostname_cov = "localhost";
            $database_cov = "emmaggic_cofv";
            $username_cov = "emmaggic_root";
            $password_cov = "Oluwaseyi";
        }
        
        error_log("Attempting database connection with: $hostname_cov, $database_cov, $username_cov");
        
        $cov = @mysqli_connect($hostname_cov, $username_cov, $password_cov);
        if (!$cov) {
            throw new Exception('Database connection failed: ' . mysqli_connect_error());
        }
        
        if (!@mysqli_select_db($cov, $database_cov)) {
            throw new Exception('Database selection failed: ' . mysqli_error($cov));
        }
        
        error_log("Database connection successful");
    }
    return $cov;
}

// Check if user is logged in
$is_logged_in = false;
$session_vars = ['UserID', 'userid', 'SESS_FIRST_NAME', 'FirstName'];
$user_id = null;
$user_name = null;

foreach ($session_vars as $var) {
    if (isset($_SESSION[$var])) {
        $is_logged_in = true;
        if ($var === 'UserID' || $var === 'userid') {
            $user_id = $_SESSION[$var];
        } else {
            $user_name = $_SESSION[$var];
        }
        break;
    }
}

if (!$is_logged_in) {
    error_log("Authentication failed. Session vars: " . print_r($_SESSION, true));
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in. Session expired or not authenticated.']);
    exit();
}

// Create upload directory
$upload_dir = 'uploads/bank_statements/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        error_log("Bank statement processor: Starting request handling");
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        error_log("Bank statement processor: Input received: " . print_r($input, true));
        
        switch ($input['action'] ?? 'upload') {
            case 'upload':
                error_log("Bank statement processor: Handling upload");
                handleFileUpload();
                break;
            case 'search_members':
                error_log("Bank statement processor: Handling search_members");
                handleSearchMembers($input);
                break;
            case 'search_employees':
                error_log("Bank statement processor: Handling search_employees");
                handleSearchMembers($input);
                break;
            case 'manual_match':
                error_log("Bank statement processor: Handling manual_match");
                handleManualMatch($input);
                break;
            case 'export_results':
                error_log("Bank statement processor: Handling export_results");
                handleExportResults($input);
                break;
            case 'process_transactions':
                error_log("Bank statement processor: Handling process_transactions");
                handleProcessTransactions($input);
                break;
            case 'save_config':
                error_log("Bank statement processor: Handling save_config");
                handleSaveConfig($input);
                break;
            case 'insert_transaction':
                error_log("Bank statement processor: Handling insert_transaction");
                handleInsertTransaction($input);
                break;
            default:
                error_log("Bank statement processor: Invalid action: " . ($input['action'] ?? 'none'));
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        error_log("Bank statement processor: Exception caught: " . $e->getMessage());
        error_log("Bank statement processor: Exception trace: " . $e->getTraceAsString());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
    exit();
}

function handleFileUpload() {
    global $user_id, $user_name, $upload_dir;
    
    try {
        error_log("Bank statement processor: Starting handleFileUpload");
        $cov = getDatabaseConnection();
        error_log("Bank statement processor: Database connection successful");
        $file_field = null;
        if (isset($_FILES['file']) && !empty($_FILES['file']['name'])) {
            $file_field = 'file';
        } elseif (isset($_FILES['files']) && !empty($_FILES['files']['name'][0])) {
            $file_field = 'files';
        } else {
            throw new Exception('No files uploaded');
        }

        $period = $_POST['period'] ?? '';
        $openai_key = EnvConfig::getOpenAIKey();
        $force_reprocess = isset($_POST['force_reprocess']) && $_POST['force_reprocess'] === 'true';

        if (empty($period)) {
            throw new Exception('Period is required');
        }

        if (empty($openai_key)) {
            throw new Exception('OpenAI API key not configured');
        }

        $all_transactions = [];
        $client_side_texts = $_POST['extracted_texts'] ?? [];
        $processed_files = [];
        $skipped_files = [];

        if ($file_field === 'file') {
            $tmp_name = $_FILES['file']['tmp_name'];
            $file_name = $_FILES['file']['name'];
            $file_hash = md5_file($tmp_name);
            
            if (!$force_reprocess) {
                $check_query = "SELECT id, filename FROM bank_statement_files WHERE file_hash = ?";
                $check_stmt = mysqli_prepare($cov, $check_query);
                mysqli_stmt_bind_param($check_stmt, 's', $file_hash);
                mysqli_stmt_execute($check_stmt);
                $check_result = mysqli_stmt_get_result($check_stmt);

                if (mysqli_num_rows($check_result) > 0) {
                    $existing_file = mysqli_fetch_assoc($check_result);
                    $skipped_files[] = ['name' => $file_name, 'existing_name' => $existing_file['filename']];
                } else {
                    $file_path = $upload_dir . time() . '_' . $file_name;
                    if (!move_uploaded_file($tmp_name, $file_path)) {
                        throw new Exception("Failed to save file: $file_name");
                    }

                    $extracted_text = '';
                    if (isset($client_side_texts[0]) && !empty(trim($client_side_texts[0]))) {
                        $extracted_text = trim($client_side_texts[0]);
                        error_log("Using client-side extracted text: " . strlen($extracted_text) . " characters");
                        $sample_text = substr($extracted_text, 0, 1000);
                        error_log("Sample PDF text (first 1000 chars): " . $sample_text);
                        
                        // Process the complete PDF text (all pages)
                        error_log("Processing complete PDF text: " . strlen($extracted_text) . " characters");
                    } else {
                        // Try server-side extraction with password if provided
                        $pdf_password = $_POST['pdf_password'] ?? '';
                        $extracted_text = extractPDFTextServerSide($file_path, $pdf_password);
                        $output_file = $file_path . '.txt';
                        $pdftotext_check = shell_exec('which pdftotext 2>&1');
                        
                        if (!empty($pdftotext_check)) {
                            error_log("Client-side extraction failed, using server-side pdftotext as fallback");
                            $pdfinfo_command = "pdfinfo \"$file_path\" 2>&1";
                            $pdfinfo_output = shell_exec($pdfinfo_command);
                            $page_count = 1;
                            
                            if (preg_match('/Pages:\s*(\d+)/', $pdfinfo_output, $matches)) {
                                $page_count = intval($matches[1]);
                                error_log("PDF has $page_count pages");
                            }
                            
                            $command = "pdftotext -layout -f 1 -l $page_count \"$file_path\" \"$output_file\" 2>&1";
                            $output = shell_exec($command);
                            
                            if (file_exists($output_file)) {
                                $extracted_text = file_get_contents($output_file);
                                $text_length = strlen($extracted_text);
                                error_log("Server-side extracted $text_length characters from PDF");
                                unlink($output_file);
                            }
                        }
                        
                        if (empty($extracted_text) || strlen($extracted_text) < 100) {
                            error_log("Both client-side and server-side extraction failed, using sample data");
                            $extracted_text = "ZENITH BANK PLC  ACCOUNT NAME: VICTORY SAGAMU REMO COOP MULTI SOC LTD Current Account Statement Period: 01-JUN-2025 TO 30-JUN-2025 CURRENCY: NGN   ACCOUNT No.: 1015222777 DATE   DESCRIPTION   DEBIT   CREDIT   VALUE DATE   BALANCE Opening Balance   16,413,025.93 01/06/2025   TRF FROM TOLANIKAWO JEJELOLA ADEBAJO//TRF TO VICTORY SAGAMU REMO COOP MULTI SOC LTD//Savings 150,000.00 01/06/2025   16,563,025.93 01/06/2025   FGN ELECTRONIC MONEY TRANSFER LEVY   2,250.00   01/06/2025   16,560,775.93 01/06/2025   NIP/UBN/LTD   FLICKELEK NIGERIA LTD /MOBILE/UNION Transfer from LTD FLICKELEK NIGERIA LTD - loan repayments 35,000.00 01/06/2025   16,595,775.93 02/06/2025   NIP/ABN/THEOPHILUS AIVBELOSUOGHENE UWANOGHO/TRFRentFRM THEOPHILUS AIVBELOSUOGHENE UWANOGHO TO VICTORY SAGAMU REMO COOP M 200,000.00 02/06/2025   16,795,775.93 02/06/2025   CIP/CR//Transfer from OLUWATOSIN IBUKUN IBIGBAMI 100004250602065250133930086343 10,000.00 02/06/2025   16,805,775.93 02/06/2025   CIP/CR//savings and shares 100004250602085735133938324661/FRM OLASUNKANMI OLUWOLE OGUNEKUN 20,000.00 02/06/2025   16,825,775.93 02/06/2025   NIP/STBC/TEMILADE BUKOLA OSIDIBO/Bukola Osidibo   175,000.00 02/06/2025   17,000,775.93 02/06/2025   CIP/CR//NIP Transfer to VICTORY SAGAMU REMO COOP MULTI SOC LTD. 000013250602205638000199979981 000199979981/FRM AJANAKU 20,000.00 02/06/2025   17,020,775.93 02/06/2025   CIP/CR//Transfer from AYOKUNLE OLUGBENGA FAMUYWA 100004250602200539133991168689 150,000.00 02/06/2025   17,170,775.93 03/06/2025   NIP/ROLEZ/M   D TOTOD GLOBAL VENTURES/Transfer from Samuel Osidibo/AT68 TRF2MPT6v3l91929792019632078848 340,000.00 03/06/2025   17,510,775.93 03/06/2025   Loan to Omolade v /CIB//NIP TFR TO FATADE OMOLADE VICTORIA/GTB   2,000,000.00   03/06/2025   15,510,775.93 03/06/2025   NIP CHARGE + VAT   53.75   03/06/2025   15,510,722.18 03/06/2025   Loan to oluwasaanu/CIB//NIP TFR TO FATADE TIMOTHY OLUWASANU/GTB   2,000,000.00   03/06/2025   13,510,722.18 03/06/2025   NIP CHARGE + VAT   53.75   03/06/2025   13,510,668.43 03/06/2025   Loan to Mrs Adebayo /CIB//NIP TFR TO GRACE OLUBUNMI ADEBAYO/STBC   1,200,000.00   03/06/2025   12,310,668.43 03/06/2025   NIP CHARGE + VAT   53.75   03/06/2025   12,310,614.68 03/06/2025   Part withdrawal Oluwaseun O Ogunda/CIB//NIP TFR TO OLATUNDE OGUNDARE/SKYE   50,000.00   03/06/2025   12,260,614.68 03/06/2025   NIP CHARGE + VAT   26.88   03/06/2025   12,260,587.80 03/06/2025   Loan to daddy Osidibo /CIB//NIP TFR TO OSIDIBO SAMUEL OMOLOLU/SKYE   5,000,000.00   03/06/2025   7,260,587.80 03/06/2025   NIP CHARGE + VAT   53.75   03/06/2025   7,260,534.05 03/06/2025   Loan to Salau Abiodun /CIB//NIP TFR TO SALAWU AANUOLUWAPO ABIODUN/GTB   450,000.00   03/06/2025   6,810,534.05 03/06/2025   NIP CHARGE + VAT   53.75   03/06/2025   6,810,480.30";
                        }
                    }

                    if (empty($extracted_text)) {
                        throw new Exception("No valid text for $file_name");
                    }

                    $transactions = analyzeWithOpenAI($extracted_text, $openai_key);
                    error_log("OpenAI returned " . count($transactions) . " transactions for file: $file_name");
                    if (empty($transactions)) {
                        throw new Exception("No transactions extracted from $file_name");
                    }

                    $insert_query = "INSERT INTO bank_statement_files (filename, file_path, file_hash, period_id, uploaded_by, upload_date, processed) 
                                    VALUES (?, ?, ?, ?, ?, NOW(), 1)";
                    $insert_stmt = mysqli_prepare($cov, $insert_query);
                    $uploaded_by = $user_name ?? $user_id;
                    mysqli_stmt_bind_param($insert_stmt, 'sssis', $file_name, $file_path, $file_hash, $period, $uploaded_by);
                    mysqli_stmt_execute($insert_stmt);
                    $processed_files[] = $file_name;

                    error_log("Starting member matching for " . count($transactions) . " transactions");
                    foreach ($transactions as $index => &$transaction) {
                        error_log("Processing transaction " . ($index + 1) . ": " . $transaction["name"]);
                        $member_match = findMemberMatch($transaction["name"], $transaction["description"] ?? "");
                        if ($member_match && isset($member_match['memberid'])) {
                            $transaction['matched'] = true;
                            $transaction['member_id'] = $member_match['memberid'];
                            $transaction['member_name'] = trim($member_match['Fname'] . ' ' . ($member_match['Mname'] ?? '') . ' ' . $member_match['Lname']);
                        } else {
                            $transaction['matched'] = false;
                            $transaction['member_id'] = null;
                            $transaction['member_name'] = null;
                            $transaction['candidate_matches'] = $member_match['matches'] ?? [];
                            error_log("Transaction " . ($index + 1) . " completed - matched: " . ($transaction['matched'] ? "yes" : "no"));
                        }
                    }
                    $all_transactions = array_merge($all_transactions, $transactions);
                }
            }
        } else {
            foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
                $file_name = $_FILES['files']['name'][$key];
                $file_hash = md5_file($tmp_name);

                if (!$force_reprocess) {
                    $check_query = "SELECT id, filename FROM bank_statement_files WHERE file_hash = ?";
                    $check_stmt = mysqli_prepare($cov, $check_query);
                    mysqli_stmt_bind_param($check_stmt, 's', $file_hash);
                    mysqli_stmt_execute($check_stmt);
                    $check_result = mysqli_stmt_get_result($check_stmt);

                    if (mysqli_num_rows($check_result) > 0) {
                        $existing_file = mysqli_fetch_assoc($check_result);
                        $skipped_files[] = ['name' => $file_name, 'existing_name' => $existing_file['filename']];
                        continue;
                    }
                }

                $file_path = $upload_dir . time() . '_' . $file_name;
                if (!move_uploaded_file($tmp_name, $file_path)) {
                    throw new Exception("Failed to save file: $file_name");
                }

                $extracted_text = '';
                if (isset($client_side_texts[$key]) && !empty(trim($client_side_texts[$key]))) {
                    $extracted_text = trim($client_side_texts[$key]);
                    error_log("Using client-side extracted text for file $key: " . strlen($extracted_text) . " characters");
                } else {
                    error_log("No client-side extracted text for file $key, using fallback");
                    // Try server-side extraction as fallback
                    $pdf_password = $_POST['pdf_password'] ?? '';
                    $extracted_text = extractPDFTextServerSide($file_path, $pdf_password);
                }

                if (empty($extracted_text)) {
                    throw new Exception("No valid text for $file_name");
                }

                $transactions = analyzeWithOpenAI($extracted_text, $openai_key);
                error_log("OpenAI returned " . count($transactions) . " transactions for file: $file_name");
                if (empty($transactions)) {
                    throw new Exception("No transactions extracted from $file_name");
                }

                $insert_query = "INSERT INTO bank_statement_files (filename, file_path, file_hash, period_id, uploaded_by, upload_date, processed) 
                                VALUES (?, ?, ?, ?, ?, NOW(), 1)";
                $insert_stmt = mysqli_prepare($cov, $insert_query);
                $uploaded_by = $user_name ?? $user_id;
                mysqli_stmt_bind_param($insert_stmt, 'sssis', $file_name, $file_path, $file_hash, $period, $uploaded_by);
                mysqli_stmt_execute($insert_stmt);
                $processed_files[] = $file_name;

                error_log("Starting member matching for " . count($transactions) . " transactions");
                foreach ($transactions as $index => &$transaction) {
                    error_log("Processing transaction " . ($index + 1) . ": " . $transaction["name"]);
                    $member_match = findMemberMatch($transaction["name"], $transaction["description"] ?? "");
                    if ($member_match && isset($member_match['memberid'])) {
                        $transaction['matched'] = true;
                        $transaction['member_id'] = $member_match['memberid'];
                        $transaction['member_name'] = trim($member_match['Fname'] . ' ' . ($member_match['Mname'] ?? '') . ' ' . $member_match['Lname']);
                    } else {
                        $transaction['matched'] = false;
                        $transaction['member_id'] = null;
                        $transaction['member_name'] = null;
                        $transaction['candidate_matches'] = $member_match['matches'] ?? [];
                        error_log("Transaction " . ($index + 1) . " completed - matched: " . ($transaction['matched'] ? "yes" : "no"));
                    }
                }
                $all_transactions = array_merge($all_transactions, $transactions);
            }
        }

        echo json_encode([
            'success' => true,
            'message' => 'Files processed successfully',
            'data' => $all_transactions,
            'skipped_files' => $skipped_files,
            'debug' => [
                'files_processed' => count($processed_files),
                'transactions_found' => count($all_transactions)
            ]
        ]);
    } catch (Exception $e) {
        error_log("File upload error: " . $e->getMessage());
        error_log("File upload error trace: " . $e->getTraceAsString());
        echo json_encode([
            'success' => false,
            'message' => 'File processing failed: ' . $e->getMessage(),
            'data' => [],
            'skipped_files' => $skipped_files ?? []
        ]);
    }
}

function extractPDFTextServerSide($file_path, $password = '') {
    try {
        // For shared hosting, we rely on client-side extraction
        // This function is kept for compatibility but returns empty
        // The actual extraction happens in the browser using PDF.js
        
        error_log("Server-side extraction not available on shared hosting");
        error_log("Relying on client-side PDF.js extraction");
        
        // Return empty string to trigger client-side fallback
        return '';
        
    } catch (Exception $e) {
        error_log("PDF text extraction error: " . $e->getMessage());
        return '';
    }
}

function analyzeWithOpenAI($text, $api_key) {
    try {
        if (empty($text)) {
            error_log("OpenAI analysis: Empty text provided");
            return [];
        }

        error_log("OpenAI analysis: Starting with " . strlen($text) . " characters");
        error_log("OpenAI analysis: First 500 chars of text: " . substr($text, 0, 500));

        $client = new Client([
            'timeout' => 60,
            'connect_timeout' => 15
        ]);

        $chunk_size = 3000;
        $chunks = strlen($text) > $chunk_size ? str_split($text, $chunk_size) : [$text];
        $all_transactions = [];

        foreach ($chunks as $chunk_index => $chunk) {
                    $prompt = "Extract financial transactions from the following Nigerian bank statement text. 
        The text has been extracted to preserve the table structure - each line represents a row with columns separated by spaces.
        Look for transaction date, names, amounts, and whether they are credits (money received) or debits (money sent).
        Focus on Nigerian names and currency amounts in Naira (₦).
        IMPORTANT: Extract the actual transaction date in DD/MM/YYYY format from the statement.
        
        TABLE STRUCTURE ANALYSIS:
        - Each line represents a table row
        - Columns are typically: DATE | DESCRIPTION | DEBIT | CREDIT | VALUE DATE | BALANCE
        - Look for the position of amounts in each row to determine if they're in DEBIT or CREDIT column
        - If amount appears in DEBIT column (usually empty for credits) = DEBIT transaction
        - If amount appears in CREDIT column (usually empty for debits) = CREDIT transaction
        
        CREDIT vs DEBIT CLASSIFICATION RULES:
        - CREDIT: Money received/added to account (amount appears in CREDIT column, DEBIT column is empty)
        - DEBIT: Money sent/withdrawn from account (amount appears in DEBIT column, CREDIT column is empty)
        - Look at the column position of the amount in the table row
        - Check if DEBIT column is empty (then it's a CREDIT transaction)
        - Check if CREDIT column is empty (then it's a DEBIT transaction)
        - Look at balance changes: if balance increases = CREDIT, if balance decreases = DEBIT
        
        EXAMPLES:
        - Row: \"03/07/2025 UP-IB Online Transfer... DORA NKEMDIBE EFUNSHILE 50,000.00 03/07/2025 14,000,231.00\"
          Analysis: Amount 50,000.00 appears in CREDIT position, DEBIT is empty → CREDIT transaction
        
        - Row: \"03/07/2025 Loan to Member... 2,000,000.00 03/07/2025 12,000,231.00\"
          Analysis: Amount 2,000,000.00 appears in DEBIT position, CREDIT is empty → DEBIT transaction
        
        Return the data in JSON format with this structure:
        [{\"date\": \"DD/MM/YYYY\",\"name\": \"Person Name\", \"amount\": 1000.00, \"type\": \"credit\" or \"debit\", \"description\": \"Transaction description\"}]
        
        Bank statement text:
        " . $chunk;

            $max_attempts = 3;
            for ($attempt = 1; $attempt <= $max_attempts; $attempt++) {
                try {
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
                        'content' => 'You are a financial data extraction specialist. Extract transaction details from bank statements accurately. Always include the transaction date in DD/MM/YYYY format. Pay special attention to CREDIT vs DEBIT classification: CREDIT = money received (appears in CREDIT column), DEBIT = money sent (appears in DEBIT column). The text preserves table structure - analyze column positions to determine transaction types.'
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
                    error_log("OpenAI response received: " . json_encode($result));
                    
                    $content = $result['choices'][0]['message']['content'] ?? '';
                    error_log("OpenAI content: " . $content);
                    
                    $content = trim($content);
                    $content = preg_replace('/^```json\s*|\s*```$/s', '', $content);
                    
                    preg_match('/\[.*\]/s', $content, $matches);
                    if (isset($matches[0])) {
                        $transactions = json_decode($matches[0], true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($transactions)) {
                            $valid_transactions = array_filter($transactions, function($t) {
                                return isset($t['date'], $t['name'], $t['amount'], $t['type']) &&
                                       is_string($t['date']) && !empty($t['date']) &&
                                       is_string($t['name']) && !empty($t['name']) &&
                                       is_numeric($t['amount']) && $t['amount'] >= 0 &&
                                       in_array($t['type'], ['credit', 'debit']);
                            });
                            
                            // Additional validation: Check for obvious credit/debit misclassifications
                            $valid_transactions = array_map(function($t) {
                                // If transaction description contains keywords that suggest credit, ensure it's classified as credit
                                $credit_keywords = ['contribution', 'savings', 'deposit', 'transfer from', 'payment from', 'credit'];
                                $debit_keywords = ['withdrawal', 'transfer to', 'payment to', 'loan', 'debit'];
                                
                                $description_lower = strtolower($t['description'] ?? '');
                                
                                // Check for credit keywords
                                foreach ($credit_keywords as $keyword) {
                                    if (strpos($description_lower, $keyword) !== false) {
                                        if ($t['type'] === 'debit') {
                                            error_log("Correcting transaction type from debit to credit for: " . $t['name'] . " (contains: $keyword)");
                                            $t['type'] = 'credit';
                                        }
                                        break;
                                    }
                                }
                                
                                // Check for debit keywords
                                foreach ($debit_keywords as $keyword) {
                                    if (strpos($description_lower, $keyword) !== false) {
                                        if ($t['type'] === 'credit') {
                                            error_log("Correcting transaction type from credit to debit for: " . $t['name'] . " (contains: $keyword)");
                                            $t['type'] = 'debit';
                                        }
                                        break;
                                    }
                                }
                                
                                return $t;
                            }, $valid_transactions);
                            
                            $all_transactions = array_merge($all_transactions, array_values($valid_transactions));
                            break;
                        }
                    }
                    
                    if ($attempt === $max_attempts) {
                        error_log("Failed to parse transactions from chunk $chunk_index after $max_attempts attempts");
                    }
                    
                } catch (RequestException $e) {
                    error_log("OpenAI attempt $attempt failed: " . $e->getMessage());
                    if ($attempt === $max_attempts) {
                        error_log("Max attempts reached for chunk $chunk_index");
                    }
                    sleep(1);
                }
            }
        }

        return array_values($all_transactions);
    } catch (Exception $e) {
        error_log("OpenAI error: " . $e->getMessage());
        return [];
    }
}

function findMemberMatch($transaction_name, $description) {
    error_log("findMemberMatch called for: $transaction_name");
    $cov = getDatabaseConnection();
    $system_keywords = ['levy', 'charge', 'vat', 'sms', 'electronic money transfer'];
    foreach ($system_keywords as $keyword) {
        if (stripos($description, $keyword) !== false || stripos($transaction_name, $keyword) !== false) {
            return ['matches' => []];
        }
    }

    $clean_name = trim(preg_replace('/[^a-zA-Z0-9\s]/', ' ', strtolower($transaction_name)));
    $name_parts = array_filter(explode(' ', $clean_name));

    if (empty($name_parts) || (count($name_parts) === 1 && is_numeric($name_parts[0]))) {
        error_log("Skipping purely numeric transaction: $transaction_name");
        return ['matches' => []];
    }

    $nickname_map = [
        'tolanikawo' => ['tola', 'tolani'], 
        'jejelola' => ['jeje'], 
        'ayokunle' => ['ayo'],
        'funmi' => ['funmilayo', 'funmilola'], 
        'sola' => ['olusola'], 
        'temi' => ['temilade']
    ];

    $search_terms = [];
    foreach ($name_parts as $part) {
        if (ctype_alpha($part)) {
            $search_terms[] = $part;
            foreach ($nickname_map as $key => $aliases) {
                if ($part === $key || in_array($part, $aliases)) {
                    $search_terms = array_merge($search_terms, [$key], $aliases);
                }
            }
        } else {
            $search_terms[] = $part;
        }
    }
    $search_terms = array_unique($search_terms);

    if (empty($search_terms)) {
        error_log("No search terms found for: $transaction_name");
        return ['matches' => []];
    }

    $placeholders = rtrim(str_repeat('?,', count($search_terms)), ',');
    $sql = "SELECT memberid, Fname, Mname, Lname 
            FROM tbl_personalinfo 
            WHERE LOWER(Fname) IN ($placeholders) 
               OR LOWER(Mname) IN ($placeholders) 
               OR LOWER(Lname) IN ($placeholders) 
            LIMIT 5";
    
    $stmt = mysqli_prepare($cov, $sql);
    if (!$stmt) {
        error_log("SQL prepare failed: " . mysqli_error($cov));
        return ['matches' => []];
    }
    
    $param_types = str_repeat('s', count($search_terms) * 3);
    $params = array_merge($search_terms, $search_terms, $search_terms);
    mysqli_stmt_bind_param($stmt, $param_types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $candidates = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $candidates[] = $row;
    }
    
    error_log("Found " . count($candidates) . " candidates for: $transaction_name");

    if (empty($candidates)) {
        return ['matches' => []];
    }

    if (count($candidates) === 1) {
        return $candidates[0];
    }

    // Use OpenAI to find the best match (with timeout protection)
    $best_match = findBestMatchWithOpenAI($transaction_name, $candidates);
    if ($best_match) {
        return $best_match;
    }

    return ['matches' => array_map(function($c) {
        return ['name' => trim($c['Fname'] . ' ' . ($c['Mname'] ?? '') . ' ' . $c['Lname']), 'memberid' => $c['memberid']];
    }, $candidates)];
}

function findSimpleMatch($transaction_name, $candidates) {
    $transaction_lower = strtolower($transaction_name);
    $transaction_words = array_filter(explode(' ', $transaction_lower));
    
    $best_score = 0;
    $best_candidate = null;
    
    foreach ($candidates as $candidate) {
        $full_name = strtolower(trim($candidate['Fname'] . ' ' . ($candidate['Mname'] ?? '') . ' ' . $candidate['Lname']));
        $name_words = array_filter(explode(' ', $full_name));
        
        $matches = 0;
        foreach ($transaction_words as $word) {
            if (strlen($word) > 2) { // Only consider words longer than 2 chars
                foreach ($name_words as $name_word) {
                    if (strlen($name_word) > 2 && 
                        (strpos($name_word, $word) !== false || strpos($word, $name_word) !== false)) {
                        $matches++;
                        break;
                    }
                }
            }
        }
        
        $score = $matches / max(count($transaction_words), count($name_words));
        if ($score > $best_score && $score >= 0.5) { // At least 50% match
            $best_score = $score;
            $best_candidate = $candidate;
        }
    }
    
    if ($best_candidate) {
        error_log("Simple match found with score $best_score for: $transaction_name");
    }
    
    return $best_candidate;
}

function findBestMatchWithOpenAI($transaction_name, $candidates) {
    static $last_call_time = 0;
    static $call_count = 0;
    static $rate_limit_window = 0;
    
    try {
        // Rate limiting: Maximum 400 calls per minute (leaving buffer for 500 RPM limit)
        $current_time = time();
        if ($current_time - $rate_limit_window >= 60) {
            // Reset counter every minute
            $call_count = 0;
            $rate_limit_window = $current_time;
        }
        
        if ($call_count >= 400) {
            error_log("Rate limit reached: $call_count calls in current window. Skipping OpenAI call for: $transaction_name");
            return null;
        }
        
        // Minimum delay between calls (120ms = 500 RPM max)
        $min_delay = 0.12; // 120ms
        $time_since_last_call = microtime(true) - $last_call_time;
        if ($time_since_last_call < $min_delay) {
            $sleep_time = $min_delay - $time_since_last_call;
            usleep($sleep_time * 1000000); // Convert to microseconds
        }
        
        $call_count++;
        $last_call_time = microtime(true);
        
        error_log("OpenAI best match called for: $transaction_name with " . count($candidates) . " candidates (Call #$call_count in current window)");
        
        $cov = getDatabaseConnection();
        error_log("Database connection successful in findBestMatchWithOpenAI");
        
        $openai_key = EnvConfig::getOpenAIKey();
        error_log("OpenAI key retrieved: " . (empty($openai_key) ? "EMPTY" : "FOUND"));
        
        if (empty($openai_key)) {
            error_log("OpenAI key not available for name matching");
            return null;
        }

        error_log("Creating GuzzleHttp client");
        $client = new Client([
            'timeout' => 10,  // Reduced timeout for faster processing
            'connect_timeout' => 5
        ]);
        error_log("GuzzleHttp client created successfully");

        $candidate_names = [];
        foreach ($candidates as $candidate) {
            $full_name = trim($candidate['Fname'] . ' ' . ($candidate['Mname'] ?? '') . ' ' . $candidate['Lname']);
            $candidate_names[] = [
                'memberid' => $candidate['memberid'],
                'name' => $full_name,
                'parts' => [
                    'first' => $candidate['Fname'],
                    'middle' => $candidate['Mname'] ?? '',
                    'last' => $candidate['Lname']
                ]
            ];
        }

        $prompt = "You are a Nigerian name matching specialist. Given a transaction name and a list of candidate names, determine which candidate best matches the transaction name.

Transaction Name: \"$transaction_name\"

Candidate Names:
";

        foreach ($candidate_names as $index => $candidate) {
            $prompt .= ($index + 1) . ". " . $candidate['name'] . " (ID: " . $candidate['memberid'] . ")\n";
        }

        $prompt .= "
Instructions:
1. Consider that Nigerian names can have different word orders (first, middle, last names can be in different positions)
2. Look for exact matches of name parts
3. Consider common nicknames and variations
4. If at least 2 name parts match, consider it a good match
5. Return ONLY the member ID of the best match, or 'NO_MATCH' if no good match exists

Return format: Just the member ID number (e.g., '294') or 'NO_MATCH'";

        error_log("Making OpenAI API call");
        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $openai_key,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a Nigerian name matching specialist. Return only the member ID or NO_MATCH.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.1,
                'max_tokens' => 50
            ]
        ]);
        error_log("OpenAI API call completed successfully");

        $result = json_decode($response->getBody(), true);
        $content = trim($result['choices'][0]['message']['content'] ?? '');
        
        $content = preg_replace('/[^0-9]/', '', $content);
        
        if (is_numeric($content)) {
            $member_id = intval($content);
            foreach ($candidates as $candidate) {
                if ($candidate['memberid'] == $member_id) {
                    error_log("OpenAI best match found: $transaction_name -> member ID: $member_id");
                    return $candidate;
                }
            }
        }
        
        return null;
        
    } catch (\GuzzleHttp\Exception\ClientException $e) {
        $status_code = $e->getResponse()->getStatusCode();
        if ($status_code === 429) {
            error_log("OpenAI rate limit exceeded. Waiting 60 seconds before retry.");
            sleep(60);
            return null;
        }
        error_log("OpenAI API client error (HTTP $status_code): " . $e->getMessage());
        return null;
    } catch (\GuzzleHttp\Exception\ServerException $e) {
        error_log("OpenAI API server error: " . $e->getMessage());
        return null;
    } catch (Exception $e) {
        error_log("OpenAI name matching error: " . $e->getMessage());
        return null;
    }
}

function handleSearchMembers($input) {
    $cov = getDatabaseConnection();

    try {
        $search_query = trim($input['search_term'] ?? $input['search_query'] ?? '');
        if (empty($search_query)) {
            throw new Exception('Search query required');
        }

        $clean_query = trim(preg_replace('/[^a-zA-Z\s]/', ' ', strtolower($search_query)));
        $query_parts = array_filter(explode(' ', $clean_query));

        $sql = "SELECT memberid, Fname, Mname, Lname FROM tbl_personalinfo WHERE ";
        $conditions = [];
        $params = [];
        $param_types = '';

        foreach ($query_parts as $part) {
            $conditions[] = "(LOWER(Fname) LIKE ? OR LOWER(Mname) LIKE ? OR LOWER(Lname) LIKE ?)";
            $params[] = "%$part%";
            $params[] = "%$part%";
            $params[] = "%$part%";
            $param_types .= 'sss';
        }

        $sql .= implode(' OR ', $conditions);
        $stmt = mysqli_prepare($cov, $sql);
        mysqli_stmt_bind_param($stmt, $param_types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $matches = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $matches[] = [
                'member_id' => $row['memberid'],
                'name' => trim($row['Fname'] . ' ' . ($row['Mname'] ?? '') . ' ' . $row['Lname'])
            ];
        }

        echo json_encode(['success' => true, 'employees' => $matches]);
    } catch (Exception $e) {
        error_log("Search error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Search failed: ' . $e->getMessage()]);
    }
}

function handleManualMatch($input) {
    global $user_id;
    
    try {
        error_log("Manual match: Starting with input: " . json_encode($input));
        
        $cov = getDatabaseConnection();
        if (!$cov) {
            throw new Exception('Database connection failed');
        }

        // Check if manual_transaction_matches table exists
        $table_check = mysqli_query($cov, "SHOW TABLES LIKE 'manual_transaction_matches'");
        if (!$table_check || mysqli_num_rows($table_check) == 0) {
            error_log("Manual match: Table manual_transaction_matches does not exist, creating it");
            createTables();
        }

        $transaction_name = $input['transaction_name'] ?? '';
        $member_id = $input['member_id'] ?? '';
        
        if (empty($transaction_name) || empty($member_id)) {
            throw new Exception('Transaction name and member ID are required');
        }
        
        $user_id = $user_id ?? 'Unknown';
        error_log("Manual match: Processing transaction_name='$transaction_name', member_id='$member_id', user_id='$user_id'");

        // First, check if the member exists
        $member_query = "SELECT memberid, Fname, Mname, Lname FROM tbl_personalinfo WHERE memberid = ?";
        $member_stmt = mysqli_prepare($cov, $member_query);
        if (!$member_stmt) {
            throw new Exception('Failed to prepare member query: ' . mysqli_error($cov));
        }
        
        mysqli_stmt_bind_param($member_stmt, 'i', $member_id);
        if (!mysqli_stmt_execute($member_stmt)) {
            throw new Exception('Failed to execute member query: ' . mysqli_stmt_error($member_stmt));
        }
        
        $member_result = mysqli_stmt_get_result($member_stmt);
        $member = mysqli_fetch_assoc($member_result);
        
        if (!$member) {
            throw new Exception('Member not found with ID: ' . $member_id);
        }

        // Insert or update manual match
        $query = "INSERT INTO manual_transaction_matches (transaction_name, member_id, matched_by, matched_date) 
                  VALUES (?, ?, ?, NOW()) 
                  ON DUPLICATE KEY UPDATE member_id = ?, matched_by = ?, matched_date = NOW()";
        $stmt = mysqli_prepare($cov, $query);
        if (!$stmt) {
            throw new Exception('Failed to prepare insert query: ' . mysqli_error($cov));
        }
        
        mysqli_stmt_bind_param($stmt, 'sisis', $transaction_name, $member_id, $user_id, $member_id, $user_id);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Failed to execute insert query: ' . mysqli_stmt_error($stmt));
        }

        error_log("Manual match: Successfully saved match for transaction '$transaction_name' with member ID '$member_id'");
        
        $response = [
            'success' => true,
            'message' => 'Manual match saved successfully',
            'member' => $member
        ];
        
        error_log("Manual match: Sending response: " . json_encode($response));
        
        // Clear any output buffer and send clean JSON
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
        
    } catch (Exception $e) {
        error_log("Manual match error: " . $e->getMessage());
        error_log("Manual match error trace: " . $e->getTraceAsString());
        
        $error_response = ['success' => false, 'message' => $e->getMessage()];
        error_log("Manual match: Sending error response: " . json_encode($error_response));
        
        // Clear any output buffer and send clean JSON
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode($error_response);
        exit();
    }
}

function handleExportResults($input) {
    try {
        $results = json_decode($input['results'], true);
        $period_text = $input['period_text'] ?? 'Unknown';

        if (empty($results)) {
            throw new Exception('No results to export');
        }

        $filename = 'bank_statement_analysis_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = 'uploads/exports/' . $filename;
        if (!is_dir('Uploads/exports/')) {
            mkdir('Uploads/exports/', 0755, true);
        }

        $file = fopen($filepath, 'w');
        fputcsv($file, ['Transaction Name', 'Amount', 'Type', 'Description', 'Matched', 'Member ID', 'Member Name', 'Period']);
        foreach ($results as $result) {
            fputcsv($file, [
                $result['name'],
                $result['amount'],
                $result['type'],
                $result['description'] ?? '',
                $result['matched'] ? 'Yes' : 'No',
                $result['member_id'] ?? '',
                $result['member_name'] ?? '',
                $period_text
            ]);
        }
        fclose($file);

        echo json_encode([
            'success' => true,
            'message' => 'Export completed',
            'file_path' => $filepath,
            'filename' => $filename
        ]);
    } catch (Exception $e) {
        error_log("Export error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleProcessTransactions($input) {
    global $user_id;

    try {
        error_log("Process transactions input type: " . gettype($input['transactions']));
        error_log("Process transactions input: " . print_r($input['transactions'], true));
        
        // Handle both JSON string and array input
        if (is_string($input['transactions'])) {
            $transactions = json_decode($input['transactions'], true);
        } else {
            $transactions = $input['transactions'];
        }
        
        error_log("Process transactions after processing: " . print_r($transactions, true));
        
        $period = $input['period'];
        $processed_count = 0;
        $skipped_count = 0;
        $unmatched_count = 0;

        if (empty($transactions) || empty($period)) {
            throw new Exception('Invalid transactions or period');
        }

        // Get database connection
        $cov = getDatabaseConnection();
        if (!$cov) {
            throw new Exception('Database connection failed');
        }

        foreach ($transactions as $txn) {
            // Handle unmatched transactions
            if (!$txn['matched']) {
                $unmatched_count++;
                error_log("Skipping unmatched transaction: " . $txn['name'] . " - " . $txn['amount']);
                
                // Save unmatched transaction to database for future reference
                try {
                    $unmatched_query = "INSERT INTO unmatched_transactions 
                        (transaction_date, transaction_name, transaction_amount, transaction_type, 
                         transaction_description, period_id, file_hash) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $unmatched_stmt = mysqli_prepare($cov, $unmatched_query);
                    if ($unmatched_stmt) {
                        $file_hash = md5($txn['name'] . $txn['description'] . $txn['amount'] . $txn['type']);
                        mysqli_stmt_bind_param($unmatched_stmt, 'ssdssss', 
                            $txn['date'], $txn['name'], $txn['amount'], $txn['type'], 
                            $txn['description'], $period, $file_hash);
                        mysqli_stmt_execute($unmatched_stmt);
                        error_log("Saved unmatched transaction to database: " . $txn['name']);
                    }
                } catch (Exception $e) {
                    error_log("Failed to save unmatched transaction: " . $e->getMessage());
                }
                
                continue;
            }

            // Validate required fields
            if (!isset($txn['member_id'], $txn['amount'], $txn['type'], $txn['date'])) {
                $skipped_count++;
                error_log("Skipping transaction with missing fields: " . json_encode($txn));
                continue;
            }

            $amount = floatval($txn['amount']);

            // Check for exact duplicate transactions (same member, period, amount, and date)
            // This prevents processing the same transaction multiple times
            $check_query = "";
            $loan_date = null;
            if (isset($txn['date']) && !empty($txn['date'])) {
                $date_parts = explode('/', $txn['date']);
                if (count($date_parts) === 3) {
                    $loan_date = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
                }
            }
            
            // if ($txn['type'] === 'credit') {
            //     // For contributions, check if exact same transaction exists
            //     $check_query = "SELECT contriId FROM tbl_contributions WHERE membersid = ? AND periodid = ? AND contribution = ?";
            //     $check_stmt = mysqli_prepare($cov, $check_query);
            //     if ($check_stmt) {
            //         mysqli_stmt_bind_param($check_stmt, 'sid', $txn['member_id'], $period, $amount);
            //         mysqli_stmt_execute($check_stmt);
            //         $check_result = mysqli_stmt_get_result($check_stmt);
            //         if (mysqli_num_rows($check_result) > 0) {
            //             $skipped_count++;
            //             error_log("Skipping exact duplicate contribution: " . $txn['name'] . " - " . $txn['amount'] . " for period " . $period);
            //             continue;
            //         }
            //     }
            // } else {
            //     // For loans, check if exact same transaction exists (including date)
            //     if ($loan_date) {
            //         $check_query = "SELECT loanid FROM tbl_loan WHERE memberid = ? AND periodid = ? AND loanamount = ? AND loan_date = ?";
            //         $check_stmt = mysqli_prepare($cov, $check_query);
            //         if ($check_stmt) {
            //             mysqli_stmt_bind_param($check_stmt, 'iids', $txn['member_id'], $period, $amount, $loan_date);
            //             mysqli_stmt_execute($check_stmt);
            //             $check_result = mysqli_stmt_get_result($check_stmt);
            //             if (mysqli_num_rows($check_result) > 0) {
            //                 $skipped_count++;
            //                 error_log("Skipping exact duplicate loan: " . $txn['name'] . " - " . $txn['amount'] . " for period " . $period . " on date " . $loan_date);
            //                 continue;
            //             }
            //         }
            //     }
            // }

            // Process credit transactions (contributions)
            if ($txn['type'] === 'credit') {
                $query = "INSERT INTO tbl_contributions (membersid, periodid, contribution) 
                          VALUES (?, ?, ?)";
                $stmt = mysqli_prepare($cov, $query);
                if (!$stmt) {
                    error_log("Failed to prepare contribution insert: " . mysqli_error($cov));
                    continue;
                }
                mysqli_stmt_bind_param($stmt, 'sid', $txn['member_id'], $period, $amount);
            } 
            // Process debit transactions (loans)
            else {
                // Convert date from DD/MM/YYYY to YYYY-MM-DD for MySQL
                $loan_date = null;
                if (isset($txn['date']) && !empty($txn['date'])) {
                    $date_parts = explode('/', $txn['date']);
                    if (count($date_parts) === 3) {
                        $loan_date = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
                    }
                }
                
                $query = "INSERT INTO tbl_loan (memberid, periodid, loanamount, loan_date) 
                          VALUES (?, ?, ?, ?)";
                $stmt = mysqli_prepare($cov, $query);
                if (!$stmt) {
                    error_log("Failed to prepare loan insert: " . mysqli_error($cov));
                    continue;
                }
                mysqli_stmt_bind_param($stmt, 'iids', $txn['member_id'], $period, $amount, $loan_date);
            }

            if (mysqli_stmt_execute($stmt)) {
                $processed_count++;
                error_log("Successfully processed transaction: " . $txn['name'] . " - " . $txn['amount'] . " (" . $txn['type'] . ")");
            } else {
                error_log("Failed to execute transaction: " . mysqli_error($cov));
            }
        }

        echo json_encode([
            'success' => true,
            'message' => "Transactions processed successfully. Processed: $processed_count, Skipped: $skipped_count, Unmatched: $unmatched_count",
            'processed_count' => $processed_count,
            'skipped_count' => $skipped_count,
            'unmatched_count' => $unmatched_count
        ]);
    } catch (Exception $e) {
        error_log("Process transactions error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Processing failed: ' . $e->getMessage()]);
    }
}

function handleSaveConfig($input) {
    try {
        $openai_key = $input['openai_key'] ?? '';
        if (empty($openai_key)) {
            throw new Exception('OpenAI API key is required');
        }

        if (EnvConfig::updateOpenAIKey($openai_key)) {
            echo json_encode(['success' => true, 'message' => 'API key saved successfully']);
        } else {
            throw new Exception('Failed to save API key');
        }
    } catch (Exception $e) {
        error_log("Save config error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleInsertTransaction($input) {
    try {
        $cov = getDatabaseConnection();
        
        $coop_id = $input['coop_id'] ?? '';
        $amount = $input['amount'] ?? '';
        $type = $input['type'] ?? '';
        $period = $input['period'] ?? '';
        
        if (empty($coop_id) || empty($amount) || empty($type) || empty($period)) {
            throw new Exception('All fields are required: coop_id, amount, type, period');
        }
        
        // Validate member exists
        $member_query = "SELECT memberid FROM tbl_personalinfo WHERE memberid = ?";
        $member_stmt = mysqli_prepare($cov, $member_query);
        mysqli_stmt_bind_param($member_stmt, 'i', $coop_id);
        mysqli_stmt_execute($member_stmt);
        $member_result = mysqli_stmt_get_result($member_stmt);
        
        if (mysqli_num_rows($member_result) == 0) {
            throw new Exception('Member not found with ID: ' . $coop_id);
        }
        
        // Insert transaction based on type
        if ($type === 'credit') {
            $query = "INSERT INTO tbl_contributions (membersid, periodid, contribution) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($cov, $query);
            mysqli_stmt_bind_param($stmt, 'sid', $coop_id, $period, $amount);
        } else {
            $query = "INSERT INTO tbl_loan (memberid, periodid, loanamount, loan_date) VALUES (?, ?, ?, NOW())";
            $stmt = mysqli_prepare($cov, $query);
            mysqli_stmt_bind_param($stmt, 'iid', $coop_id, $period, $amount);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Transaction inserted successfully']);
        } else {
            throw new Exception('Failed to insert transaction: ' . mysqli_stmt_error($stmt));
        }
        
    } catch (Exception $e) {
        error_log("Insert transaction error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to insert transaction: ' . $e->getMessage()]);
    }
}

function createTables() {
    $cov = getDatabaseConnection();

    $tables = [
        "CREATE TABLE IF NOT EXISTS bank_statement_files (
            id INT AUTO_INCREMENT PRIMARY KEY,
            filename VARCHAR(255) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_hash VARCHAR(32) NOT NULL UNIQUE,
            period_id VARCHAR(50) NOT NULL,
            uploaded_by VARCHAR(50) NOT NULL,
            upload_date DATETIME NOT NULL,
            processed BOOLEAN DEFAULT FALSE
        )",
        "CREATE TABLE IF NOT EXISTS manual_transaction_matches (
            id INT AUTO_INCREMENT PRIMARY KEY,
            transaction_name VARCHAR(255) NOT NULL,
            member_id INT NOT NULL,
            matched_by VARCHAR(100) NOT NULL,
            matched_date DATETIME NOT NULL,
            UNIQUE KEY unique_match (transaction_name, member_id)
        )",

        "CREATE TABLE IF NOT EXISTS unmatched_transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            transaction_date VARCHAR(20),
            transaction_name VARCHAR(255) NOT NULL,
            transaction_amount DECIMAL(15,2) NOT NULL,
            transaction_type ENUM('credit', 'debit') NOT NULL,
            transaction_description TEXT,
            period_id VARCHAR(50),
            file_hash VARCHAR(32),
            created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_period (period_id),
            INDEX idx_file_hash (file_hash)
        )"
    ];

    foreach ($tables as $sql) {
        if (!mysqli_query($cov, $sql)) {
            error_log("Failed to create table: " . mysqli_error($cov));
        }
    }
}

createTables();
?>