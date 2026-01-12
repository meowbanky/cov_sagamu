<?php

$file =  __DIR__ . '/../vendor/autoload.php';
//require_once '/Users/mac/Desktop/Project/64_folder/cov/vendor/autoload.php';
require_once $file;
require_once('Connections/cov.php');

//use DateTime;
use onesignal\client\api\DefaultApi;
use onesignal\client\Configuration;
use onesignal\client\model\GetNotificationRequestBody;
use onesignal\client\model\Notification;
use onesignal\client\model\StringMap;
use onesignal\client\model\Player;
use onesignal\client\model\UpdatePlayerTagsRequestBody;
use onesignal\client\model\ExportPlayersRequestBody;
use onesignal\client\model\Segment;
use onesignal\client\model\FilterExpressions;
use PHPUnit\Framework\TestCase;
//use GuzzleHttp;

const APP_ID = '2ec0cda9-7643-471c-9b3f-f607768d243d';

// Load env if not already loaded available
if (!getenv('ONESIGNAL_APP_KEY_TOKEN')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad();
}

define('APP_KEY_TOKEN', getenv('ONESIGNAL_APP_KEY_TOKEN'));

$config = Configuration::getDefaultConfiguration()
    ->setAppKeyToken(APP_KEY_TOKEN);
//  ->setUserKeyToken(USER_KEY_TOKEN);

$apiInstance = new DefaultApi(
    new GuzzleHttp\Client(),
    $config
);


function createNotificationGeneral($enContent): Notification
{
    global $cov;
    global $database_cov;

    mysqli_select_db($cov, $database_cov);
    $sql = "INSERT INTO tbl_notification (subject,message,coop_id,date) VALUES ('Notification','{$enContent}',-1,now())";
    $query = mysqli_query($cov, $sql);


    $content = new StringMap();
    $content->setEn($enContent);

    $notification = new Notification();
    $notification->setAppId(APP_ID);

    $notification->setContents($content);
    $notification->setIncludedSegments(['Subscribed Users']);
    //$notification->setIncludePlayerIds(['e30bb2e6-2429-4eed-99c7-cc2ca1c7a4ad']);

    return $notification;
}

function createNotificationPlayer($enContent, $player, $coopid): Notification
{

    global $cov;
    global $database_cov;

    mysqli_select_db($cov, $database_cov);
    $sql = "INSERT INTO tbl_notification (subject,message,coop_id,date) VALUES ('Notification','{$enContent}','{$coopid}',now())";
    $query = mysqli_query($cov, $sql);


    $content = new StringMap();
    $content->setEn($enContent);

    $notification = new Notification();
    $notification->setAppId(APP_ID);

    $notification->setContents($content);

    //$notification->setIncludePlayerIds(['e30bb2e6-2429-4eed-99c7-cc2ca1c7a4ad']);
    $notification->setIncludePlayerIds([$player]);
    return $notification;
}


