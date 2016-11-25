<?php

class User {
	public static $info, $on_hold;
	
	public static function setInfo($info) {
		User::$info = $info;
		if (!empty($info['id'])) {
			$balances = self::getBalances($info['id']);
			if ($balances) {
				foreach ($balances as $abbr => $row) {
					User::$info[$abbr] = $row;
				}
			}
		}
	}
	
	public static function getInfo($session_id=false) {
		global $CFG;
		
		$session_id = preg_replace("/[^0-9]/", "",$session_id);
		
		if (!($session_id > 0) || !$CFG->session_active)
			return false;
	
		$result = db_query_array('SELECT site_users.first_name,site_users.last_name,site_users.country,site_users.email, site_users.default_currency FROM sessions LEFT JOIN site_users ON (sessions.user_id = site_users.id) WHERE sessions.session_id = '.$session_id);
		return $result[0];
	}
	
	public static function userExists($email) {
		$email = preg_replace("/[^0-9a-zA-Z@\.\!#\$%\&\*+_\~\?\-]/", "",$email);
	
		if (!$email)
			return false;
	
		$sql = "SELECT id FROM site_users WHERE email = '$email'";
		$result = db_query_array($sql);
	
		if ($result)
			return $result[0]['id'];
		else
			return false;
	}
	
	
	public static function registerNew($info) {
		global $CFG;
		
		if (!is_array($info))
			return false;
		
		$info['email'] = preg_replace("/[^0-9a-zA-Z@\.\!#\$%\&\*+_\~\?\-]/", "",$info['email']);
		$exist_id = self::userExists($info['email']);
		if ($exist_id > 0) {
			$user_info = DB::getRecord('site_users',$exist_id,0,1);
			$email = SiteEmail::getRecord('register-existing');
			Email::send($CFG->form_email,$info['email'],$email['title'],$CFG->form_email_from,false,$email['content'],$user_info);
			return false;
		}

		BitExchange::commands('signup', $info);
		$query = BitExchange::send();
			
		if(empty($query['errors']) == true) {
			$info['date'] = date('Y-m-d H:i:s');
			unset($info['terms']);
			$info['bitcoin_api_key'] = $query['authy_id'];
			$info['pass'] = Encryption::hash($info['pass']);
			$record_id = db_insert('site_users',$info);
			
			if($record_id == 0) {
				return false;
			}
			
			return true;
		}

		return false;
	}
	

	public static function deleteCache($session_id=false) {
		global $CFG;
		
		$session_id = (!$session_id) ? $CFG->session_id : $session_id;
		if ($CFG->memcached && $CFG->session_id)
			$CFG->delete_cache = $CFG->m->delete('session_'.$CFG->session_id);
	}
	
	public static function verifyUser($email, $pass, $token) {
		$sql = "SELECT * FROM site_users WHERE email = '$email'";
		$result = db_query_array($sql);
		
		if ($result == null)
			return false;
		
		$info = array();
		$info['authy_id'] = $result[0]['bitcoin_api_key'];
		$info['token'] = $token;
		$info['email'] = $email;
		$info['pass'] = $pass;
		$info['tel'] =  $result[0]['tel'];
		
		BitExchange::commands('verify_user', $info);
		$query = BitExchange::send();
			
		if(empty($query['errors']) == true) {			
			$ret = db_update('site_users', $result[0]['id'], array('bitcoin_api_key'=>$query['api_secret_key'], 'bitcoin_api_pub_key'=>$query['api_key']));
			return true;
		}
		
		return $query;
	}
	
	public static function login($email, $pass) {
		$sql = "SELECT * FROM site_users WHERE email = '$email'";
		$result = db_query_array($sql);
		
		if ($result == null)
			return false;
		
		BitExchange::apiKey($result[0]['bitcoin_api_key'], $result[0]['bitcoin_api_pub_key']);
		$info = array();
		$info['email'] = $email;
		$info['pass'] = $pass;
		
		BitExchange::commands('login', $info);
		$query = BitExchange::send();
		
		
		if(empty($query['errors']) == true) {
			
			$res = openssl_pkey_new(array("digest_alg"=>"sha256","private_key_bits"=>512,"private_key_type"=>OPENSSL_KEYTYPE_RSA));
			openssl_pkey_export($res,$private);
			
			$public = openssl_pkey_get_details($res);
			
			$public = $public["key"];
			$nonce = rand(2,99999);
			
			$session_id = db_insert('sessions',array('session_key'=>$public,'user_id'=>$result[0]['id'],'nonce'=>$nonce,'session_time'=>date('Y-m-d H:i:s'),'session_start'=>date('Y-m-d H:i:s'),'awaiting'=>(($awaiting_token) ? 'Y' : 'N'),'ip'=>$ip1));
			$return['user_id'] = $result[0]['id'];
			$return['session_id'] = $session_id;
			$return['session_key'] = $private;
			$return['nonce'] = $nonce;
			
			return $session_id;
		}
		
		return false;
	}
	public static function verifyLogin() {
		global $CFG;
	
		// IP throttling
		$login_attempts = 0;
			if (!($CFG->session_id > 0))
				return array('message'=>'not-logged-in','attempts'=>$login_attempts);
	
			if (!User::$info) {
				return array('error'=>'session-not-found','attempts'=>$login_attempts);
			}
	
			if (User::$info['ip'] != $CFG->client_ip) {
				return array('message'=>'session-not-found','attempts'=>$login_attempts);
			}
	
			if (User::$info['awaiting'] == 'Y') {
				return array('message'=>'awaiting-token','attempts'=>$login_attempts);
			}
	
			$return_values = array(
					'user',
					'first_name',
					'last_name',
					'fee_schedule',
					'tel',
					'country');			
			$return = array();
			foreach (User::$info as $key => $value) {
				if (in_array($key,$return_values))
					$return[$key] = $value;
			}
			
			if ($return['country_code'] > 0) {
				$s = strlen($return['country_code']);
				$return['country_code'] = str_repeat('x',$s);
			}
			
			if ($return['tel'] > 0) {
				$s = strlen($return['tel']) - 2;
				$return['tel'] = str_repeat('x',$s).substr($return['tel'], -2);
			}
			
			if (User::$info['default_currency'] > 0) {
				$currency = $CFG->currencies[User::$info['default_currency']];
				$return['default_currency_abbr'] = $currency['currency'];
			}
			
			return array('message'=>'logged-in','info'=>$return);
		}
		
		private static function findAPIkey($session_id=false) {
			
			if (!($session_id > 0))
				return false;
			
			$session_id = preg_replace("/[^0-9]/", "",$session_id);
			$result = db_query_array('SELECT sessions.nonce AS nonce ,sessions.session_key AS session_key, sessions.ip AS ip, sessions.awaiting AS awaiting, site_users.* FROM sessions LEFT JOIN site_users ON (sessions.user_id = site_users.id) WHERE sessions.session_id = '.$session_id);
			
			if ($result == null)
				return false;
			
			BitExchange::apiKey($result[0]['bitcoin_api_key'], $result[0]['bitcoin_api_pub_key']);
			return true;
		}
		
		public static function logOut($session_id=false) {
			if (!($session_id > 0))
				return false;
		
			$session_id = preg_replace("/[^0-9]/", "",$session_id);
		
			self::deleteCache();
			
			$result = self::findAPIkey($session_id);
			
			if($result == false) {
				return false;
			}
			
			BitExchange::commands('logout', array());
			$query = BitExchange::send();
			
			return db_delete('sessions',$session_id,'session_id');
		}
		
		public static function deposit($session_id, $amount) {
			if (!($session_id > 0))
				return false;
			
			$session_id = preg_replace("/[^0-9]/", "",$session_id);
			
			self::deleteCache();
				
			$result = self::findAPIkey($session_id);
				
			if($result == false) {
				return false;
			}
				
			$commands = array();

			$commands['currency'] = 'krw';
			$commands['amount'] = $amount;
			BitExchange::commands('deposite', $commands);
			$query = BitExchange::send();

			if(empty($query['errors']) == true) {
				return true;
			}
			
			return $query;
		}
		
		public static function sendKRW($session_id, $toEmail, $amount) {
			
			if (!($session_id > 0))
				return false;
				
			$session_id = preg_replace("/[^0-9]/", "",$session_id);
				
			self::deleteCache();
			
			$result = self::findAPIkey($session_id);
			
			if($result == false) {
				return false;
			}
			
			$commands = array();
			
			// 1. KRW분에 해당한 BTC구매  		(order)
			BitExchange::commands('get-current-bid-ask', $commands);
			$query = BitExchange::send();
			if(empty($query['errors']) == false) {
				return false;
			}
			$bid = $query['bid'];
			$ask = $query['ask'];
			
			$commands['side'] = 'buy';
			$commands['type'] = 'limit';
			$commands['currency'] = 'krw';
			$commands['limit_price'] = $bid;
			$commands['amount'] = $amount/$bid;
			
			/*BitExchange::commands('orders/new', $commands);
			$query = BitExchange::send();
			if(empty($query['errors']) == false) {
				return false;
			}*/
			
			$btc_amount = $amount/$bid;
			
			// 2. btc를 $toEmail유저에게 전송	(withdrawl btc)
			$commands = array();
			$commands['email'] = $toEmail;
			BitExchange::commands('get-bitcoin-address', $commands);
			$query = BitExchange::send();
			if(empty($query['errors']) == false) {
				return $query;
			}
			$receiver_address = $query['bitcoin_address'];
			
			$commands = array();
			$commands['currency'] = 'btc';
			$commands['amount'] = $btc_amount;
			$commands['address'] = $receiver_address;
			BitExchange::commands('withdrawals/new', $commands);
			$query = BitExchange::send();
			if(empty($query['errors']) == false) {
				return $query;
			}
		
			// 3. $toEmail유저의 btc_amount를 팔아서 KRW로 전환
			
			
			return $query;
			
		}
	
}

?>
