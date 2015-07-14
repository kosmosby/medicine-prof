<?php


$ch = curl_init("78.140.138.8:5080");
$fp = fopen("example_homepage.txt", "w");

curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_HEADER, 0);

$response = curl_exec($ch);

echo "<pre>";
print_r($response); die;

curl_close($ch);
fclose($fp);









$session = curl_init("http://78.140.138.8:5080/openmeetings/services/UserService/getSession");

curl_setopt($session, 42, true);
curl_setopt($session, 19913, true);

$response = curl_exec($session);




?>
