<?php
// PHP Example

// we add our public key and nonce to whatever parameters we are sending
$commands['user'] = 12;
if(true) {
	$commands['currency'] = 'krw';
	$commands['amount'] = 500000;
}
else {
	$commands['currency'] = 'btc';
	$commands['amount'] = 30;
}

$commands['api_key'] = 'BnVC4XHclpPvOywG';
$commands['nonce'] = time();

// create the signature
$data = json_encode($commands);
$signature = hash_hmac('sha256', json_encode($commands, JSON_NUMERIC_CHECK), 'WSvmNiMVGJ9aHB4dl0t6ugo7RQZ8YwXy');

// add signature to request parameters
$commands['signature'] = $signature;

$ch = curl_init();
curl_setopt_array($ch, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_SSL_VERIFYHOST => 0,
		CURLOPT_SSL_VERIFYPEER => 0,
		CURLOPT_URL => 'http://localhost:8080/kpbitcoin/bitexchange/frontend/public_api/index.php?endpoint=deposite',
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => $commands
));

$response = curl_exec($ch);

if (curl_errno($ch)) {
	$response = curl_error($ch);
	curl_close($ch);
	return $response;
}
curl_close($ch);
$response1 = json_decode($response, true);
if(false) {
	$response1['commands'] = $data;
}
echo json_encode($response1);
?>