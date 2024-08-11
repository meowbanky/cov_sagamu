<?php
echo curlPost('http://api.ebulksms.com/balance/cov@emmaggi.com/9e6ce612af1fa2dc982e668176e806435830e5ff');


function curlPost($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if ($error !== '') {
        throw new \Exception($error);
    }

    return $response;
}
?>