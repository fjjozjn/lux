<?
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://www.thienhk.com/payment/mol/mol_test_ip.php");
//curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:8.8.8.8', 'CLIENT-IP:8.8.8.8'));  //构造IP
//curl_setopt($ch, CURLOPT_REFERER, "http://www.gosoa.com.cn/ ");   //构造来路
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
$out = curl_exec($ch);
curl_close($ch);