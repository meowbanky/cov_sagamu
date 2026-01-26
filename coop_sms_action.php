<?php
header('Content-Type: application/json');
require_once('Connections/cov.php');
// Ensure NotificationService is loaded. 
// Pointing to the merged libs/services/NotificationService.php
$notificationServicePath = __DIR__ . '/libs/services/NotificationService.php';
$autoloaderPath = __DIR__ . '/vendor/autoload.php';

// Load Composer Autoloader (for Dotenv)
if (file_exists($autoloaderPath)) {
    require_once($autoloaderPath);
    // Load .env
    if (class_exists('Dotenv\Dotenv') && file_exists(__DIR__ . '/.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->safeLoad();
    }
}

if (file_exists($notificationServicePath)) {
    require_once($notificationServicePath);
} else {
    // If not found, try absolute path or fallback
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/cov/cov_admin/libs/services/NotificationService.php')) {
        require_once($_SERVER['DOCUMENT_ROOT'] . '/cov/cov_admin/libs/services/NotificationService.php');
    } else {
        error_log("coop_sms_action: NotificationService.php not found at: " . $notificationServicePath);
    }
}

// Namespace for libs/services is App\Services
use App\Services\NotificationService;

$response = ['status' => 'error', 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'fetch_all_contacts') {
            // Fetch all active numbers
            // Using PDO $conn from Connections/cov.php
            $query = "SELECT MobilePhone FROM tbl_personalinfo WHERE Status = 'Active' AND MobilePhone IS NOT NULL AND MobilePhone != ''";
            $stmt = $conn->query($query);
            $numbers = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Clean numbers (basic trim)
            $cleanNumbers = array_map('trim', $numbers);
            $cleanNumbers = array_filter($cleanNumbers); // Remove empty

            $response['status'] = 'success';
            $response['data'] = array_values($cleanNumbers);
            $response['count'] = count($cleanNumbers);
        }

        elseif ($action === 'send_bulk_sms') {
            $recipientsStr = $_POST['recipients'] ?? '';
            $message = $_POST['message'] ?? '';

            if (empty($recipientsStr) || empty($message)) {
                throw new Exception("Recipients and Message are required.");
            }

            if (!class_exists(NotificationService::class)) {
                throw new Exception("Notification Service not available.");
            }

            // Parse numbers (comma separated)
            $recipients = explode(',', $recipientsStr);
            $recipients = array_map('trim', $recipients);
            $recipients = array_filter($recipients);

            $notificationService = new NotificationService($conn);

            // Chunk recipients into batches of 100 (Termii API limit)
            $chunks = array_chunk($recipients, 100);
            $totalSubmitted = 0;
            $batchResults = [];
            $hasErrors = false;

            foreach ($chunks as $index => $chunk) {
                try {
                    $result = $notificationService->sendBulkSMS($chunk, $message);
                    $totalSubmitted += count($chunk);
                    $batchResults[] = [
                        'batch' => $index + 1,
                        'status' => 'success',
                        'count' => count($chunk),
                        'response' => $result
                    ];
                } catch (Exception $e) {
                    $hasErrors = true;
                    $batchResults[] = [
                        'batch' => $index + 1,
                        'status' => 'error',
                        'count' => count($chunk),
                        'message' => $e->getMessage()
                    ];
                }
                
                // valid "nice" pause between batches
                if (count($chunks) > 1) usleep(200000); // 0.2s pause
            }

            $response['status'] = $hasErrors && $totalSubmitted == 0 ? 'error' : 'success';
            $response['message'] = "Processed " . count($recipients) . " recipients. Submitted: $totalSubmitted.";
            $response['data'] = [
                'total_processed' => count($recipients),
                'total_submitted' => $totalSubmitted,
                'batches' => $batchResults
            ];
        }

        elseif ($action === 'get_balance') {
            if (!class_exists(NotificationService::class)) {
                throw new Exception("Notification Service not available.");
            }
            $notificationService = new NotificationService($conn);
            $balance = $notificationService->getSMSBalance();
            
            $response['status'] = 'success';
            $response['data'] = ['balance' => $balance];
        }

        elseif ($action === 'get_history') {
            if (!class_exists(NotificationService::class)) {
                throw new Exception("Notification Service not available.");
            }
            $notificationService = new NotificationService($conn);
            $history = $notificationService->getSMSInbox();
            
            // Limit to recent 50 for performance
            $history = array_slice($history, 0, 50);

            // Enrich with Names
            // Collect phone numbers (last 10 digits for matching)
            $phoneMap = [];
            $phonesToLookup = [];
            foreach ($history as $idx => $item) {
                $raw = $item['receiver'];
                // Clean: remove non-digits, take last 10
                $clean = preg_replace('/[^0-9]/', '', $raw);
                if (strlen($clean) >= 10) {
                    $key = substr($clean, -10);
                    $phonesToLookup[$key] = true;
                    // Store reference to update later
                    $history[$idx]['lookup_key'] = $key;
                }
                $history[$idx]['member_name'] = $history[$idx]['receiver']; // Default to number
            }

            if (!empty($phonesToLookup)) {
                $keys = array_keys($phonesToLookup);
                // Create placeholders for IN clause
                $placeholders = implode(',', array_fill(0, count($keys), '?'));
                
                // Prepare statement
                // NOTE: RIGHT() function usage. 
                $sql = "SELECT Fname, Lname, MobilePhone FROM tbl_personalinfo WHERE RIGHT(MobilePhone, 10) IN ($placeholders)";
                $stmt = $conn->prepare($sql);
                $stmt->execute($keys);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Build Map: '8012345678' => 'John Doe'
                $dbMap = [];
                foreach ($results as $row) {
                    $cleanDB = preg_replace('/[^0-9]/', '', $row['MobilePhone']);
                    if (strlen($cleanDB) >= 10) {
                        $k = substr($cleanDB, -10);
                        $name = trim($row['Fname'] . ' ' . $row['Lname']);
                        $dbMap[$k] = $name;
                    }
                }

                // Apply map to history
                foreach ($history as $idx => $item) {
                    if (isset($item['lookup_key']) && isset($dbMap[$item['lookup_key']])) {
                        $history[$idx]['member_name'] = $dbMap[$item['lookup_key']];
                    }
                }
            }
            
            $response['status'] = 'success';
            $response['data'] = $history;
        }

        else {
            throw new Exception("Invalid Action");
        }

    } else {
        throw new Exception("Invalid Request Method");
    }

} catch (Exception $e) {
    http_response_code(400);
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
