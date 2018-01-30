<?php
if(isset($_POST['input'])){

$ch = curl_init();
$input = $_POST['input'];
$input = urlencode($input);
curl_setopt($ch, CURLOPT_URL, "https://api.wit.ai/message?q=".$input);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");


$headers = array();
$headers[] = "Authorization: Bearer UVK5TW3AEBML3DE2HVHWDRUPBHBFNAEL";
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$result = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
}
curl_close ($ch);
$json = json_decode($result);
echo json_encode($json, JSON_PRETTY_PRINT);
}
?>
