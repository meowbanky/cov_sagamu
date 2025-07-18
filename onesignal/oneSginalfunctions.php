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

const APP_KEY_TOKEN = 'YTQzNWNkYmUtOGU3YS00NzFjLTk4YzgtMzhhMmM5YWQ4MWI1';
const APP_KEY_TOKEN = 'os_v2_app_f3am3klwindrzgz76ydxndjehxdz7sg5w2ouuoe5jncrpi7duvrllmir3njseflpgp3xwgkpigugfma5wavgygbyk2vlyw2r4wahr5y';
//const USER_KEY_TOKEN = '<YOUR_USER_KEY_TOKEN>';

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


