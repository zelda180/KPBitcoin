<?php

include '../lib/common.php';

$page_title = Lang::string('verify-token');

if (!empty($_REQUEST['submitted'])) {

	if (!empty($_REQUEST['submitted']) && (empty($_SESSION["register_uniq"]) || $_SESSION["register_uniq"] != $_REQUEST['uniq']))
		Errors::add('Page expired.');
	
	$email = $_SESSION['register_temp_email'];
	$pass = $_REQUEST['verify']['pass'];
	$token = $_REQUEST['verify']['token'];
	API::add('User','verifyUser',array($email, $pass, $token));
	$query = API::send();
	
	$result = $query['User']['verifyUser']['results'][0];
	if($result == true) {
		Link::redirect($CFG->baseurl.'login.php');
	}
	else {
		Errors::add(Lang::string('security-no-token'));
	}
}

$_SESSION["register_uniq"] = md5(uniqid(mt_rand(),true));
include 'includes/head.php';
?>
<div class="page_title">
	<div class="container">
		<div class="title"><h1><?= Lang::string('verify-token') ?></h1></div>
	</div>
</div>
<div class="fresh_projects login_bg">
	<div class="clearfix mar_top8"></div>
	<div class="container">
    	<h2><?= Lang::string('verify-token') ?></h2>
    	<? 
    	if (count(Errors::$errors) > 0) {
			echo '
		<div class="error" id="div4">
			<div class="message-box-wrap">
				'.((User::$timeout > 0) ? str_replace('[timeout]','<span class="time_until"></span><input type="hidden" class="time_until_seconds" value="'.(time() + User::$timeout).'" />',Lang::string('login-timeout')) : Errors::$errors[0]).'
			</div>
		</div>';
		}
		
		if (count(Messages::$messages) > 0) {
			echo '
		<div class="messages" id="div4">
			<div class="message-box-wrap">
				'.Messages::$messages[0].'
			</div>
		</div>';
		}
    	?>
    	<form method="POST" action="verify-user.php" name="verify">
	    	<div class="loginform">
	    		<div class="loginform_inputs">
		    		<div class="input_contain">
		    			<i class="fa fa-user"></i>
		    			<input type="text" class="login" name="verify[token]" value="please input token.">
		    		</div>
		    		<div class="separate"></div>
		    		<div class="input_contain last">
		    			<i class="fa fa-lock"></i>
		    			<input type="password" class="login" name="verify[pass]">
		    		</div>
	    		</div>
	    		<input type="hidden" name="submitted" value="1" />
	    		<input type="hidden" name="uniq" value="<?= $_SESSION["register_uniq"] ?>" />
	    		<input type="submit" name="submit" value="<?= Lang::string('verify-token') ?>" class="but_user" />
	    	</div>
    	</form>
    	<a class="forgot" href="how-to-register.php"><?= Lang::string('login-dont-have') ?></a>
    </div>
    <div class="clearfix mar_top8"></div>
</div>
<? include 'includes/foot.php'; ?>