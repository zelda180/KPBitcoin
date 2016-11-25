<?php 
class BitExchange{
	private static $commands,$nonce, $api_pub_key, $api_signature, $api_key, $api_update_nonce, $raw_params_json,$end_point;
	
	static public function commands($end_point, $paramas) {
		BitExchange::$end_point = $end_point;
		BitExchange::$commands = $paramas;
	}
	
	static public function apiKey($api_key, $api_pub_key) {
		BitExchange::$api_key = $api_key;
		BitExchange::$api_pub_key = $api_pub_key;
	}

	static public function apiSignature($api_signature,$raw_params_json) {
		BitExchange::$api_signature = $api_signature;
		BitExchange::$raw_params_json = $raw_params_json;
	}
	
	static public function apiUpdateNonce() {
		BitExchange::$api_update_nonce = 1;
	}
	
	static public function apiEndPoint($end_point) {
		BitExchange::$end_point = $end_point;
	}
	
	static public function send($nonce=false) {
		global $CFG;
		
		if (!is_array(BitExchange::$commands) || BitExchange::$end_point == null)
			return false;

		$commands = BitExchange::$commands;
		
		if(BitExchange::$api_pub_key != null) {
			$commands['api_key'] = BitExchange::$api_pub_key;
			$commands['nonce'] = time();
			
			$data = json_encode($commands);
			$signature = hash_hmac('sha256', json_encode($commands, JSON_NUMERIC_CHECK), BitExchange::$api_key);
			$commands['signature'] = $signature;
		}
		$ch = curl_init();
		if($CFG->debug == true) {
			curl_setopt_array($ch, array(
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_SSL_VERIFYHOST => 0,
					CURLOPT_SSL_VERIFYPEER => 0,
					CURLOPT_URL => $CFG->bitcoin_api_url.'?endpoint='.BitExchange::$end_point,
					CURLOPT_POST => 1,
					CURLOPT_VERBOSE => true,
					CURLOPT_COOKIE => 'XDEBUG_SESSION=ECLIPSE_DBGP',
					CURLOPT_POSTFIELDS => $commands
			));
		}
		else {
			curl_setopt_array($ch, array(
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_SSL_VERIFYHOST => 0,
					CURLOPT_SSL_VERIFYPEER => 0,
					CURLOPT_URL => $CFG->bitcoin_api_url.'?endpoint='.BitExchange::$end_point,
					CURLOPT_POST => 1,
					CURLOPT_POSTFIELDS => $commands
			));
		}
		$result1 = curl_exec($ch);
		
		if (curl_errno($ch)) {
			$response = curl_error($ch);
			curl_close($ch);
			return $response;
		}
		curl_close($ch);

		$result = json_decode($result1,true);
	
		BitExchange::$commands = array();
		BitExchange::$end_point = null;
		return $result;
	}
	
	static public function getUserIp() {
		$ip_addresses = array();
		$ip_elements = array(
				'HTTP_X_FORWARDED_FOR', 'HTTP_FORWARDED_FOR',
				'HTTP_X_FORWARDED', 'HTTP_FORWARDED',
				'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_CLUSTER_CLIENT_IP',
				'HTTP_X_CLIENT_IP', 'HTTP_CLIENT_IP',
				'REMOTE_ADDR'
		);
		
		foreach ( $ip_elements as $element ) {
			if(isset($_SERVER[$element])) {
				if (!is_string($_SERVER[$element]) )
					continue;
				
				$address_list = explode(',',$_SERVER[$element]);
				$address_list = array_map('trim',$address_list);

				foreach ($address_list as $x)
					$ip_addresses[] = $x;
			}
		}
		
		if (count($ip_addresses) == 0)
			return false;
		else
			return $ip_addresses[0];
	}
}

?>