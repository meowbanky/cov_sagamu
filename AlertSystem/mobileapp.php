<?php 
require_once('Connections/cov.php'); 
if (isset($_GET['staffid'])){$staffid = $_GET['staffid'];}else {$staffid = '1';}

?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
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


if (isset($_POST['equality'])){
$equality = $_POST['equality'];} 
else {$equality = '>=';}

if (isset($_POST['staffid'])){$staffid = $_POST['staffid'];}else {$staffid = '1';}


mysql_select_db($database_hms, $hms);
$query_PersonalinFo = "SELECT tblusers.UserID, tblusers.lastname, tblusers.middlename, tblusers.firstname, tblusers.Username, tblusers.PlainPassword, tbl_personalinfo.MobilePhone FROM tblusers INNER JOIN tbl_personalinfo ON tbl_personalinfo.memberid = tblusers.UserID WHERE ordered_id " . $equality ." ". $staffid . " AND tbl_personalinfo.Status = 'Active' GROUP BY
tbl_personalinfo.patientid ORDER BY ordered_id ASC" ;
$PersonalinFo = mysql_query($query_PersonalinFo, $hms) or die(mysql_error());
$row_PersonalinFo = mysql_fetch_assoc($PersonalinFo);
$totalRows_PersonalinFo = mysql_num_rows($PersonalinFo);

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

<title>..:OOUTH WHWUN SMS ALERT:..</title>

</head>



<body>
<div id="progress" style="width:500px;border:1px solid #ccc;"></div>
<!-- Progress information -->
<div id="information" style="width"></div>



<?php
  
$json_url = "http://api.ebulksms.com:8080/sendsms.json";
$xml_url = "http://api.ebulksms.com:8080/sendsms.xml";
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
$total = $totalRows_PersonalinFo ;   
//for( $i=0; $i <= $total; $i++ ){
// Calculate the percentation
    $percent = intval($i/$total * 100)."%";
	
	
	$username = "cov@emmaggi.com"; //$_POST['username'];
    $apikey = "9e6ce612af1fa2dc982e668176e806435830e5ff";//$_POST['apikey'];
	 $sendername = substr('VSCMSLTD', 0, 11);
    $recipients = $row_coopid2['MobilePhone'] ;//$_POST['telephone'];	
	$message = 'Please download the COV COOP android mobile App via http://emmaggi.com/cov/download/cov_coop.apk Your username is:-'. $row_PersonalinFo ['UserID']. ' and your Password :- '. $row_PersonalinFo['PlainPassword'] .' You will be prompted to change your password on your first login. Kindly use password you can easily remember.';
	
 	
	
	$flash = 0;
    if (get_magic_quotes_gpc()) {
        $message = stripslashes($message);
    }
    $message = substr($message, 0, 320);
#Use the next line for HTTP POST with JSON
    $result = useJSON($json_url, $username, $apikey, $flash, $sendername, $message, $recipients);
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
      echo "SMS Sent to :- " . $row_PersonalinFo['MobilePhone'] . "<br>" ;
	  echo $percent;
 }else {echo $result; echo 'error';}
	  
	  //echo "SMS Sent to :- " . $row_coopid2['MobilePhone'] . "<br>" ;
	  ob_start();
//}  
$i++;

//} while ($row_masterTransaction = mysql_fetch_assoc($masterTransaction)); 
 } while ($row_PersonalinFo  = mysql_fetch_assoc($PersonalinFo)); 
 echo $result;
echo '<script language="javascript">document.getElementById("information").innerHTML="Process completed"</script>';

}
 
//}
// Tell user that the process is completed
?>
</body>

</html>

<?php
mysql_free_result($PersonalinFo );
?>


