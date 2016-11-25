<?php
// PHP Example

// we add our public key and nonce to whatever parameters we are sending

if(false) {
	$commands['side'] = 'buy';
	$commands['user'] = 11;
	
	$commands['type'] = 'limit';
	$commands['currency'] = 'krw';
	$commands['limit_price'] = '655490'; 
	$commands['amount'] = '0.02';
	
	$commands['api_key'] = 'BnVC4XHclpPvOywG';
	$commands['nonce'] = time();
	
	// create the signature
	$data = json_encode($commands);
	$signature = hash_hmac('sha256', json_encode($commands, JSON_NUMERIC_CHECK), 'WSvmNiMVGJ9aHB4dl0t6ugo7RQZ8YwXy');
	
}
else { //
	$commands['side'] = 'sell'; //buy, sell
	$commands['user'] = 6;
	
	$commands['type'] = 'limit';
	$commands['currency'] = 'krw';
	$commands['limit_price'] = '655490'; // usd btc가격에 curencies의 환전율을 곱하여 차이가 5%미만인 값을 택해야 한다.
	$commands['amount'] = '0.02';
	
	$commands['api_key'] = 'UjRNE2AOM6lnpK9m';
	$commands['nonce'] = time();
	
	// create the signature
	$data = json_encode($commands);
	$signature = hash_hmac('sha256', json_encode($commands, JSON_NUMERIC_CHECK), 'VwyPJqamnDu5Ztpojgf2XAzxsvGl9diF');
}

// add signature to request parameters
$commands['signature'] = $signature;

$ch = curl_init();
curl_setopt_array($ch, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_SSL_VERIFYHOST => 0,
		CURLOPT_SSL_VERIFYPEER => 0,
		CURLOPT_URL => 'http://localhost:8080/kpbitcoin/bitexchange/frontend/public_api/index.php?endpoint=orders/new',
		CURLOPT_USERAGENT => 'Codular Sample cURL Request',
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