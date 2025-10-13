<?php

// Prevent direct web access
if (isset($_SERVER['HTTP_HOST'])) {
    die('This script can only be run from command line');
}

// Set time limit and memory limit for long-running processes
set_time_limit(300); // 5 minutes
ini_set('memory_limit', '256M');

// Get the absolute path to the project root
$projectRoot = dirname(__DIR__);

// Include required files using absolute paths
require_once($projectRoot . '/Connections/cov.php');
require_once($projectRoot . '/libs/services/EmailQueueManager.php');

// Logging function
function logMessage($message) {
    global $projectRoot;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
    
    // Log to file using absolute path
    $logFile = $projectRoot . '/logs/email_queue.log';
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    
    // Also output to console if running from CLI
    if (php_sapi_name() === 'cli') {
        echo $logMessage;
    }
}

try {
    logMessage("Starting email queue processing...");
    
    // Initialize database connection
    $queueManager = new EmailQueueManager($cov, $database_cov);
    
    // Get queue statistics before processing
    $statsBefore = $queueManager->getQueueStats();
    $pendingBefore = $statsBefore['pending']['count'] ?? 0;
    
    logMessage("Pending emails before processing: {$pendingBefore}");
    
    // Process the queue
    $result = $queueManager->processQueue();
    
    // Log results
    if (isset($result['skipped'])) {
        logMessage("Queue processing skipped: {$result['skipped']}");
    } else {
        logMessage("Queue processing completed - Processed: {$result['processed']}, Failed: {$result['failed']}");
    }
    
    // Clean up old emails (run once per day)
    $currentHour = date('H');
    if ($currentHour === '02') { // Run at 2 AM
        logMessage("Running cleanup of old emails...");
        $cleaned = $queueManager->cleanupOldEmails(30);
        logMessage("Cleaned up {$cleaned} old email records");
    }
    
    // Get final statistics
    $statsAfter = $queueManager->getQueueStats();
    $pendingAfter = $statsAfter['pending']['count'] ?? 0;
    
    logMessage("Pending emails after processing: {$pendingAfter}");
    logMessage("Email queue processing completed successfully");
    
} catch (Exception $e) {
    logMessage("ERROR: " . $e->getMessage());
    logMessage("Stack trace: " . $e->getTraceAsString());
    exit(1);
}

exit(0);
?>