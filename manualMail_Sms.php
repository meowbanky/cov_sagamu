<?php global $cov;
require_once('Connections/cov.php');
//require_once('sendmail.php');
//require_once('sendsms.php');

require_once('Connections/cov.php');
require_once __DIR__ . '/libs/services/NotificationService.php';

use App\Services\NotificationService;

// Initialize notification service
try {
    $notificationService = new NotificationService($cov);
} catch (Exception $e) {
    error_log("Failed to initialize notification service: " . $e->getMessage());
}

if (isset($_GET['equality'])){
$equality = $_GET['equality'];} 
else {$equality = '>=';}

if (isset($_GET['period'])){
$period  = $_GET['period'];} 
else {$period  = '-1';}

if (isset($_GET['staffid'])){$staffid = $_GET['staffid'];}else {$staffid = '1';}
mysqli_select_db($cov, $database_cov);
$query_member = "SELECT * FROM tbl_personalinfo WHERE tbl_personalinfo.memberid " . $equality ." ". $staffid . " AND Status = 'Active'";
$member = mysqli_query($cov,$query_member) or die(mysqli_error($cov));
$row_member = mysqli_fetch_assoc($member);
$totalRows_member = mysqli_num_rows($member);

do{

echo $row_member['memberid'].'<Br>';


        try {
            $notificationService = new NotificationService($cov);
            $notificationService->sendTransactionNotification(
                $row_member['memberid'],
                $period
            );
        } catch (Exception $e) {
            error_log("Failed to send notification: " . $e->getMessage());
        }


} while ($row_member = mysqli_fetch_assoc($member)); 

ob_end_flush();
    flush();
?>