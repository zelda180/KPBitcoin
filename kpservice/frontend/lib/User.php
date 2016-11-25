<?php
class User {
	private static $logged_in;
	public static $awaiting_token, $info, $attempts, $timeout;
	
	static function logIn($user=false, $pass=false, $email=false, $email_authcode=false,$email_authcode_request=false) {
		global $CFG;
		
		API::add('User','login',array($email, $pass));
		$query = API::send();
		$login = $query['User']['login']['results'][0];
		
		if ($login && empty($login['errors'])) {
			$_SESSION['session_id'] = $login;
		}
		return $login;
	}
	
	static function verifyLogin($query) {
		global $CFG;
		
		if (isset($query['User']['verifyLogin']['results'][0]))
			$result = $query['User']['verifyLogin']['results'][0];

		if (!empty($result['attempts']) && (empty($_SESSION['attempts']) || $result['attempts'] > $_SESSION['attempts']))
			self::$attempts = $result['attempts'];
		else if (!empty($_SESSION['attempts']))
			self::$attempts = $_SESSION['attempts'];
		
		if (empty($_SESSION['session_id']))
			return false;

		if (!empty($result['error']) || !empty($query['error']) || !isset($result)) {
			$session_id = session_id();
			if (!empty($session_id)) {
				session_destroy();
				$_SESSION = array();
			}
			return false;
		}
		
		if (!empty($result['message']) && $result['message'] == 'awaiting-token') {
			self::$awaiting_token = true;
			return true;
		}
		else {
			self::$info = $result['info'];
			self::$logged_in = true;
			//self::updateNonce();
			return true;
		}
	}
	
	static function verifyToken($token,$dont_ask=false) {
		global $CFG;
		
		// TODO
		
		return  true;
	}
	
	static function isLoggedIn() {
		return self::$logged_in;
	}
	
	static function logOut($logout, $session_id=false) {
		if ($logout && $_REQUEST['uniq'] == $_SESSION["logout_uniq"]) {
			
			API::add('User','logOut',array($_SESSION['session_id']));
			API::send();
			
			$lang = $_SESSION['language'];
			unset($_SESSION);
			session_destroy();
			session_start();
			$_SESSION['language'] = $lang;
			
			self::$logged_in = false;
			self::$info = false;
			return true;
		}
	}
	
	static function updateNonce() {
		if (!self::$logged_in)
			return false;
		
		$_SESSION['nonce']++;
		return true;
	}
	
	static function sendSMS($authy_id=false) {
		global $CFG;
		
		API::add('User','sendSMS',array($authy_id));
		$query = API::send();
		$response = $query['User']['sendSMS']['results'][0];
		
		if (!$response || !is_array($response))
			Errors::add(Lang::string('security-com-error'));
		elseif ($response['success'] === false)
			Errors::merge($response['errors']);
		else {
			return true;
		}
	}
}
?>