<?php

function doBulkSendMessage($to, $message)
{
    $curl = curl_init();
    $country_code = '234';

    $gsm = array();
    $country_code = '234';
    $arr_recipient = explode(',', $to);
    foreach ($arr_recipient as $recipient) {
        $mobilenumber = trim($recipient);
        if (substr($mobilenumber, 0, 1) == '0') {
            $mobilenumber = $country_code . substr($mobilenumber, 1);
        } elseif (substr($mobilenumber, 0, 1) == '+') {
            $mobilenumber = substr($mobilenumber, 1);
        }
        array_push($gsm, $mobilenumber);
    }
    $data = array(
        "to" => $gsm, "from" => "VCMSSAGAMU",
        "sms" => $message, "type" => "plain", "channel" => "generic", "api_key" => "TLYa2oT5vTpT3X4r3fSv2lSfErDApbmhbOAjOP3ituAA2XnLYMFIqzrq3leU1y"
    );

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
        CURLOPT_POSTFIELDS => $post_data,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
}



$SentMessageMessage = $_POST['SentMessageMessage'];
$SentMessageRecipient = $_POST['SentMessageRecipient'];

$response = doBulkSendMessage($SentMessageRecipient, $SentMessageMessage);

$response = json_decode($response, true);

if ($response['message'] == 'Successfully Sent') {
    print_r('Message Successfully Sent');
} else {
    echo $response['message'] . 'ERROR';
}
