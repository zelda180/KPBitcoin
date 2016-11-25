<?php
include '../lib/common.php';

$_REQUEST['register']['email'] = (!empty($_REQUEST['register']['email'])) ? preg_replace("/[^0-9a-zA-Z@\.\!#\$%\&\*+_\~\?\-]/", "",$_REQUEST['register']['email']) : false;


if (empty($CFG->google_recaptch_api_key) || empty($CFG->google_recaptch_api_secret))
	$_REQUEST['is_caco'] = (!empty($_REQUEST['form_name']) && empty($_REQUEST['is_caco'])) ? array('register'=>1) : (!empty($_REQUEST['is_caco']) ? $_REQUEST['is_caco'] : false);

if (empty($_REQUEST['form_name']))
	unset($_REQUEST['register']);

if (!empty($_REQUEST['register']['pass'])) {
	$match = preg_match_all($CFG->pass_regex,$_REQUEST['register']['pass'],$matches);
	$too_few_chars = (mb_strlen($_REQUEST['register']['pass'],'utf-8') < $CFG->pass_min_chars);
}

$register = new Form('register',false,false,'form3');
unset($register->info['uniq']);
$register->verify();
$register->reCaptchaCheck();

if ($match)
	$register->errors[] = htmlentities(str_replace('[characters]',implode(',',array_unique($matches[0])),Lang::string('login-pass-chars-error')));

if (!empty($_REQUEST['register']) && (empty($_SESSION["register_uniq"]) || $_SESSION["register_uniq"] != $_REQUEST['register']['uniq']))
	$register->errors[] = 'Page expired.';

if (!empty($_REQUEST['register']) && !$register->info['terms'])
	$register->errors[] = Lang::string('settings-terms-error');

if (!empty($_REQUEST['register']) && (is_array($register->errors))) {
	$errors = array();
	
	if ($register->errors) {
		foreach ($register->errors as $key => $error) {
			if (stristr($error,'login-required-error')) {
				$errors[] = Lang::string('settings-'.str_replace('_','-',$key)).' '.Lang::string('login-required-error');
			}
			elseif (strstr($error,'-')) {
				$errors[] = Lang::string($error);
			}
			else {
				$errors[] = $error;
			}
		}
	}
		
	Errors::$errors = $errors;
}
elseif (!empty($_REQUEST['register']) && !is_array($register->errors)) {
	API::add('User','registerNew',array($register->info));
	$query = API::send();
	
	$result = $query['User']['registerNew']['results'][0];
	
	if($result == true) {
		$_SESSION["register_uniq"] = md5(uniqid(mt_rand(),true));
		$_SESSION["register_temp_email"] = $register->info['email'];
		Link::redirect($CFG->baseurl.'verify-user.php?lang=kr&message=registered');
	}
	else {
		$register->errors[] = $result;
	}
}

$page_title = Lang::string('home-register');

$_SESSION["register_uniq"] = md5(uniqid(mt_rand(),true));
include 'includes/head.php';
?>
<div class="page_title">
	<div class="container">
		<div class="title"><h1><?= $page_title ?></h1></div>
        <div class="pagenation">&nbsp;<a href="index.php"><?= Lang::string('home') ?></a> <i>/</i> <a href="register.php"><?= Lang::string('register') ?></a></div>
	</div>
</div>
<div class="container">
	<? include 'includes/sidebar_account.php'; ?>
	<div class="content_right">
		<div class="testimonials-4">
			<? 
            Errors::display(); 
            Messages::display();
            ?>
            <div class="content">
            	<h3 class="section_label">
                    <span class="left"><i class="fa fa-user fa-2x"></i></span>
                    <span class="right"><?= Lang::string('settings-registration-info') ?></span>
                </h3>
                <div class="clear"></div>
                <?
              
				$register->textInput('first_name',Lang::string('settings-first-name'),'first_name');
                $register->textInput('last_name',Lang::string('settings-last-name'),'last_name');
                $register->passwordInput('pass',Lang::string('settings-password'), 'password');
                $register->passwordInput('pass2',Lang::string('settings-pass-confirm'),'password',false,false,false,false,false,'pass');
                $register->textInput('email',Lang::string('settings-email'),'email');
                $register->textInput('tel',Lang::string('settings-tel'), 'tel');
                $register->textInput('address',Lang::string('settings-address'), 'address');
                $register->checkBox('terms',Lang::string('settings-terms-accept'),false,false,false,false,false,false,'checkbox_label');
                $register->captcha(Lang::string('settings-capcha'));
                $register->HTML('<div class="form_button"><input type="submit" name="submit" value="'.Lang::string('home-register').'" class="but_user" /></div>');
                $register->hiddenInput('uniq',1,$_SESSION["register_uniq"]);
                $register->display();
                ?>
            	<div class="clear"></div>
            </div>
            <div class="mar_top8"></div>
        </div>
	</div>
</div>
<? include 'includes/foot.php'; ?>