<?php
if (!isset($_GET['q'])) exit;

$query = urlencode($_GET['q']);
$url = "https://nominatim.openstreetmap.org/search?format=json&q=$query";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, "UrbanaFix/1.0");
$response = curl_exec($ch);
curl_close($ch);

header('Content-Type: application/json');
echo $response;
