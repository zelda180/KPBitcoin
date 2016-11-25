<?php
// PHP Example

// we add our public key and nonce to whatever parameters we are sending
$commands['user'] = 111010;
//$commands['first_name'] = 'bitcoin';
//$commands['last_name'] = 'kim';
$commands['pass'] = 'test123123';
//$commands['tel'] = '1-566-228-7091';
//$commands['email'] = 'leebongchun@outlook.com';

$commands['api_key'] = 'aFJrYXvi3q9EZgc1';
$commands['nonce'] = time();
// create the signature
$data = json_encode($commands);
$signature = hash_hmac('sha256', json_encode($commands, JSON_NUMERIC_CHECK), 'vgQNuIqWOhbEBpT0o3tn6wPzsFUHafD2');

// add signature to request parameters
$commands['signature'] = $signature;

$ch = curl_init();
curl_setopt_array($ch, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_SSL_VERIFYHOST => 0,
		CURLOPT_SSL_VERIFYPEER => 0,
		CURLOPT_URL => 'http://localhost:8080/kpbitcoin/bitexchange/frontend/public_api/index.php?endpoint=update_personal_info',
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