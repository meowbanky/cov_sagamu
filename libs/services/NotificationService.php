<?php
namespace App\Services;

class NotificationService {
    private $db;
    private $oneSignalConfig;
    private $smsConfig;

    public function __construct($db) {
        $this->db = $db;
        $this->oneSignalConfig = [
            'appId' => $_ENV['ONESIGNAL_APP_ID'],
            'apiKey' => $_ENV['ONESIGNAL_API_KEY']
        ];
        $this->smsConfig = [
            'sender' => $_ENV['TERMII_SENDER'],
            'apiKey' => $_ENV['TERMII_API_KEY'],
            'endpoint' => 'https://v3.api.termii.com/api/sms/send'
        ];
    }

    public function calculateTransactionCost($message, $recipientCount) {
        // Cost per page = 5 Naira
        $costPerPage = 5.0;
        
        $len = strlen($message);
        $pages = 1;
        
        if ($len > 160) {
            // Standard multi-part calculation
            // 153 chars per segment for concatenated SMS
            $pages = ceil($len / 153);
        }
        
        return $pages * $recipientCount * $costPerPage;
    }

    public function sendTransactionNotification($memberId, $periodId) {
        try {
            // Get transaction details
            $transactionData = $this->getTransactionDetails($memberId, $periodId);
//            error_log('transatino data '.json_encode($transactionData));
            if (!$transactionData) {
                throw new \Exception("No transaction data found");
            }

            // Format message
            $message = $this->formatTransactionMessage($transactionData);

            // Send notifications
            $smsResult = $this->sendSMS($transactionData['MobilePhone'], $message);
//            error_log("SMS Response: " . json_encode($smsResult));
//            error_log("Mobile Number: " . json_encode($transactionData['MobilePhone']));

            if (!empty($transactionData['onesignal_id'])) {
                $this->sendPushNotification(
                    $transactionData['onesignal_id'],
                    "Transaction Update",
                    $message
                );
            }

            // Log notification
            $this->logNotification($memberId, $message);

            return true;
        } catch (\Exception $e) {
            error_log("Notification Error: " . $e->getMessage());
            return false;
        }
    }

    private function getTransactionDetails($memberId, $periodId) {
        $query = "SELECT tlb_mastertransaction.memberid,tbpayrollperiods.Periodid,
CONCAT(tbl_personalinfo.Lname, ' , ', tbl_personalinfo.Fname, ' ', IFNULL(tbl_personalinfo.Mname, '')) AS namess,
    tbl_personalinfo.MobilePhone,
    tbpayrollperiods.PayrollPeriod,
    SUM(tlb_mastertransaction.entryFee) as entryFee,
    SUM(tlb_mastertransaction.savings) as savingsAmount,
    SUM(tlb_mastertransaction.shares) as sharesAmount,
    SUM(tlb_mastertransaction.interestPaid) as InterestPaid,
    SUM(tlb_mastertransaction.interest) as interest,
    SUM(tlb_mastertransaction.loanAmount) as loan,
    SUM(tlb_mastertransaction.loanRepayment) as loanRepayment,
    (
        SELECT 
            SUM(m2.interest) - SUM(m2.interestPaid)
        FROM tlb_mastertransaction m2
        WHERE m2.memberid = tlb_mastertransaction.memberid
        AND m2.periodid <= tlb_mastertransaction.periodid
    ) as interestBalance,
    (
        SELECT 
            SUM(m2.loanAmount) - SUM(m2.loanRepayment)
        FROM tlb_mastertransaction m2
        WHERE m2.memberid = tlb_mastertransaction.memberid
        AND m2.periodid <= tlb_mastertransaction.periodid
    ) as loanBalance,
		(
        SELECT 
            SUM(m2.savings)
        FROM tlb_mastertransaction m2
        WHERE m2.memberid = tlb_mastertransaction.memberid
        AND m2.periodid <= tlb_mastertransaction.periodid
    ) as savingsBalance,
		(
        SELECT 
            SUM(m2.shares)
        FROM tlb_mastertransaction m2
        WHERE m2.memberid = tlb_mastertransaction.memberid
        AND m2.periodid <= tlb_mastertransaction.periodid
    ) as sharesBalance,
    SUM(tlb_mastertransaction.entryFee + 
        tlb_mastertransaction.savings + 
        tlb_mastertransaction.shares + 
        tlb_mastertransaction.interestPaid + 
        tlb_mastertransaction.loanRepayment + 
        tlb_mastertransaction.repayment_bank ) as total
FROM tlb_mastertransaction INNER JOIN tbl_personalinfo on tlb_mastertransaction.memberid = tbl_personalinfo.memberid
LEFT JOIN tbpayrollperiods ON tlb_mastertransaction.periodid = tbpayrollperiods.Periodid 
        WHERE tbl_personalinfo.memberid = '" . mysqli_real_escape_string($this->db, $memberId) . "' 
        AND tlb_mastertransaction.periodid = " . (int)$periodId . "
        GROUP BY tbpayrollperiods.Periodid ORDER BY tbpayrollperiods.Periodid DESC LIMIT 1";

        $result = mysqli_query($this->db, $query);
        if (!$result) {
            throw new \Exception("Database query failed: " . mysqli_error($this->db));
        }

        return mysqli_fetch_assoc($result);
    }

    private function formatTransactionMessage($data) {
        return sprintf(
            "COOP ACCT. BAL., MONTHLY CONTR.: %s\n" .
            "SAVINGS: %s\n" .
            "SAVINGS BALANCE: %s\n" .
            "SHARES: %s\n" .
            "SHARES BALANCE: %s\n" .
            "INT PAID: %s\n" .
            "UNPAID INT: %s\n" .
            "LOAN: %s\n" .
            "LOAN REPAY: %s\n" .
            "LOAN BAL: %s\n" .
            "AS AT: %s ENDING\n" .
            "Download our mobile app here: %s",
            number_format(floatval($data['total']), 2, '.', ','),
            number_format(floatval($data['savingsAmount']), 2, '.', ','),
            number_format(floatval($data['savingsBalance']), 2, '.', ','),
            number_format(floatval($data['sharesAmount']), 2, '.', ','),
            number_format(floatval($data['sharesBalance']), 2, '.', ','),
            number_format(floatval($data['InterestPaid']), 2, '.', ','),
            number_format(floatval($data['interestBalance']), 2, '.', ','),
            number_format(floatval($data['loan']), 2, '.', ','),
            number_format(floatval($data['loanRepayment']), 2, '.', ','),
            number_format(floatval($data['loanBalance']), 2, '.', ','),
            $data['PayrollPeriod'],
            "https://emmaggi.com/cov/download.html"
        );
    }

    private function sendSMS($phone, $message) {
        if (empty($phone)) {
            throw new \Exception("Phone number is required");
        }

        $phone = $this->formatPhoneNumber($phone);

        $data = [
            "api_key" => $this->smsConfig['apiKey'],
            "to" => $phone,  // Single phone number, not array
            "from" => $this->smsConfig['sender'],
            "sms" => $message,
            "type" => "plain",
            "channel" => "generic"
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->smsConfig['endpoint'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json"
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception("Curl error: $error");
        }

        curl_close($ch);

        $responseData = json_decode($response, true);
//        error_log("Full SMS API Response: " . $response);

        if ($httpCode !== 200 && $httpCode !== 201) {
            $errorMessage = isset($responseData['message']) ? $responseData['message'] : $response;
            throw new \Exception("SMS API Error ($httpCode): $errorMessage");
        }

        return $responseData;
    }

    private function sendPushNotification($playerId, $title, $message) {
        if (empty($playerId)) {
            return false; // Skip if no player ID
        }

        $fields = [
            'app_id' => $this->oneSignalConfig['appId'],
            'include_player_ids' => [$playerId],
            'headings' => ['en' => $title],
            'contents' => ['en' => $message],
            'priority' => 10
        ];

        $ch = curl_init('https://onesignal.com/api/v1/notifications');
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Basic ' . $this->oneSignalConfig['apiKey']
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($fields),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception("OneSignal API Error: $response");
        }

        return json_decode($response, true);
    }

    private function formatPhoneNumber($phone) {
        $phone = trim($phone);
        if (substr($phone, 0, 1) === '0') {
            return '234' . substr($phone, 1);
        } elseif (substr($phone, 0, 1) === '+') {
            return substr($phone, 1);
        }
        return $phone;
    }

    private function logNotification($memberId, $message) {
        $memberId = mysqli_real_escape_string($this->db, $memberId);
        $message = mysqli_real_escape_string($this->db, $message);

        $query = "INSERT INTO notifications 
                  (memberid, message, created_at, status) 
                  VALUES 
                  ('$memberId', '$message', NOW(), 'unread')";

        return mysqli_query($this->db, $query);
    }

    public function sendBulkSMS(array $phoneNumbers, $message, $channel = 'generic') {
        if (empty($phoneNumbers)) {
            throw new \Exception("Phone numbers are required");
        }

        // Re-index array to be safe JSON
        // Using local formatPhoneNumber
        $formattedNumbers = array_values(array_map([$this, 'formatPhoneNumber'], $phoneNumbers));

        $data = [
            "api_key" => $this->smsConfig['apiKey'],
            "to" => $formattedNumbers,
            "from" => $this->smsConfig['sender'],
            "sms" => $message,
            "type" => "plain",
            "channel" => $channel
        ];

        // HARDCODED BULK URL to ensure correctness
        $url = "https://v3.api.termii.com/api/sms/send/bulk";

        return $this->executeCurlRequest($url, $data);
    }

    public function getSMSBalance() {
        // Use the configured API key
        $apiKey = $this->smsConfig['apiKey'];
        
        if (empty($apiKey)) {
            error_log("Termii Balance Error: API Key is empty.");
            return 0;
        }

        $url = "https://v3.api.termii.com/api/get-balance?api_key=" . urlencode(trim($apiKey));
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false, 
            CURLOPT_SSL_VERIFYHOST => 0,     
            CURLOPT_TIMEOUT => 30 
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
             error_log("Termii Balance Curl Exec Failed: " . curl_error($ch));
             curl_close($ch);
             return 0;
        }

        curl_close($ch);
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Termii Balance JSON Decode Error: " . json_last_error_msg());
            return 0;
        }

        if ($httpCode !== 200) {
             error_log("Termii Balance Failed ($httpCode): " . $response);
             return 0;
        }
        
        if (isset($data['balance'])) {
             return $data['balance'];
        } else {
             error_log("Termii Balance: 'balance' key missing in response.");
             return 0;
        }
    }

    public function getSMSInbox() {
        // Use the configured API key
        $apiKey = $this->smsConfig['apiKey'];
        
        if (empty($apiKey)) {
            error_log("Termii Inbox Error: API Key is empty.");
            return [];
        }

        $url = "https://v3.api.termii.com/api/sms/inbox?api_key=" . urlencode(trim($apiKey));


        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_TIMEOUT => 60 
        ]);

        $response = curl_exec($ch);
    
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
             error_log("Termii Inbox Curl Exec Failed: " . curl_error($ch));
             curl_close($ch);
             return [];
        }

        curl_close($ch);
        
        $data = json_decode($response, true);

        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Termii Inbox JSON Decode Error: " . json_last_error_msg());
            return [];
        }
        
        if (is_array($data)) {
            return $data; 
        }

        return [];
    }

    private function executeCurlRequest($url, $data) {
        $ch = curl_init();
        
        // Debug Log Payload
        error_log("Termii Request Payload: " . json_encode($data));

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30, // Increased timeout for network latency
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json"
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception("Curl error: $error");
        }

        curl_close($ch);

        $responseData = json_decode($response, true);

        // Termii can return various codes, strictly check for success indicators
        if ($httpCode !== 200 && $httpCode !== 201) {
             // Debug log
             error_log("Termii API Error: URL: $url - Code: $httpCode - Response: $response");
             $errorMessage = isset($responseData['message']) ? $responseData['message'] : $response;
             // Include URL in error message for better debugging
             throw new \Exception("SMS API Error ($httpCode): $errorMessage (URL: $url)");
        }

        // Return formatted numbers for debugging
        $responseData['debug_numbers'] = $data['to'] ?? []; 

        return $responseData;
    }
}