<?php
// PHP Example

// we add our public key and nonce to whatever parameters we are sending
$commands['token'] = '1728277';
$commands['user'] = 111;

$ch = curl_init();
curl_setopt_array($ch, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_SSL_VERIFYHOST => 0,
		CURLOPT_SSL_VERIFYPEER => 0,
		CURLOPT_URL => 'http://localhost:8080/kpbitcoin/bitexchange/frontend/public_api/index.php?endpoint=verify_token',
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

echo json_encode($response1);
?>