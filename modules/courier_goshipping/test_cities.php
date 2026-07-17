<?php
$ch = curl_init('http://localhost:8080/perfex_crm/courier/portal/get_cities');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['country'=>'Kenya']));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
echo "Result: " . $res;
?>
