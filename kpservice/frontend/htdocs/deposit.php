<?php
include '../lib/common.php';

if (User::$info['locked'] == 'Y' || User::$info['deactivated'] == 'Y')
	Link::redirect('settings.php');
elseif (User::$awaiting_token)
	Link::redirect('verify-token.php');
elseif (!User::isLoggedIn())
	Link::redirect('login.php');

$page1 = (!empty($_REQUEST['page'])) ? preg_replace("/[^0-9]/", "",$_REQUEST['page']) : false;

$krw = (!empty($_REQUEST['deposit']['krw'])) ? preg_replace("/[^0-9]/", "",$_REQUEST['deposit']['krw']) : false;

API::add('BankAccounts','get');
API::add('Content','getRecord',array('deposit-bank-instructions'));
API::add('Content','getRecord',array('deposit-no-bank'));
$query = API::send();

$bank_accounts = $query['BankAccounts']['get']['results'][0];
$key = (is_array($bank_accounts)) ? key($bank_accounts) : false;
$bank_account = $bank_accounts[$key];
$bank_instructions = ($bank_account) ? $query['Content']['getRecord']['results'][0] : $query['Content']['getRecord']['results'][1];
$bank_account_currency = $CFG->currencies[$bank_account['currency']];
$pagination = $pagination = Content::pagination('deposit.php',$page1,$total,15,5,false);

$page_title = Lang::string('deposit');

if($krw != false) {
	API::add('User','deposit',array($_SESSION['session_id'], $krw));
	$query = API::send();
	$result = $query['User']['deposit']['results'][0];
	if($result == true) {
		Link::redirect('account.php');
	}
	else {
		Errors::add(Lang::string('deposit-no'));
	}
}

if(!empty($_REQUEST['deposit']['krw']) && $krw == false) {
	Errors::add(Lang::string('deposit-no'));
}

$deposit = new Form('deposit',false,false,'form1','deposit');

if (empty($_REQUEST['bypass'])) {
	include 'includes/head.php';
?>
<div class="page_title">
	<div class="container">
		<div class="title"><h1><?= $page_title ?></h1></div>
        <div class="pagenation">&nbsp;<a href="index.php"><?= Lang::string('home') ?></a> <i>/</i> <a href="account.php"><?= Lang::string('account') ?></a> <i>/</i> <a href="deposit.php"><?= $page_title ?></a></div>
	</div>
</div>
<div class="container">
	<? include 'includes/sidebar_account.php'; ?>
	<div class="content_right">
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
		<div class="testimonials-4">
			<div class="one_half">
				<div class="content">
					<h3 class="section_label">
						<span class="left"><i class="fa fa-krw fa-2x"></i></span>
						<span class="right"><?= Lang::string('deposit-krw') ?></span>
					</h3>
					<div class="clear"></div>
					<div class="buyform">
						<?
		                $deposit->textInput('krw','','krw');
		                $deposit->HTML('<div class="form_button"><input type="submit" name="submit" value="'.Lang::string('settings-save-info').'" class="but_user" /></div><input type="hidden" name="submitted" value="1" />');
		                $deposit->hiddenInput('uniq',1,$_SESSION["settings_uniq"]);
		                $deposit->display();
		                ?>
            	<div class="clear"></div>
					</div>
				</div>
			</div>
		</div>
		<div class="mar_top3"></div>
		<div class="clear"></div>
		</div>
		<div class="mar_top5"></div>
	</div>
</div>
<? include 'includes/foot.php'; ?>
<? } ?>