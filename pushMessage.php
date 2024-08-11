<?php

//$data = array();
//$data['data']['notification']['title'] = "FCM Message";
//$data['data']['notification']['body'] = "This is an FCM Message";
//$data['data']['notification']['icon'] = "itwonders-web-logo.png";
//$data['data']['notification']['click_action'] = "https://emmaggi.com";
//$data['data']['webpush']['headers']['Urgency'] = "high";

$token= "d3u8_aEXKA4:APA91bE5ktRX3nd0AsxCBO5oFsfdpTSRHDoQDr4Cs4fZA9QIae8eoeGpODgfsRvQerk6J1zEAb1FY6eC-Bpt_3pKLO1YAQYwFUk__CMjTr815MyHlBJ68o-FMVI6Qf6AGXCHjbiD_aS4";//$_POST["token"];
$title= "OLABISI ONABANJO UNIVERSITY TEACHING HOSPITAL";// $_POST["title"];
$message= "Check out our new Gallery";//$_POST["message"];
$postlink= "https://oouth.com/oouth/portal/events/photos.php?folder=AGM%20OOUTHCOOP" ; //$_POST["postlink"];


$data = array(
"to" => "$token",
"notification" => array( 
"title" => "$title", 
"body" => "$message", 
"icon" => "https://oouth.com/oouth/portal/oouth_logo.gif", // Replace https://example.com/icon.png with your PUSH ICON URL
"click_action" => "$postlink")
);


$data['to'] = $token;
// print_r(json_encode($data));
$ch = curl_init();

curl_setopt($ch, CURLOPT_POST, 1);
$headers = array();
$headers[] = "Authorization: key = AAAA1vl2RHg:APA91bF1SChaxs2P1rfj6tP0Mun3zTCEtlSbsSHOOv9E9w8zHmgFpoJQXvd9zI1taIsQ7FXkDRO9u528s-v38IO_PgZTfcueM4RZq5BtkSIPaZdZPsfqGupKbEVE2JvhXvVNuYZXaItoGvH5c6S0jl34pDTVT2MaXg";
$headers[] = "Content-Type: application/json";
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

curl_setopt($ch, CURLOPT_URL , "https://fcm.googleapis.com/fcm/send");
curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch,CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($data));
// curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, false);
// curl_setopt($ch,CURLOPT_SSL_VERIFYPEER , false);

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$result = curl_exec($ch);
if (curl_errno($ch))
echo 'Error:' . curl_error($ch);

curl_close($ch);

echo "<pre>Result : ";
print_r(json_decode($result,1));
echo '<br>sent through</pre>';

?>
