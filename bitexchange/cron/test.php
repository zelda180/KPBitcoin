#!/usr/bin/php
<?php
echo "Beginning Send Bitcoin processing...".PHP_EOL;

include 'common.php';

$bitcoin = new Bitcoin($CFG->bitcoin_username,$CFG->bitcoin_passphrase,$CFG->bitcoin_host,$CFG->bitcoin_port,$CFG->bitcoin_protocol);
$status = DB::getRecord('status',1,0,1);
$available = $status['hot_wallet_btc'];
$deficit = $status['deficit_btc'];
$bitcoin->settxfee($CFG->bitcoin_sending_fee);

// for test
if(false) {
	$ret = $bitcoin->getaccountaddress($CFG->bitcoin_username);
	if($ret == false) {
		echo $bitcoin->error;
	}
	else {
		echo $ret;
	}
}


echo 'done'.PHP_EOL;
