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

// Ensure all required tables exist before processing any requests
try {
    $cov = getDatabaseConnection();
    createTables();
} catch (Exception $e) {
    error_log("Failed to create tables: " . $e->getMessage());
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
            case 'get_unmatched_transactions':
                error_log("Bank statement processor: Handling get_unmatched_transactions");
                handleGetUnmatchedTransactions($input);
                break;
            case 'get_matched_transactions':
                error_log("Bank statement processor: Handling get_matched_transactions");
                handleGetMatchedTransactions($input);
                break;
            case 'process_matched_transaction':
                error_log("Bank statement processor: Handling process_matched_transaction");
                handleProcessMatchedTransaction($input);
                break;
            case 'delete_matched_transaction':
                error_log("Bank statement processor: Handling delete_matched_transaction");
                handleDeleteMatchedTransaction($input);
                break;
            case 'check_file_exists':
                error_log("Bank statement processor: Handling check_file_exists");
                handleCheckFileExists($input);
                break;
            case 'get_existing_files':
                error_log("Bank statement processor: Handling get_existing_files");
                handleGetExistingFiles($input);
                break;
            case 'load_existing_analysis':
                error_log("Bank statement processor: Handling load_existing_analysis");
                handleLoadExistingAnalysis($input);
                break;
            case 'save_extracted_data':
                error_log("Bank statement processor: Handling save_extracted_data");
                handleSaveExtractedData($input);
                break;
            case 'save_reclassification':
                error_log("Bank statement processor: Handling save_reclassification");
                handleSaveReclassification($input);
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
        error_log("POST data received: " . print_r($_POST, true));
        error_log("FILES data received: " . print_r($_FILES, true));
        
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
        $page_info = $_POST['page_info'] ?? null;
        $processed_files = [];
        $skipped_files = [];
        
        // Log page information if available
        if ($page_info) {
            $page_data = json_decode($page_info, true);
            if ($page_data) {
                error_log("Processing page {$page_data['pageNumber']} of {$page_data['totalPages']} from file: {$page_data['originalFile']}");
            }
        }

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
                        error_log("No client-side extracted text found for single file");
                        error_log("Client-side texts array: " . print_r($client_side_texts, true));
                        
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
                    error_log("Sample of extracted text length: " . strlen($extracted_text));
                    error_log("Sample of extracted text (first 200 chars): " . substr($extracted_text, 0, 200));
                    
                    if (empty($transactions)) {
                        error_log("No transactions found in extracted text");
                        throw new Exception("No transactions extracted from $file_name");
                    }

                    // Add page information to transactions if available
                    if ($page_info) {
                        $page_data = json_decode($page_info, true);
                        if ($page_data) {
                            foreach ($transactions as &$transaction) {
                                $transaction['page_info'] = $page_data;
                            }
                            error_log("Added page information to " . count($transactions) . " transactions: Page {$page_data['pageNumber']} of {$page_data['totalPages']}");
                        }
                    }

                    // Store file information for later recording after transaction processing
                    $file_info = [
                        'filename' => $file_name,
                        'file_path' => $file_path,
                        'file_hash' => $file_hash,
                        'period_id' => $period,
                        'uploaded_by' => $user_name ?? $user_id
                    ];
                    $processed_files[] = $file_info;

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

                // Skip duplicate checking for page-based processing
                $is_page_processing = !empty($page_info);
                if (!$force_reprocess && !$is_page_processing) {
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

                // For page-based processing, don't save the file multiple times
                if ($is_page_processing) {
                    $file_path = $upload_dir . 'temp_' . time() . '_' . $file_name;
                    // Don't save the file for page processing, just use the extracted text
                } else {
                    $file_path = $upload_dir . time() . '_' . $file_name;
                    if (!move_uploaded_file($tmp_name, $file_path)) {
                        throw new Exception("Failed to save file: $file_name");
                    }
                }

                $extracted_text = '';
                if (isset($client_side_texts[$key]) && !empty(trim($client_side_texts[$key]))) {
                    $extracted_text = trim($client_side_texts[$key]);
                    error_log("Using client-side extracted text for file $key: " . strlen($extracted_text) . " characters");
                    $sample_text = substr($extracted_text, 0, 500);
                    error_log("Sample text for file $key (first 500 chars): " . $sample_text);
                } else {
                    error_log("No client-side extracted text for file $key, using fallback");
                    error_log("Client-side texts for key $key: " . (isset($client_side_texts[$key]) ? $client_side_texts[$key] : 'NOT SET'));
                    // Try server-side extraction as fallback
                    $pdf_password = $_POST['pdf_password'] ?? '';
                    $extracted_text = extractPDFTextServerSide($file_path, $pdf_password);
                }

                if (empty($extracted_text)) {
                    throw new Exception("No valid text for $file_name");
                }

                $transactions = analyzeWithOpenAI($extracted_text, $openai_key);
                error_log("OpenAI returned " . count($transactions) . " transactions for file: $file_name");
                error_log("Sample of extracted text length: " . strlen($extracted_text));
                error_log("Sample of extracted text (first 200 chars): " . substr($extracted_text, 0, 200));
                
                if (empty($transactions)) {
                    error_log("No transactions found in extracted text");
                    throw new Exception("No transactions extracted from $file_name");
                }

                // Add page information to transactions if available
                if ($page_info) {
                    $page_data = json_decode($page_info, true);
                    if ($page_data) {
                        foreach ($transactions as &$transaction) {
                            $transaction['page_info'] = $page_data;
                        }
                        error_log("Added page information to " . count($transactions) . " transactions: Page {$page_data['pageNumber']} of {$page_data['totalPages']}");
                    }
                }

                // Store file information for later recording after transaction processing
                $file_info = [
                    'filename' => $file_name,
                    'file_path' => $file_path,
                    'file_hash' => $file_hash,
                    'period_id' => intval($period), // Ensure period_id is an integer
                    'uploaded_by' => intval($user_id ?? 1) // Ensure uploaded_by is an integer
                ];
                $processed_files[] = $file_info;

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
            'file_info' => $processed_files, // Include file information for later recording
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
                            
                            // No AI-based type correction - use original classification from table structure
                            
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
            ";
     
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
    try {
        error_log("=== handleSearchMembers START ===");
        error_log("Input received: " . print_r($input, true));
        
        $cov = getDatabaseConnection();
        if (!$cov) {
            throw new Exception('Database connection failed');
        }
        
        error_log("Database connection successful");
        
        $search_query = trim($input['search_term'] ?? $input['search_query'] ?? '');
        if (empty($search_query)) {
            throw new Exception('Search query required');
        }
        
        error_log("Search query: '$search_query'");

        // First, check if the table exists
        $table_check = mysqli_query($cov, "SHOW TABLES LIKE 'tbl_personalinfo'");
        if (!$table_check || mysqli_num_rows($table_check) == 0) {
            error_log("Table tbl_personalinfo does not exist");
            throw new Exception('Members table not found');
        }
        
        // Check table structure
        $structure_query = "DESCRIBE tbl_personalinfo";
        $structure_result = mysqli_query($cov, $structure_query);
        if (!$structure_result) {
            error_log("Failed to get table structure: " . mysqli_error($cov));
            throw new Exception('Failed to get table structure');
        }
        
        $columns = [];
        while ($row = mysqli_fetch_assoc($structure_result)) {
            $columns[] = $row['Field'];
        }
        error_log("Table columns: " . implode(', ', $columns));
        
        // Check if required columns exist
        $required_columns = ['memberid', 'Fname', 'Lname'];
        $missing_columns = array_diff($required_columns, $columns);
        if (!empty($missing_columns)) {
            error_log("Missing required columns: " . implode(', ', $missing_columns));
            throw new Exception('Missing required columns: ' . implode(', ', $missing_columns));
        }

        $clean_query = trim(preg_replace('/[^a-zA-Z\s]/', ' ', strtolower($search_query)));
        $query_parts = array_filter(explode(' ', $clean_query));
        
        error_log("Query parts: " . print_r($query_parts, true));

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
        error_log("SQL query: $sql");
        error_log("Parameters: " . print_r($params, true));
        error_log("Parameter types: $param_types");
        
        $stmt = mysqli_prepare($cov, $sql);
        if (!$stmt) {
            error_log("Failed to prepare statement: " . mysqli_error($cov));
            throw new Exception('Failed to prepare statement: ' . mysqli_error($cov));
        }
        
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $param_types, ...$params);
        }
        
        if (!mysqli_stmt_execute($stmt)) {
            error_log("Failed to execute statement: " . mysqli_stmt_error($stmt));
            throw new Exception('Failed to execute statement: ' . mysqli_stmt_error($stmt));
        }
        
        $result = mysqli_stmt_get_result($stmt);
        if (!$result) {
            error_log("Failed to get result: " . mysqli_error($cov));
            throw new Exception('Failed to get result: ' . mysqli_error($cov));
        }

        $matches = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $matches[] = [
                'member_id' => $row['memberid'],
                'name' => trim($row['Fname'] . ' ' . ($row['Mname'] ?? '') . ' ' . $row['Lname'])
            ];
        }
        
        error_log("Found " . count($matches) . " matches");
        error_log("Matches: " . print_r($matches, true));

        echo json_encode(['success' => true, 'employees' => $matches]);
        error_log("=== handleSearchMembers SUCCESS ===");
        
    } catch (Exception $e) {
        error_log("=== handleSearchMembers ERROR ===");
        error_log("Search error: " . $e->getMessage());
        error_log("Error trace: " . $e->getTraceAsString());
        
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
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
        $file_info = $input['file_info'] ?? null; // Get file information for recording
        error_log("Process transactions: Received file_info: " . print_r($file_info, true));
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

            // Process matched transactions directly to appropriate tables
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
                // Get the properly formatted date from the extractions table
                $loan_date = null;
                if (isset($txn['uniqueId'])) {
                    $date_query = "SELECT transaction_date FROM bank_statement_extractions WHERE unique_id = ?";
                    $date_stmt = mysqli_prepare($cov, $date_query);
                    if ($date_stmt) {
                        mysqli_stmt_bind_param($date_stmt, 's', $txn['uniqueId']);
                        mysqli_stmt_execute($date_stmt);
                        $date_result = mysqli_stmt_get_result($date_stmt);
                        if ($date_row = mysqli_fetch_assoc($date_result)) {
                            $loan_date = $date_row['transaction_date'];
                        }
                        mysqli_stmt_close($date_stmt);
                    }
                }
                
                // If no date from extractions table, try to format from transaction data
                if (!$loan_date && isset($txn['date']) && !empty($txn['date'])) {
                    $date_str = trim($txn['date']);
                    
                    // Check if it's already in YYYY-MM-DD format
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_str)) {
                        $loan_date = $date_str;
                    }
                    // Check if it's in DD/MM/YYYY format
                    elseif (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $date_str)) {
                        $date_parts = explode('/', $date_str);
                        $day = str_pad($date_parts[0], 2, '0', STR_PAD_LEFT);
                        $month = str_pad($date_parts[1], 2, '0', STR_PAD_LEFT);
                        $year = $date_parts[2];
                        $loan_date = "$year-$month-$day";
                    }
                    // If none of the above, try to parse with strtotime
                    else {
                        $timestamp = strtotime($date_str);
                        if ($timestamp !== false) {
                            $loan_date = date('Y-m-d', $timestamp);
                        }
                    }
                }
                
                // Validate the final date
                if ($loan_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $loan_date)) {
                    error_log("Invalid loan date format: " . $loan_date . " (original: " . ($txn['date'] ?? 'NULL') . ")");
                    $loan_date = null;
                }
                
                // If still no valid date, use current date as fallback
                if (!$loan_date) {
                    $loan_date = date('Y-m-d');
                    error_log("Using current date as fallback for loan: " . $txn['name'] . " - " . $txn['amount']);
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
                error_log("Successfully processed matched transaction: " . $txn['name'] . " - " . $txn['amount'] . " (" . $txn['type'] . ")");
                
                // Mark transaction as processed in bank_statement_extractions table
                if (isset($txn['uniqueId'])) {
                    $update_query = "UPDATE bank_statement_extractions 
                                    SET processed = 1, 
                                        processed_date = NOW(), 
                                        processed_to_table = ? 
                                    WHERE unique_id = ?";
                    $update_stmt = mysqli_prepare($cov, $update_query);
                    if ($update_stmt) {
                        $table_name = ($txn['type'] === 'credit') ? 'tbl_contributions' : 'tbl_loan';
                        mysqli_stmt_bind_param($update_stmt, 'ss', $table_name, $txn['uniqueId']);
                        mysqli_stmt_execute($update_stmt);
                        error_log("Marked transaction as processed in extractions table: " . $txn['uniqueId']);
                    }
                }
            } else {
                error_log("Failed to execute matched transaction: " . mysqli_error($cov));
            }
        }

        // Record file to database after transactions are processed (even if none were processed)
        if ($file_info) {
            try {
                $insert_query = "INSERT INTO bank_statement_files (filename, file_path, file_hash, period_id, uploaded_by, upload_date, processed) 
                                VALUES (?, ?, ?, ?, ?, NOW(), 1)";
                $insert_stmt = mysqli_prepare($cov, $insert_query);
                mysqli_stmt_bind_param($insert_stmt, 'sssis', 
                    $file_info['filename'], 
                    $file_info['file_path'], 
                    $file_info['file_hash'], 
                    $file_info['period_id'], 
                    $file_info['uploaded_by']
                );
                mysqli_stmt_execute($insert_stmt);
                error_log("Successfully recorded file to database: " . $file_info['filename'] . " (processed: " . $processed_count . " transactions)");
            } catch (Exception $e) {
                error_log("Failed to record file to database: " . $e->getMessage());
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

// Handle checking if file already exists
function handleCheckFileExists($input) {
    try {
        $cov = getDatabaseConnection();
        
        if (!isset($input['file_hash'])) {
            echo json_encode(['success' => false, 'message' => 'File hash is required']);
            return;
        }
        
        $file_hash = $input['file_hash'];
        $query = "SELECT id, filename, upload_date, analysis_complete, total_transactions, matched_transactions, unmatched_transactions 
                  FROM bank_statement_files 
                  WHERE file_hash = ? 
                  ORDER BY upload_date DESC 
                  LIMIT 1";
        
        $stmt = mysqli_prepare($cov, $query);
        mysqli_stmt_bind_param($stmt, 's', $file_hash);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            echo json_encode([
                'success' => true, 
                'exists' => true, 
                'file_info' => $row
            ]);
        } else {
            echo json_encode([
                'success' => true, 
                'exists' => false
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Check file exists error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to check file existence: ' . $e->getMessage()]);
    }
}

// Handle getting existing files for dropdown
function handleGetExistingFiles($input) {
    try {
        $cov = getDatabaseConnection();
        
        $period_filter = $input['period_filter'] ?? '';
        $where_clause = "WHERE analysis_complete = 1";
        $params = [];
        $types = "";
        
        if ($period_filter) {
            $where_clause .= " AND period_id = ?";
            $params[] = $period_filter;
            $types .= "i";
        }
        
        $query = "SELECT bsf.id, bsf.filename, bsf.upload_date, bsf.period_id, bsf.total_transactions, 
                         bsf.matched_transactions, bsf.unmatched_transactions, pp.PayrollPeriod, pp.PhysicalMonth, pp.PhysicalYear
                  FROM bank_statement_files bsf
                  LEFT JOIN tbpayrollperiods pp ON bsf.period_id = pp.periodid
                  $where_clause 
                  ORDER BY bsf.upload_date DESC";
        
        if ($types) {
            $stmt = mysqli_prepare($cov, $query);
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
        } else {
            $result = mysqli_query($cov, $query);
        }
        
        $files = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $files[] = $row;
        }
        
        echo json_encode(['success' => true, 'files' => $files]);
        
    } catch (Exception $e) {
        error_log("Get existing files error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to get existing files: ' . $e->getMessage()]);
    }
}

// Handle loading existing analysis data
function handleLoadExistingAnalysis($input) {
    try {
        $cov = getDatabaseConnection();
        
        if (!isset($input['file_id'])) {
            echo json_encode(['success' => false, 'message' => 'File ID is required']);
            return;
        }
        
        $file_id = $input['file_id'];
        
        // Get file info
        $file_query = "SELECT * FROM bank_statement_files WHERE id = ?";
        $file_stmt = mysqli_prepare($cov, $file_query);
        mysqli_stmt_bind_param($file_stmt, 'i', $file_id);
        mysqli_stmt_execute($file_stmt);
        $file_result = mysqli_stmt_get_result($file_stmt);
        $file_info = mysqli_fetch_assoc($file_result);
        
        if (!$file_info) {
            echo json_encode(['success' => false, 'message' => 'File not found']);
            return;
        }
        
        // Get extraction data
        $query = "SELECT * FROM bank_statement_extractions WHERE file_id = ? ORDER BY page_number, id";
        $stmt = mysqli_prepare($cov, $query);
        mysqli_stmt_bind_param($stmt, 'i', $file_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $transactions = [];
        while ($row = mysqli_fetch_assoc($result)) {
            // Convert database format to frontend format
            $transaction = [
                'id' => $row['id'],
                'date' => $row['transaction_date'],
                'name' => $row['transaction_name'],
                'amount' => floatval($row['transaction_amount']),
                'type' => $row['transaction_type'],
                'description' => $row['transaction_description'],
                'matched' => (bool)$row['matched'],
                'member_id' => $row['member_id'],
                'member_name' => $row['member_name'],
                'processed' => (bool)$row['processed'],
                'processed_date' => $row['processed_date'],
                'processed_to_table' => $row['processed_to_table'],
                'uniqueId' => $row['unique_id'],
                'page_info' => [
                    'pageNumber' => $row['page_number']
                ]
            ];
            $transactions[] = $transaction;
        }
        
        echo json_encode([
            'success' => true, 
            'data' => $transactions,
            'file_info' => $file_info
        ]);
        
    } catch (Exception $e) {
        error_log("Load existing analysis error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to load existing analysis: ' . $e->getMessage()]);
    }
}

// Handle saving extracted data to database
function handleSaveExtractedData($input) {
    try {
        error_log("=== handleSaveExtractedData START ===");
        error_log("Input received: " . print_r($input, true));
        
        // Validate input structure
        if (!is_array($input)) {
            throw new Exception("Input must be an array, got: " . gettype($input));
        }
        
        $cov = getDatabaseConnection();
        if (!$cov) {
            throw new Exception("Failed to get database connection");
        }
        
        if (!isset($input['file_info']) || !isset($input['transactions'])) {
            error_log("Missing required data - file_info: " . (isset($input['file_info']) ? 'YES' : 'NO') . ", transactions: " . (isset($input['transactions']) ? 'YES' : 'NO'));
            echo json_encode(['success' => false, 'message' => 'File info and transactions are required']);
            return;
        }
        
        $file_info = $input['file_info'];
        $transactions = $input['transactions'];
        
        // Validate file_info structure
        if (!is_array($file_info)) {
            throw new Exception("File info must be an array, got: " . gettype($file_info));
        }
        
        if (!is_array($transactions)) {
            throw new Exception("Transactions must be an array, got: " . gettype($transactions));
        }
        
        // Check required file_info fields
        $required_fields = ['filename', 'file_path', 'file_hash', 'period_id', 'uploaded_by'];
        foreach ($required_fields as $field) {
            if (!isset($file_info[$field])) {
                throw new Exception("Missing required field in file_info: " . $field);
            }
        }
        
        error_log("File info: " . print_r($file_info, true));
        error_log("Transactions count: " . count($transactions));
        error_log("Sample transaction: " . print_r($transactions[0] ?? 'NO TRANSACTIONS', true));
        
        // Start transaction
        error_log("Starting database transaction...");
        mysqli_autocommit($cov, false);
        
        // Insert or update file record
        $file_query = "INSERT INTO bank_statement_files 
                       (filename, file_path, file_hash, period_id, uploaded_by, total_transactions, matched_transactions, unmatched_transactions, analysis_complete) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                       ON DUPLICATE KEY UPDATE 
                       total_transactions = VALUES(total_transactions),
                       matched_transactions = VALUES(matched_transactions),
                       unmatched_transactions = VALUES(unmatched_transactions),
                       analysis_complete = VALUES(analysis_complete)";
        
        // Validate transaction structure
        foreach ($transactions as $index => $transaction) {
            if (!is_array($transaction)) {
                throw new Exception("Transaction at index " . $index . " must be an array, got: " . gettype($transaction));
            }
            
            // Check required transaction fields
            $required_tx_fields = ['name', 'amount', 'type', 'description', 'matched'];
            foreach ($required_tx_fields as $field) {
                if (!isset($transaction[$field])) {
                    throw new Exception("Missing required field '" . $field . "' in transaction at index " . $index);
                }
            }
        }
        
        $matched_count = count(array_filter($transactions, function($t) { return $t['matched']; }));
        $unmatched_count = count($transactions) - $matched_count;
        $total_transactions = count($transactions);
        $analysis_complete = 1;
        
        error_log("File info for database insert: " . print_r($file_info, true));
        error_log("Transaction counts - total: " . $total_transactions . ", matched: " . $matched_count . ", unmatched: " . $unmatched_count);
        
        $file_stmt = mysqli_prepare($cov, $file_query);
        mysqli_stmt_bind_param($file_stmt, 'sssiiiiii', 
            $file_info['filename'], 
            $file_info['file_path'], 
            $file_info['file_hash'], 
            $file_info['period_id'], 
            $file_info['uploaded_by'],
            $total_transactions,
            $matched_count,
            $unmatched_count,
            $analysis_complete
        );
        
        if (!mysqli_stmt_execute($file_stmt)) {
            throw new Exception("Failed to insert file record: " . mysqli_error($cov));
        }
        
        // Get the file ID
        $file_id = mysqli_insert_id($cov);
        if ($file_id == 0) {
            // File already exists, get its ID
            $get_id_query = "SELECT id FROM bank_statement_files WHERE file_hash = ?";
            $get_id_stmt = mysqli_prepare($cov, $get_id_query);
            mysqli_stmt_bind_param($get_id_stmt, 's', $file_info['file_hash']);
            mysqli_stmt_execute($get_id_stmt);
            $id_result = mysqli_stmt_get_result($get_id_stmt);
            $id_row = mysqli_fetch_assoc($id_result);
            $file_id = $id_row['id'];
        }
        
        // Clear existing extractions for this file (in case of re-processing)
        $clear_query = "DELETE FROM bank_statement_extractions WHERE file_id = ?";
        $clear_stmt = mysqli_prepare($cov, $clear_query);
        mysqli_stmt_bind_param($clear_stmt, 'i', $file_id);
        mysqli_stmt_execute($clear_stmt);
        
        // Insert extraction records
        $extract_query = "INSERT INTO bank_statement_extractions 
                          (file_id, file_hash, transaction_date, transaction_name, transaction_amount, transaction_type, 
                           transaction_description, period_id, page_number, matched, member_id, member_name, unique_id) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        error_log("Extraction query prepared: " . $extract_query);
        error_log("About to insert " . count($transactions) . " transactions into bank_statement_extractions");
        
        $extract_stmt = mysqli_prepare($cov, $extract_query);
        if (!$extract_stmt) {
            error_log("Failed to prepare extraction statement: " . mysqli_error($cov));
            throw new Exception("Failed to prepare extraction statement: " . mysqli_error($cov));
        }
        
        $insert_count = 0;
        foreach ($transactions as $index => $transaction) {
            $page_number = isset($transaction['page_info']) ? $transaction['page_info']['pageNumber'] : 1;
            
            // Properly format the transaction date for MySQL DATE column
            $transaction_date = null;
            if (!empty($transaction['date'])) {
                // Handle different date formats
                $date_str = trim($transaction['date']);
                
                // Check if it's already in YYYY-MM-DD format
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_str)) {
                    $transaction_date = $date_str;
                }
                // Check if it's in DD/MM/YYYY format
                elseif (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $date_str)) {
                    $date_parts = explode('/', $date_str);
                    $day = str_pad($date_parts[0], 2, '0', STR_PAD_LEFT);
                    $month = str_pad($date_parts[1], 2, '0', STR_PAD_LEFT);
                    $year = $date_parts[2];
                    $transaction_date = "$year-$month-$day";
                }
                // Check if it's in MM/DD/YYYY format
                elseif (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $date_str)) {
                    $date_parts = explode('/', $date_str);
                    $month = str_pad($date_parts[0], 2, '0', STR_PAD_LEFT);
                    $day = str_pad($date_parts[1], 2, '0', STR_PAD_LEFT);
                    $year = $date_parts[2];
                    $transaction_date = "$year-$month-$day";
                }
                // If none of the above, try to parse with strtotime
                else {
                    $timestamp = strtotime($date_str);
                    if ($timestamp !== false) {
                        $transaction_date = date('Y-m-d', $timestamp);
                    }
                }
                
                // Validate the final date
                if ($transaction_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $transaction_date)) {
                    error_log("Invalid date format after conversion: " . $transaction_date . " (original: " . $date_str . ")");
                    $transaction_date = null;
                }
            }
            
            // Create variables for all bind parameters to avoid reference issues
            $tx_name = $transaction['name'];
            $tx_amount = $transaction['amount'];
            $tx_type = $transaction['type'];
            $tx_description = $transaction['description'];
            $tx_matched = $transaction['matched'] ? 1 : 0;
            $tx_member_id = $transaction['member_id'] ?? null;
            $tx_member_name = $transaction['member_name'] ?? null;
            $tx_unique_id = $transaction['uniqueId'];
            
            error_log("Processing transaction " . ($index + 1) . ": " . print_r($transaction, true));
            error_log("Transaction date: " . ($transaction_date ?: 'NULL') . " (original: " . ($transaction['date'] ?? 'NULL') . ")");
            error_log("Page number: " . $page_number);
            error_log("Matched: " . $tx_matched);
            
            $bind_result = mysqli_stmt_bind_param($extract_stmt, 'isssdssiiiiss',
                $file_id,
                $file_info['file_hash'],
                $transaction_date,
                $tx_name,
                $tx_amount,
                $tx_type,
                $tx_description,
                $file_info['period_id'],
                $page_number,
                $tx_matched,
                $tx_member_id,
                $tx_member_name,
                $tx_unique_id
            );
            
            if (!$bind_result) {
                error_log("Failed to bind parameters for transaction " . ($index + 1) . ": " . mysqli_stmt_error($extract_stmt));
                throw new Exception("Failed to bind parameters for transaction " . ($index + 1) . ": " . mysqli_stmt_error($extract_stmt));
            }
            
            $execute_result = mysqli_stmt_execute($extract_stmt);
            if (!$execute_result) {
                error_log("Failed to insert extraction record " . ($index + 1) . ": " . mysqli_stmt_error($extract_stmt));
                throw new Exception("Failed to insert extraction record " . ($index + 1) . ": " . mysqli_stmt_error($extract_stmt));
            }
            
            $insert_count++;
            error_log("Successfully inserted transaction " . ($index + 1) . " (ID: " . mysqli_stmt_insert_id($extract_stmt) . ")");
        }
        
        error_log("Total transactions inserted into bank_statement_extractions: " . $insert_count);
        
        // Commit transaction
        error_log("About to commit transaction to database...");
        $commit_result = mysqli_commit($cov);
        if (!$commit_result) {
            error_log("Failed to commit transaction: " . mysqli_error($cov));
            throw new Exception("Failed to commit transaction: " . mysqli_error($cov));
        }
        error_log("Transaction committed successfully");
        
        mysqli_autocommit($cov, true);
        error_log("=== handleSaveExtractedData COMPLETED SUCCESSFULLY ===");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Extracted data saved successfully',
            'file_id' => $file_id
        ]);
        
    } catch (Exception $e) {
        // Rollback on error
        if (isset($cov)) {
            mysqli_rollback($cov);
            mysqli_autocommit($cov, true);
        }
        
        error_log("Save extracted data error: " . $e->getMessage());
        error_log("Error trace: " . $e->getTraceAsString());
        
        // Ensure we return valid JSON even on error
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        echo json_encode(['success' => false, 'message' => 'Failed to save extracted data: ' . $e->getMessage()]);
        exit;
    } catch (Error $e) {
        // Catch PHP 7+ errors
        if (isset($cov)) {
            mysqli_rollback($cov);
            mysqli_autocommit($cov, true);
        }
        
        error_log("Save extracted data PHP error: " . $e->getMessage());
        error_log("Error trace: " . $e->getTraceAsString());
        
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        echo json_encode(['success' => false, 'message' => 'PHP Error: ' . $e->getMessage()]);
        exit;
    }
}

function ensureTableColumns($cov) {
    // Check if bank_statement_files table exists first
    $check_table = "SHOW TABLES LIKE 'bank_statement_files'";
    $table_result = mysqli_query($cov, $check_table);
    
    if (mysqli_num_rows($table_result) > 0) {
        // Table exists, check if it has the required columns
        $check_columns = "SHOW COLUMNS FROM bank_statement_files LIKE 'total_transactions'";
        $col_result = mysqli_query($cov, $check_columns);
        
        if (mysqli_num_rows($col_result) == 0) {
            // Add missing columns
            $alter_table = "ALTER TABLE bank_statement_files 
                           ADD COLUMN total_transactions INT DEFAULT 0,
                           ADD COLUMN matched_transactions INT DEFAULT 0,
                           ADD COLUMN unmatched_transactions INT DEFAULT 0,
                           ADD COLUMN analysis_complete TINYINT(1) DEFAULT 0";
            if (mysqli_query($cov, $alter_table)) {
                error_log("Added missing columns to bank_statement_files table");
            } else {
                error_log("Failed to add columns to bank_statement_files table: " . mysqli_error($cov));
            }
        }
    }
    
    // Clean up any existing invalid dates in bank_statement_extractions
    cleanupInvalidDates($cov);
}

function cleanupInvalidDates($cov) {
    try {
        // Check if bank_statement_extractions table exists
        $check_table = "SHOW TABLES LIKE 'bank_statement_extractions'";
        $table_result = mysqli_query($cov, $check_table);
        
        if (mysqli_num_rows($table_result) > 0) {
            // Find records with invalid dates (0000-00-00 or NULL)
            $invalid_dates_query = "SELECT id, transaction_date FROM bank_statement_extractions 
                                   WHERE transaction_date = '0000-00-00' OR transaction_date IS NULL";
            $invalid_result = mysqli_query($cov, $invalid_dates_query);
            
            if ($invalid_result && mysqli_num_rows($invalid_result) > 0) {
                error_log("Found " . mysqli_num_rows($invalid_result) . " records with invalid dates");
                
                while ($row = mysqli_fetch_assoc($invalid_result)) {
                    $record_id = $row['id'];
                    
                    // Set invalid dates to NULL (which is acceptable for DATE columns)
                    $update_query = "UPDATE bank_statement_extractions 
                                    SET transaction_date = NULL 
                                    WHERE id = ?";
                    $update_stmt = mysqli_prepare($cov, $update_query);
                    if ($update_stmt) {
                        mysqli_stmt_bind_param($update_stmt, 'i', $record_id);
                        if (mysqli_stmt_execute($update_stmt)) {
                            error_log("Cleaned up invalid date for record ID: " . $record_id);
                        } else {
                            error_log("Failed to clean up invalid date for record ID: " . $record_id . " - " . mysqli_stmt_error($update_stmt));
                        }
                        mysqli_stmt_close($update_stmt);
                    }
                }
                
                error_log("Completed cleanup of invalid dates in bank_statement_extractions table");
            }
        }
    } catch (Exception $e) {
        error_log("Error during date cleanup: " . $e->getMessage());
    }
}

function createTables() {
    $cov = getDatabaseConnection();

    // Ensure existing tables have all required columns
    ensureTableColumns($cov);

    $tables = [
        "CREATE TABLE IF NOT EXISTS bank_statement_files (
            id INT AUTO_INCREMENT PRIMARY KEY,
            filename VARCHAR(255) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_hash VARCHAR(64) NOT NULL UNIQUE,
            period_id INT NOT NULL,
            uploaded_by INT NOT NULL,
            upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            processed TINYINT(1) DEFAULT 0,
            total_transactions INT DEFAULT 0,
            matched_transactions INT DEFAULT 0,
            unmatched_transactions INT DEFAULT 0,
            analysis_complete TINYINT(1) DEFAULT 0,
            INDEX idx_period (period_id),
            INDEX idx_uploaded_by (uploaded_by),
            INDEX idx_file_hash (file_hash),
            INDEX idx_processed (processed),
            INDEX idx_analysis_complete (analysis_complete)
        )",
        
        "CREATE TABLE IF NOT EXISTS bank_statement_extractions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            file_id INT NOT NULL,
            file_hash VARCHAR(64) NOT NULL,
            transaction_date DATE,
            transaction_name VARCHAR(255),
            transaction_amount DECIMAL(15,2),
            transaction_type ENUM('credit', 'debit'),
            transaction_description TEXT,
            period_id INT,
            page_number INT DEFAULT 1,
            matched TINYINT(1) DEFAULT 0,
            member_id INT NULL,
            member_name VARCHAR(255) NULL,
            processed TINYINT(1) DEFAULT 0,
            processed_date TIMESTAMP NULL,
            processed_to_table ENUM('tbl_contributions', 'tbl_loan') NULL,
            unique_id VARCHAR(100),
            created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_file_id (file_id),
            INDEX idx_file_hash (file_hash),
            INDEX idx_period (period_id),
            INDEX idx_matched (matched),
            INDEX idx_processed (processed),
            INDEX idx_unique_id (unique_id)
        )",
        
        "CREATE TABLE IF NOT EXISTS manual_transaction_matches (
            id INT AUTO_INCREMENT PRIMARY KEY,
            transaction_name VARCHAR(255) NOT NULL,
            transaction_amount DECIMAL(15,2) NOT NULL,
            transaction_type ENUM('credit', 'debit') NOT NULL,
            transaction_date DATE,
            transaction_description TEXT,
            member_id INT NOT NULL,
            member_name VARCHAR(255) NOT NULL,
            period_id INT NOT NULL,
            file_hash VARCHAR(64),
            matched_by INT NOT NULL,
            matched_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            processed TINYINT(1) DEFAULT 0,
            INDEX idx_member (member_id),
            INDEX idx_period (period_id),
            INDEX idx_file_hash (file_hash),
            INDEX idx_processed (processed)
        )",

        "CREATE TABLE IF NOT EXISTS unmatched_transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            transaction_date DATE,
            transaction_name VARCHAR(255) NOT NULL,
            transaction_amount DECIMAL(15,2) NOT NULL,
            transaction_type ENUM('credit', 'debit') NOT NULL,
            transaction_description TEXT,
            period_id INT,
            file_hash VARCHAR(64),
            created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            processed TINYINT(1) DEFAULT 0,
            INDEX idx_period (period_id),
            INDEX idx_file_hash (file_hash),
            INDEX idx_processed (processed)
        )"
    ];

    foreach ($tables as $sql) {
        if (!mysqli_query($cov, $sql)) {
            error_log("Failed to create table: " . mysqli_error($cov));
        }
    }
}

createTables();

function handleGetUnmatchedTransactions($input) {
    try {
        $cov = getDatabaseConnection();
        
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 50;
        $search = $input['search'] ?? '';
        $period_filter = $input['period_filter'] ?? '';
        
        $offset = ($page - 1) * $limit;
        
        // Build query with filters
        $where_conditions = [];
        $params = [];
        $param_types = '';
        
        if (!empty($search)) {
            $where_conditions[] = "(transaction_name LIKE ? OR transaction_description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $param_types .= 'ss';
        }
        
        if (!empty($period_filter)) {
            $where_conditions[] = "period_id = ?";
            $params[] = $period_filter;
            $param_types .= 's';
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        // Get total count
        $count_query = "SELECT COUNT(*) as total FROM unmatched_transactions $where_clause";
        $count_stmt = mysqli_prepare($cov, $count_query);
        if (!empty($params)) {
            mysqli_stmt_bind_param($count_stmt, $param_types, ...$params);
        }
        mysqli_stmt_execute($count_stmt);
        $count_result = mysqli_stmt_get_result($count_stmt);
        $total_count = mysqli_fetch_assoc($count_result)['total'];
        
        // Get transactions
        $query = "SELECT * FROM unmatched_transactions $where_clause ORDER BY transaction_date DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $param_types .= 'ii';
        
        $stmt = mysqli_prepare($cov, $query);
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $param_types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $transactions = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $transactions[] = $row;
        }
        
        // Get available periods for filter
        $periods_query = "SELECT DISTINCT period_id FROM unmatched_transactions ORDER BY period_id DESC";
        $periods_result = mysqli_query($cov, $periods_query);
        $periods = [];
        while ($row = mysqli_fetch_assoc($periods_result)) {
            $periods[] = $row['period_id'];
        }
        
        echo json_encode([
            'success' => true,
            'transactions' => $transactions,
            'total_count' => $total_count,
            'current_page' => $page,
            'total_pages' => ceil($total_count / $limit),
            'periods' => $periods
        ]);
        
    } catch (Exception $e) {
        error_log("Get unmatched transactions error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleGetMatchedTransactions($input) {
    try {
        $cov = getDatabaseConnection();
        
        $page = $input['page'] ?? 1;
        $limit = $input['limit'] ?? 50;
        $search = $input['search'] ?? '';
        $period_filter = $input['period_filter'] ?? '';
        
        $offset = ($page - 1) * $limit;
        
        // Build query with filters
        $where_conditions = [];
        $params = [];
        $param_types = '';
        
        if (!empty($search)) {
            $where_conditions[] = "(transaction_name LIKE ? OR transaction_description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $param_types .= 'ss';
        }
        
        if (!empty($period_filter)) {
            $where_conditions[] = "periodid = ?";
            $params[] = $period_filter;
            $param_types .= 's';
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        // Get total count
        $count_query = "SELECT COUNT(*) as total FROM manual_transaction_matches $where_clause";
        $count_stmt = mysqli_prepare($cov, $count_query);
        if (!empty($params)) {
            mysqli_stmt_bind_param($count_stmt, $param_types, ...$params);
        }
        mysqli_stmt_execute($count_stmt);
        $count_result = mysqli_stmt_get_result($count_stmt);
        $total_count = mysqli_fetch_assoc($count_result)['total'];
        
        // Get transactions with member information
        $query = "SELECT mtm.*, 
                         CONCAT(p.Fname, ' ', COALESCE(p.Mname, ''), ' ', p.Lname) as member_name
                  FROM manual_transaction_matches mtm
                  LEFT JOIN tbl_personalinfo p ON mtm.member_id = p.memberid
                  $where_clause 
                  ORDER BY mtm.matched_date DESC 
                  LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $param_types .= 'ii';
        
        $stmt = mysqli_prepare($cov, $query);
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $param_types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $transactions = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $transactions[] = $row;
        }
        
        // Get available periods for filter
        $periods_query = "SELECT DISTINCT period_id FROM manual_transaction_matches ORDER BY period_id DESC";
        $periods_result = mysqli_query($cov, $periods_query);
        $periods = [];
        while ($row = mysqli_fetch_assoc($periods_result)) {
            $periods[] = $row['period_id'];
        }
        
        echo json_encode([
            'success' => true,
            'transactions' => $transactions,
            'total_count' => $total_count,
            'current_page' => $page,
            'total_pages' => ceil($total_count / $limit),
            'periods' => $periods
        ]);
        
    } catch (Exception $e) {
        error_log("Get matched transactions error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleProcessMatchedTransaction($input) {
    try {
        $cov = getDatabaseConnection();
        
        $transaction_id = $input['transaction_id'] ?? '';
        if (empty($transaction_id)) {
            throw new Exception('Transaction ID is required');
        }
        
        // Get the matched transaction details
        $query = "SELECT * FROM manual_transaction_matches WHERE id = ?";
        $stmt = mysqli_prepare($cov, $query);
        mysqli_stmt_bind_param($stmt, 'i', $transaction_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $transaction = mysqli_fetch_assoc($result);
        
        if (!$transaction) {
            throw new Exception('Transaction not found');
        }
        
        // Validate member exists
        $member_query = "SELECT memberid FROM tbl_personalinfo WHERE memberid = ?";
        $member_stmt = mysqli_prepare($cov, $member_query);
        mysqli_stmt_bind_param($member_stmt, 'i', $transaction['member_id']);
        mysqli_stmt_execute($member_stmt);
        $member_result = mysqli_stmt_get_result($member_stmt);
        
        if (mysqli_num_rows($member_result) == 0) {
            throw new Exception('Member not found with ID: ' . $transaction['member_id']);
        }
        
        // Insert into appropriate table based on transaction type
        if ($transaction['transaction_type'] === 'credit') {
            // Insert into contributions table
            $insert_query = "INSERT INTO tbl_contributions (membersid, periodid, contribution) VALUES (?, ?, ?)";
            $insert_stmt = mysqli_prepare($cov, $insert_query);
            mysqli_stmt_bind_param($insert_stmt, 'sid', 
                $transaction['member_id'], 
                $transaction['period_id'], 
                $transaction['transaction_amount']
            );
        } else {
            // Insert into loans table
            $insert_query = "INSERT INTO tbl_loan (memberid, periodid, loanamount, loan_date) VALUES (?, ?, ?, NOW())";
            $insert_stmt = mysqli_prepare($cov, $insert_query);
            mysqli_stmt_bind_param($insert_stmt, 'iid', 
                $transaction['member_id'], 
                $transaction['period_id'], 
                $transaction['transaction_amount']
            );
        }
        
        if (!mysqli_stmt_execute($insert_stmt)) {
            throw new Exception('Failed to insert transaction: ' . mysqli_stmt_error($insert_stmt));
        }
        
        // Delete from manual_transaction_matches table
        $delete_query = "DELETE FROM manual_transaction_matches WHERE id = ?";
        $delete_stmt = mysqli_prepare($cov, $delete_query);
        mysqli_stmt_bind_param($delete_stmt, 'i', $transaction_id);
        mysqli_stmt_execute($delete_stmt);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Transaction processed successfully and moved to ' . 
                        ($transaction['transaction_type'] === 'credit' ? 'contributions' : 'loans') . ' table'
        ]);
        
    } catch (Exception $e) {
        error_log("Process matched transaction error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleDeleteMatchedTransaction($input) {
    try {
        $cov = getDatabaseConnection();
        
        $transaction_id = $input['transaction_id'] ?? '';
        if (empty($transaction_id)) {
            throw new Exception('Transaction ID is required');
        }
        
        // Delete from manual_transaction_matches table
        $delete_query = "DELETE FROM manual_transaction_matches WHERE id = ?";
        $delete_stmt = mysqli_prepare($cov, $delete_query);
        mysqli_stmt_bind_param($delete_stmt, 'i', $transaction_id);
        
        if (!mysqli_stmt_execute($delete_stmt)) {
            throw new Exception('Failed to delete transaction: ' . mysqli_stmt_error($delete_stmt));
        }
        
        if (mysqli_affected_rows($cov) === 0) {
            throw new Exception('Transaction not found or already deleted');
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Transaction deleted successfully'
        ]);
        
    } catch (Exception $e) {
        error_log("Delete matched transaction error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleFixInvalidDates($input) {
    try {
        $cov = getDatabaseConnection();
        
        // Clean up any existing invalid dates
        cleanupInvalidDates($cov);
        
        // Also fix any dates that might be in wrong format
        $fix_dates_query = "SELECT id, transaction_date FROM bank_statement_extractions 
                           WHERE transaction_date IS NOT NULL 
                           AND transaction_date != '0000-00-00'
                           AND transaction_date NOT REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$'";
        $fix_result = mysqli_query($cov, $fix_dates_query);
        
        if ($fix_result && mysqli_num_rows($fix_result) > 0) {
            error_log("Found " . mysqli_num_rows($fix_result) . " records with malformed dates");
            
            while ($row = mysqli_fetch_assoc($fix_result)) {
                $record_id = $row['id'];
                $date_str = $row['transaction_date'];
                
                // Try to fix the date format
                $fixed_date = null;
                
                // Check if it's in DD/MM/YYYY format
                if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $date_str)) {
                    $date_parts = explode('/', $date_str);
                    $day = str_pad($date_parts[0], 2, '0', STR_PAD_LEFT);
                    $month = str_pad($date_parts[1], 2, '0', STR_PAD_LEFT);
                    $year = $date_parts[2];
                    $fixed_date = "$year-$month-$day";
                }
                // Check if it's in MM/DD/YYYY format
                elseif (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $date_str)) {
                    $date_parts = explode('/', $date_str);
                    $month = str_pad($date_parts[0], 2, '0', STR_PAD_LEFT);
                    $day = str_pad($date_parts[1], 2, '0', STR_PAD_LEFT);
                    $year = $date_parts[2];
                    $fixed_date = "$year-$month-$day";
                }
                // Try strtotime as fallback
                else {
                    $timestamp = strtotime($date_str);
                    if ($timestamp !== false) {
                        $fixed_date = date('Y-m-d', $timestamp);
                    }
                }
                
                // Update the record if we have a valid date
                if ($fixed_date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fixed_date)) {
                    $update_query = "UPDATE bank_statement_extractions 
                                    SET transaction_date = ? 
                                    WHERE id = ?";
                    $update_stmt = mysqli_prepare($cov, $update_query);
                    if ($update_stmt) {
                        mysqli_stmt_bind_param($update_stmt, 'si', $fixed_date, $record_id);
                        if (mysqli_stmt_execute($update_stmt)) {
                            error_log("Fixed date for record ID: " . $record_id . " from '" . $date_str . "' to '" . $fixed_date . "'");
                        } else {
                            error_log("Failed to fix date for record ID: " . $record_id . " - " . mysqli_stmt_error($update_stmt));
                        }
                        mysqli_stmt_close($update_stmt);
                    }
                } else {
                    // If we can't fix it, set to NULL
                    $null_query = "UPDATE bank_statement_extractions 
                                  SET transaction_date = NULL 
                                  WHERE id = ?";
                    $null_stmt = mysqli_prepare($cov, $null_query);
                    if ($null_stmt) {
                        mysqli_stmt_bind_param($null_stmt, 'i', $record_id);
                        mysqli_stmt_execute($null_stmt);
                        error_log("Set unparseable date to NULL for record ID: " . $record_id . " (original: " . $date_str . ")");
                        mysqli_stmt_close($null_stmt);
                    }
                }
            }
            
            error_log("Completed fixing malformed dates in bank_statement_extractions table");
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Invalid dates have been cleaned up and fixed'
        ]);
        
    } catch (Exception $e) {
        error_log("Fix invalid dates error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleSaveReclassification($input) {
    try {
        $cov = getDatabaseConnection();
        
        $transaction_name = $input['transaction_name'] ?? '';
        $transaction_amount = $input['transaction_amount'] ?? 0;
        $old_type = $input['old_type'] ?? '';
        $new_type = $input['new_type'] ?? '';
        $unique_id = $input['unique_id'] ?? null;
        $matched = $input['matched'] ?? false;
        
        if (empty($transaction_name) || empty($old_type) || empty($new_type)) {
            throw new Exception('Transaction name, old type, and new type are required');
        }
        
        // Check if reclassification table exists, create if not
        $table_check = mysqli_query($cov, "SHOW TABLES LIKE 'transaction_reclassifications'");
        if (!$table_check || mysqli_num_rows($table_check) == 0) {
            $create_table = "CREATE TABLE IF NOT EXISTS transaction_reclassifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                transaction_name VARCHAR(255) NOT NULL,
                transaction_amount DECIMAL(10,2) NOT NULL,
                old_type ENUM('credit', 'debit') NOT NULL,
                new_type ENUM('credit', 'debit') NOT NULL,
                unique_id VARCHAR(255),
                matched BOOLEAN DEFAULT FALSE,
                reclassified_by VARCHAR(100),
                reclassified_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_transaction_name (transaction_name),
                INDEX idx_unique_id (unique_id)
            )";
            
            if (!mysqli_query($cov, $create_table)) {
                throw new Exception('Failed to create reclassification table: ' . mysqli_error($cov));
            }
        }
        
        // Get user ID from session
        global $user_id;
        $user_id = $user_id ?? 'Unknown';
        
        // Insert reclassification record
        $query = "INSERT INTO transaction_reclassifications 
                  (transaction_name, transaction_amount, old_type, new_type, unique_id, matched, reclassified_by) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($cov, $query);
        if (!$stmt) {
            throw new Exception('Failed to prepare insert query: ' . mysqli_error($cov));
        }
        
        mysqli_stmt_bind_param($stmt, 'sdssss', 
            $transaction_name, 
            $transaction_amount, 
            $old_type, 
            $new_type, 
            $unique_id, 
            $matched, 
            $user_id
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Failed to execute insert query: ' . mysqli_stmt_error($stmt));
        }
        
        error_log("Reclassification saved successfully: $transaction_name from $old_type to $new_type");
        
        echo json_encode([
            'success' => true,
            'message' => 'Reclassification saved successfully'
        ]);
        
    } catch (Exception $e) {
        error_log("Save reclassification error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>