<?php
/**
 * Fix Email Queue Scheduled Times
 * This script updates pending emails with future scheduled_at to current time
 * so they can be processed immediately
 */

require_once('Connections/cov.php');
mysqli_select_db($cov, $database_cov);

echo "<h2>Fixing Email Queue Scheduled Times</h2>";
echo "<pre>";

// Check current pending emails
$checkSql = "SELECT id, member_id, recipient_email, scheduled_at, created_at, NOW() as current_time
            FROM tbl_email_queue 
            WHERE status = 'pending'
            ORDER BY id";

$result = mysqli_query($cov, $checkSql);
$pendingEmails = [];

echo "Current Pending Emails:\n";
echo str_repeat("-", 80) . "\n";
printf("%-5s | %-10s | %-30s | %-19s | %-19s\n", "ID", "Member", "Email", "Scheduled At", "Created At");
echo str_repeat("-", 80) . "\n";

while ($row = mysqli_fetch_assoc($result)) {
    $pendingEmails[] = $row;
    printf("%-5s | %-10s | %-30s | %-19s | %-19s\n", 
        $row['id'], 
        $row['member_id'], 
        substr($row['recipient_email'], 0, 30),
        $row['scheduled_at'],
        $row['created_at']
    );
}

echo "\n";
echo "Current Server Time: " . date('Y-m-d H:i:s') . "\n";
echo "Database NOW(): " . ($pendingEmails[0]['current_time'] ?? 'N/A') . "\n";
echo "\n";

// Update pending emails to be scheduled for immediate processing
$updateSql = "UPDATE tbl_email_queue 
              SET scheduled_at = NOW() 
              WHERE status = 'pending' 
              AND scheduled_at > NOW()";

$updateResult = mysqli_query($cov, $updateSql);

if ($updateResult) {
    $affectedRows = mysqli_affected_rows($cov);
    echo "✅ SUCCESS: Updated $affectedRows email(s) to be scheduled immediately\n\n";
    
    // Show updated emails
    if ($affectedRows > 0) {
        $verifySQL = "SELECT id, member_id, recipient_email, scheduled_at, created_at
                     FROM tbl_email_queue 
                     WHERE status = 'pending'
                     ORDER BY id";
        
        $verifyResult = mysqli_query($cov, $verifySQL);
        
        echo "Updated Pending Emails:\n";
        echo str_repeat("-", 80) . "\n";
        printf("%-5s | %-10s | %-30s | %-19s | %-19s\n", "ID", "Member", "Email", "Scheduled At", "Created At");
        echo str_repeat("-", 80) . "\n";
        
        while ($row = mysqli_fetch_assoc($verifyResult)) {
            printf("%-5s | %-10s | %-30s | %-19s | %-19s\n", 
                $row['id'], 
                $row['member_id'], 
                substr($row['recipient_email'], 0, 30),
                $row['scheduled_at'],
                $row['created_at']
            );
        }
    }
} else {
    echo "❌ ERROR: " . mysqli_error($cov) . "\n";
}

echo "\n";
echo "Done! Pending emails are now ready for processing.\n";
echo "Run the cron job or visit email_queue_dashboard.php to process them.\n";
echo "</pre>";
?>

