<?php require_once('Connections/cov.php'); ?>
<?php

if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($conn_vote, $theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
    {
      $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

      $theValue = function_exists("mysqli_real_escape_string") ? mysqli_real_escape_string($conn_vote, $theValue) : mysqli_escape_string($conn_vote, $theValue);

      switch ($theType) {
        case "text":
          $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
          break;    
        case "long":
        case "int":
          $theValue = ($theValue != "") ? intval($theValue) : "NULL";
          break;
        case "double":
          $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
          break;
        case "date":
          $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
          break;
        case "defined":
          $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
          break;
      }
      return $theValue;
    }
   
}
 


if (isset($_GET['equality'])){
$equality = $_GET['equality'];} 
else {$equality = '>=';}

if (isset($_GET['period'])){
$period  = $_GET['period'];} 
else {$period  = '-1';}

if (isset($_GET['staffid'])){$staffid = $_GET['staffid'];}else {$staffid = '1';}

?>
<?php
mysqli_select_db($cov,$database_cov);
$query_masterTransaction = "SELECT  tlb_mastertransaction.periodid as Period FROM tlb_mastertransaction where periodid = ".$period;
$masterTransaction = mysqli_query($cov,$query_masterTransaction) or die(mysql_error());
$row_masterTransaction = mysqli_fetch_assoc($masterTransaction);
$totalRows_masterTransaction = mysqli_num_rows($masterTransaction);

mysqli_select_db($cov,$database_cov);
$query_MaxPeriod = "SELECT tbpayrollperiods.PayrollPeriod FROM tbpayrollperiods where periodid = " . $row_masterTransaction['Period'] ;
$MaxPeriod = mysqli_query($cov,$query_MaxPeriod) or die(mysql_error());
$row_MaxPeriod = mysqli_fetch_assoc($MaxPeriod);
$totalRows_MaxPeriod = mysqli_num_rows($MaxPeriod);

mysqli_select_db($cov,$database_cov);
$query_coopid2 = "SELECT
tbl_personalinfo.memberid,MobilePhone,
tlb_mastertransaction.transactionid,
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
WHERE tbl_personalinfo.memberid " . $equality ." ". $staffid . " AND Status = 'Active' AND tlb_mastertransaction.Periodid <= '". $period."' GROUP BY tbl_personalinfo.memberid ORDER BY memberid ASC";
$coopid2 = mysqli_query($cov,$query_coopid2) or die(mysql_error());
$row_coopid2 = mysqli_fetch_assoc($coopid2);
$totalRows_coopid2 = mysqli_num_rows($coopid2);


$query_payperiod = "SELECT tbpayrollperiods.Periodid, tbpayrollperiods.PayrollPeriod FROM tbpayrollperiods WHERE Periodid = ".$period;
$payperiod = mysqli_query($cov,$query_payperiod) or die(mysql_error());
$row_payperiod = mysqli_fetch_assoc($payperiod);
$totalRows_payperiod = mysqli_num_rows($payperiod);

?>
<?php
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

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

<title>..:<?php echo $row_title['value']; ?> SMS ALERT:..</title>

</head>



<body>
<div id="progress" style="width:500px;border:1px solid #ccc;"></div>
<!-- Progress information -->
<div id="information" style="width"></div>



<?php
  
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
//ob_end_flush();
//ob_start();
//ob_end_flush();
$total = $totalRows_coopid2;   
//for( $i=0; $i <= $total; $i++ ){
// Calculate the percentation
    $percent = intval($i/$total * 100)."%";
	
	
//mysql_select_db($database_cov, $cov);
//$query_getContriubtion = "SELECT ifnull((tbl_contributions.contribution),0) as contri_input, tbl_contributions.contribution  FROM tbl_contributions WHERE membersid = '".$row_coopid2['memberid ']."' AND periodid = '".$period. "'";
//$getContriubtion = mysql_query($query_getContriubtion, $cov) or die(mysql_error());
//$row_getContriubtion = mysql_fetch_assoc($getContriubtion);
//$totalRows_getContriubtion = mysql_num_rows($getContriubtion);


mysqli_select_db($cov,$database_cov);
$query_getLoan = "SELECT ifnull((tlb_mastertransaction.loanAmount),0.00) as newLoan, interest, (sum(tlb_mastertransaction.shares) + sum(tlb_mastertransaction.loanRepayment)+sum(tlb_mastertransaction.savings)+sum(tlb_mastertransaction.interestPaid)) as totalcontribution  FROM tlb_mastertransaction WHERE memberid = '".$row_coopid2['memberid']."' and periodid = '".$period."'";
$getLoan = mysqli_query($cov,$query_getLoan) or die(mysql_error());
$row_getLoan = mysqli_fetch_assoc($getLoan);
$totalRows_getLoan = mysqli_num_rows($getLoan);

mysqli_select_db($cov,$database_cov);
$query_title = "SELECT tbl_globa_settings.`value` FROM tbl_globa_settings WHERE tbl_globa_settings.setting_id = 1";
$title = mysqli_query($cov,$query_title) or die(mysql_error());
$row_title = mysqli_fetch_assoc($title);
$totalRows_title = mysqli_num_rows($title);

	$username = "cov@emmaggi.com"; //$_POST['username'];
    $apikey = "9e6ce612af1fa2dc982e668176e806435830e5ff";//$_POST['apikey'];
	 $sendername = substr('VSCMSLTD', 0, 11);
    $recipients = $row_coopid2['MobilePhone'] ;//$_POST['telephone'];	
	$message = $message = 'COOP ACCT. BAL., MONTHLY CONTR.: '.number_format(round($row_getLoan['totalcontribution']),2,'.',','). ' SAVINGS: '.number_format($row_coopid2['savings'],2,'.',',').' SHARES: '.number_format($row_coopid2['shares'],2,'.',',').' INT PAID: '.number_format($row_getLoan['interest'],2,'.',','). ' UNPAID INT: '.number_format($row_coopid2['unpaidInterest'],2,'.',','). ' LOAN : '.number_format($row_getLoan ['newLoan'],2,'.',','). ' LOAN BAL: '.number_format($row_coopid2['loanBalance'],2,'.',',').'  AS AT: '. $row_payperiod['PayrollPeriod'].' ENDING';
	
	 $flash = 0;
    if (get_magic_quotes_gpc()) {
        $message = stripslashes($message);
    }
    $message = substr($message, 0, 320);
#Use the next line for HTTP POST with JSON
    
	if ($row_coopid2['MobilePhone'] != '' ){
	$result = useJSON($json_url, $username, $apikey, $flash, $sendername, $message, $recipients);
	}
	
#Uncomment the next line and comment the one above if you want to use HTTP POST with XML
    //$result = useXML($xml_url, $username, $apikey, $flash, $sendername, $message, $recipients);

	
//doSendMessage($recipients = $row_coopid2['MobilePhone'], $message = 'Your NASUWEL ACCT. BAL., MONTHLY CONTR. : '.number_format($row_getContriubtion['contri_input'],2,'.',','). ' WELFARE SAVINGS: '.number_format($row_coopid2['Contribution'],2,'.',','). ' LOAN BAL: '.number_format($row_coopid2['Loanbalance'],2,'.',',').'  AS AT: '. $row_payperiod['PayrollPeriod']);


// Javascript for updating the progress bar and information
   echo '<script language="javascript">
         document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.';background-color:#ddd; background-image:url(pbar-ani.gif)\">&nbsp;</div>";
    document.getElementById("information").innerHTML="'.$i.' row(s) processed.";
    </script>';

    
// This is for the buffer achieve the minimum size in order to flush data
    echo str_repeat(' ',1024*64);

    
// Send output to browser immediately
	ob_end_flush();
    flush();

    
// Sleep one second so we can see the delay
    //sleep(1);
	

//echo $i . "  messages sent <br>" ;
      
	   if ($result == 'SUCCESS') {
      echo "SMS Sent to :- " . $row_coopid2['MobilePhone'] . "<br>" ;
	  echo $percent;
 }else {echo $result; echo 'error';}
	  
	  //echo "SMS Sent to :- " . $row_coopid2['MobilePhone'] . "<br>" ;
	  ob_start();
//}  
$i++;

//} while ($row_masterTransaction = mysqli_fetch_assoc($masterTransaction)); 
 } while ($row_coopid2 = mysqli_fetch_assoc($coopid2)); 
 echo $result;
echo '<script language="javascript">document.getElementById("information").innerHTML="Process completed"</script>';

}
 
//}
// Tell user that the process is completed
?>
</body>

</html>

<?php
mysqli_free_result($title);

mysqli_free_result($masterTransaction);
?>


