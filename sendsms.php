<?php require_once('Connections/cov.php'); 
require_once('onesignal/oneSginalfunctions.php');

function useJSON($url, $username, $apikey, $flash, $sendername, $messagetext, $recipients) {
    $gsm = array();
    $country_code = '234';
    $arr_recipient = explode(',', $recipients);
    foreach ($arr_recipient as $recipient) {
        $mobilenumber = trim($recipient);
        if (substr($mobilenumber, 0, 1) == '0'){
            $mobilenumber = $country_code . substr($mobilenumber, 1);
        }
        elseif (substr($mobilenumber, 0, 1) == '+'){
            $mobilenumber = substr($mobilenumber, 1);
        }
        $generated_id = uniqid('int_', false);
        $generated_id = substr($generated_id, 0, 30);
        $gsm['gsm'][] = array('msidn' => $mobilenumber, 'msgid' => $generated_id);
    }
    $message = array(
        'sender' => $sendername,
        'messagetext' => $messagetext,
        'flash' => "{$flash}",
    );

    $request = array('SMS' => array(
            'auth' => array(
                'username' => $username,
                'apikey' => $apikey
            ),
            'message' => $message,
            'recipients' => $gsm
    ));
    $json_data = json_encode($request);
    if ($json_data) {
        $response = doPostRequest($url, $json_data, array('Content-Type: application/json'));
        $result = json_decode($response);
        return $result->response->status;
    } else {
        return false;
    }
}

function useXML($url, $username, $apikey, $flash, $sendername, $messagetext, $recipients) {
    $country_code = '234';
    $arr_recipient = explode(',', $recipients);
    $count = count($arr_recipient);
    $msg_ids = array();
    $recipients = '';

    $xml = new SimpleXMLElement('<SMS></SMS>');
    $auth = $xml->addChild('auth');
    $auth->addChild('username', $username);
    $auth->addChild('apikey', $apikey);

    $msg = $xml->addChild('message');
    $msg->addChild('sender', $sendername);
    $msg->addChild('messagetext', $messagetext);
    $msg->addChild('flash', $flash);

    $rcpt = $xml->addChild('recipients');
    for ($i = 0; $i < $count; $i++) {
        $generated_id = uniqid('int_', false);
        $generated_id = substr($generated_id, 0, 30);
        $mobilenumber = trim($arr_recipient[$i]);
        if (substr($mobilenumber, 0, 1) == '0') {
            $mobilenumber = $country_code . substr($mobilenumber, 1);
        } elseif (substr($mobilenumber, 0, 1) == '+') {
            $mobilenumber = substr($mobilenumber, 1);
        }
        $gsm = $rcpt->addChild('gsm');
        $gsm->addchild('msidn', $mobilenumber);
        $gsm->addchild('msgid', $generated_id);
    }
    $xmlrequest = $xml->asXML();

    if ($xmlrequest) {
        $result = doPostRequest($url, $xmlrequest, array('Content-Type: application/xml'));
        $xmlresponse = new SimpleXMLElement($result);
        return $xmlresponse->status;
    }
    return false;
}

//Function to connect to SMS sending server using HTTP POST
function doPostRequest($url, $arr_params, $headers = array('Content-Type: application/x-www-form-urlencoded')) {
    $response = array();
    $final_url_data = $arr_params;
    if (is_array($arr_params)) {
        $final_url_data = http_build_query($arr_params, '', '&');
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $final_url_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response['body'] = curl_exec($ch);
    $response['code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $response['body'];
}



// -------------------------------------------------------------------- \\
//termi API
function doSendMessage($to,$message) {
$curl = curl_init();
 $country_code = '234';
$mobilenumber = trim($to);
        if (substr($mobilenumber, 0, 1) == '0') {
            $mobilenumber = $country_code . substr($mobilenumber, 1);
        } elseif (substr($mobilenumber, 0, 1) == '+') {
            $mobilenumber = substr($mobilenumber, 1);
        }
        
$data = array("to" => [$mobilenumber], "from" => "VCMSSAGAMU", 
"sms" => $message, "type" => "plain", "channel" => "generic", "api_key" => "TLYa2oT5vTpT3X4r3fSv2lSfErDApbmhbOAjOP3ituAA2XnLYMFIqzrq3leU1y" );

$post_data = json_encode($data);

curl_setopt_array($curl, array(
CURLOPT_URL => 'https://api.ng.termii.com/api/sms/send/bulk',
CURLOPT_RETURNTRANSFER => true,
CURLOPT_ENCODING => '',
CURLOPT_MAXREDIRS => 10,
CURLOPT_TIMEOUT => 0,
CURLOPT_FOLLOWLOCATION => true,
CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
CURLOPT_CUSTOMREQUEST => 'POST',
CURLOPT_POSTFIELDS =>$post_data,
CURLOPT_HTTPHEADER => array(
  'Content-Type: application/json'
),
));

$response = curl_exec($curl);

curl_close($curl);
return $response;
}



function sendsms($staffid, $period){

$hostname_cov = "localhost";
$database_cov = "emmaggic_cofv";
$username_cov = "emmaggic_root";
$password_cov = "Oluwaseyi";
$cov = mysqli_connect($hostname_cov, $username_cov, $password_cov) or trigger_error(mysqli_error($cov),E_USER_ERROR); 


try {
            $conn = new PDO("mysql:host=$hostname_cov;dbname=$database_cov", $username_cov, $password_cov, array(PDO::ATTR_PERSISTENT=>true));
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    catch(PDOException $e)
        {
            echo "Failed Connection: " . $e->getMessage();
        }


 mysqli_select_db($cov,$database_cov);

            $sql_oneSignal = "SELECT * from oneSignal WHERE coop_id = '" . $staffid . "'";
            $result_oneSignal = mysqli_query($cov, $sql_oneSignal) or die(mysqli_error($cov));
            $row_oneSignal = mysqli_fetch_array($result_oneSignal);
            $totalRows_oneSignal = mysqli_num_rows($result_oneSignal);

mysqli_select_db($cov,$database_cov);
$query_masterTransaction = "SELECT  tlb_mastertransaction.periodid as Period FROM tlb_mastertransaction where periodid = ".$period;
$masterTransaction = mysqli_query($cov,$query_masterTransaction) or die(mysqli_error($cov));
$row_masterTransaction = mysqli_fetch_assoc($masterTransaction);
$totalRows_masterTransaction = mysqli_num_rows($masterTransaction);

mysqli_select_db($cov,$database_cov);
$query_MaxPeriod = "SELECT tbpayrollperiods.PayrollPeriod FROM tbpayrollperiods where periodid = " . $row_masterTransaction['Period'] ;
$MaxPeriod = mysqli_query($cov,$query_MaxPeriod) or die(mysqli_error($cov));
$row_MaxPeriod = mysqli_fetch_assoc($MaxPeriod);
$totalRows_MaxPeriod = mysqli_num_rows($MaxPeriod);

mysqli_select_db($cov,$database_cov);
$query_coopid2 = "SELECT
tbl_personalinfo.memberid,MobilePhone,
ANY_VALUE(tlb_mastertransaction.transactionid) AS transactionid,
concat(tbl_personalinfo.Lname,' , ', tbl_personalinfo.Fname,' ', ifnull( tbl_personalinfo.Mname,'')) AS namess,
ifnull((Sum(tlb_mastertransaction.loanAmount)),0) AS loan,(ifnull(Sum(tlb_mastertransaction.loanAmount),0)-ifnull(Sum(tlb_mastertransaction.loanRepayment),0)) as loanBalance,
ifnull(Sum(tlb_mastertransaction.loanRepayment),0) AS loanrepayments,
ifnull(Sum(tlb_mastertransaction.withdrawal),0) AS withrawals,
((ifnull(Sum(tlb_mastertransaction.loanRepayment),0)+ifnull(sum(tlb_mastertransaction.entryFee),0)+ifnull(sum(tlb_mastertransaction.savings),0)+
ifnull(sum(tlb_mastertransaction.shares),0)+ifnull(sum(tlb_mastertransaction.interestPaid),0))) AS total,
tbpayrollperiods.PayrollPeriod,
tlb_mastertransaction.periodid,
ifnull(sum(tlb_mastertransaction.entryFee),0) as entryFee,
ifnull(sum(tlb_mastertransaction.savings),0) as savings,
ifnull(sum(tlb_mastertransaction.shares),0) as shares,
ifnull(sum(tlb_mastertransaction.interestPaid),0) as interestPaid,ifnull(sum(tlb_mastertransaction.interest),0) as interest, (ifnull(sum(tlb_mastertransaction.interest),0) - ifnull(sum(tlb_mastertransaction.interestPaid),0)) as unpaidInterest
FROM
tbl_personalinfo
INNER JOIN tlb_mastertransaction ON tbl_personalinfo.memberid = tlb_mastertransaction.memberid
INNER JOIN tbpayrollperiods ON tbpayrollperiods.Periodid = tlb_mastertransaction.periodid
LEFT JOIN tbl_refund ON tbl_refund.membersid = tbl_personalinfo.memberid AND tbl_refund.periodid = tbpayrollperiods.Periodid
WHERE tbl_personalinfo.memberid = ". $staffid . " AND Status = 'Active' AND tlb_mastertransaction.Periodid <= '". $period."' GROUP BY tbl_personalinfo.memberid ORDER BY memberid ASC";
$coopid2 = mysqli_query($cov,$query_coopid2) or die(mysqli_error($cov));
$row_coopid2 = mysqli_fetch_assoc($coopid2);
$totalRows_coopid2 = mysqli_num_rows($coopid2);


$query_payperiod = "SELECT tbpayrollperiods.Periodid, tbpayrollperiods.PayrollPeriod FROM tbpayrollperiods WHERE Periodid = ".$period;
$payperiod = mysqli_query($cov,$query_payperiod) or die(mysqli_error($cov));
$row_payperiod = mysqli_fetch_assoc($payperiod);
$totalRows_payperiod = mysqli_num_rows($payperiod);


  
$json_url = "http://api.ebulksms.com:80/sendsms.json";
$xml_url = "http://api.ebulksms.com:80/sendsms.xml";
$username = '';
$apikey = '';

if (!$sock = @fsockopen('www.google.com', 80, $num, $error, 5))
{ echo "<script>alert('THERE IS NO INTERNET CONNECTION NOW!!!')</script>";
echo "<script>navigate('smsalert.php')</script>";
exit();
}else{

//if (($totalRows_coopid2 > 0) and  ($balance > $totalRows_coopid2)) {
$i=1;
do { 

set_time_limit(0);

$total = $totalRows_coopid2;   

// Calculate the percentation
    $percent = intval($i/$total * 100)."%";
	
	
mysqli_select_db($cov,$database_cov);
$query_getLoan = "SELECT ifnull((tlb_mastertransaction.loanAmount),0.00) as newLoan, interest, (sum(tlb_mastertransaction.shares) + sum(tlb_mastertransaction.loanRepayment)+sum(tlb_mastertransaction.savings)+sum(tlb_mastertransaction.interestPaid)) as totalcontribution  FROM tlb_mastertransaction WHERE memberid = '".$row_coopid2['memberid']."' and periodid = '".$period."'";
$getLoan = mysqli_query($cov,$query_getLoan) or die(mysqli_error($cov));
$row_getLoan = mysqli_fetch_assoc($getLoan);
$totalRows_getLoan = mysqli_num_rows($getLoan);

mysqli_select_db($cov,$database_cov);
$query_title = "SELECT tbl_globa_settings.`value` FROM tbl_globa_settings WHERE tbl_globa_settings.setting_id = 1";
$title = mysqli_query($cov,$query_title) or die(mysqli_error($cov));
$row_title = mysqli_fetch_assoc($title);
$totalRows_title = mysqli_num_rows($title);

	$username = "cov@emmaggi.com"; //$_POST['username'];
    $apikey = "9e6ce612af1fa2dc982e668176e806435830e5ff";//$_POST['apikey'];
	 $sendername = substr('VSCMSLTD', 0, 11);
    $recipients = $row_coopid2['MobilePhone'] ;//$_POST['telephone'];	
	$message = $message = 'COOP ACCT. BAL., MONTHLY CONTR.: '.number_format(round($row_getLoan['totalcontribution']),2,'.',','). ' SAVINGS: '.number_format($row_coopid2['savings'],2,'.',',').' SHARES: '.number_format($row_coopid2['shares'],2,'.',',').' INT PAID: '.number_format($row_getLoan['interest'],2,'.',','). ' UNPAID INT: '.number_format($row_coopid2['unpaidInterest'],2,'.',','). ' LOAN : '.number_format($row_getLoan ['newLoan'],2,'.',','). ' LOAN BAL: '.number_format($row_coopid2['loanBalance'],2,'.',',').'  AS AT: '. $row_payperiod['PayrollPeriod'].' ENDING';
	
	 $flash = 0;
    
        $message = stripslashes($message);
    
    $message = substr($message, 0, 320);
#Use the next line for HTTP POST with JSON
    
	if ($row_coopid2['MobilePhone'] != '' ){
//	$result = useJSON($json_url, $username, $apikey, $flash, $sendername, $message, $recipients);

        doSendMessage($recipients,$message) ;
        
	}
	
	
            if ($totalRows_oneSignal  > 0) {
                do{
                global $apiInstance;
                $notification = createNotificationPlayer($message, $row_oneSignal['player_id'], $row_oneSignal['coop_id']);
                $result__ = $apiInstance->createNotification($notification);
                }while ($row_oneSignal = mysqli_fetch_assoc($result_oneSignal));
            }

    echo str_repeat(' ',1024*64);

    
// Send output to browser immediately
	ob_end_flush();
    flush();

    

	  ob_start();

$i++;

//} while ($row_masterTransaction = mysqli_fetch_assoc($masterTransaction)); 
 } while ($row_coopid2 = mysqli_fetch_assoc($coopid2)); 

}
 


}
