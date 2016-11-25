<?php
include '../lib/common.php';

if (User::$info['locked'] == 'Y' || User::$info['deactivated'] == 'Y')
	Link::redirect('settings.php');
elseif (User::$awaiting_token)
	Link::redirect('verify-token.php');
elseif (!User::isLoggedIn())
	Link::redirect('login.php');

$page1 = (!empty($_REQUEST['page'])) ? preg_replace("/[^0-9]/", "",$_REQUEST['page']) : false;
$address1 = (!empty($_REQUEST['btc_address'])) ? preg_replace("/[^0-9a-zA-Z@\.\!#\$%\&\*+_\~\?\-]/", "",$_REQUEST['btc_address']) : false; 
$amount1 = (!empty($_REQUEST['btc_amount']) && $_REQUEST['btc_amount'] > 0) ? preg_replace("/[^0-9.]/", "",$_REQUEST['btc_amount']) : 0;

API::add('BankAccounts','get');
if ($account1 > 0) {
	API::add('BankAccounts','getRecord',array($account1));
}
$query = API::send();

$bank_accounts = $query['BankAccounts']['get']['results'][0];
if ($account1 > 0) {
	$bank_account = $query['BankAccounts']['getRecord']['results'][0];
}
elseif ($bank_accounts) {
	$key = key($bank_accounts);
	$bank_account = $bank_accounts[$key];	
}

API::add('User','getAvailable');
if ($bank_account) {
	if (is_numeric($bank_account['currency'])) {
		API::add('Currencies','getRecord',array(false,$bank_account['currency']));
		API::add('Currencies','getRecord',array(false,$bank_account['currency']));
	}
	else {
		API::add('Currencies','getRecord',array($bank_account['currency']));
		API::add('Currencies','getRecord',array($bank_account['currency']));
	}
	$query = API::send();
	
	$currency_info = $query['Currencies']['getRecord']['results'][0];
	$currency1 = $currency_info['currency'];
	$bank_account_currency = $query['Currencies']['getRecord']['results'][1];
}
else {
	API::add('Content','getRecord',array('deposit-no-bank'));
	$query = API::send();
	$bank_instructions = $query['Content']['getRecord']['results'][0];
}
$user_available = $query['User']['getAvailable']['results'][0];
$pagination = Content::pagination('send-krw.php',$page1,$total,15,5,false);

if (!empty($_REQUEST['message'])) {
	if ($_REQUEST['message'] == 'withdraw-2fa-success')
		Messages::add(Lang::string('withdraw-2fa-success'));
	elseif ($_REQUEST['message'] == 'withdraw-success')
		Messages::add(Lang::string('withdraw-success'));
}

if (!empty($_REQUEST['notice']) && $_REQUEST['notice'] == 'email')
	$notice = Lang::string('withdraw-email-notice');

if (!empty($_REQUEST['bitcoins'])) {

	/*if (!($amount1 > 0))
		Errors::add(Lang::string('withdraw-amount-zero'));
	if ($amount1 > $user_available['krw'])
		Errors::add(Lang::string('withdraw-too-much'));*/
	
	if (!is_array(Errors::$errors)) {
		API::add('User','sendKRW',array($_SESSION['session_id'], $address1, $amount1));
		$query = API::send();
		$result = $query['User']['sendKRW']['results'][0];
		//if($result == true) {
			//Link::redirect('account.php');
			Errors::add(json_encode($result));
		/*}
		else {
			Errors::add(Lang::string('deposit-no'));
		}*/
	}
}

$page_title = Lang::string('withdraw');

if (empty($_REQUEST['bypass'])) {
	include 'includes/head.php';
?>
<div class="page_title">
	<div class="container">
		<div class="title"><h1><?= $page_title ?></h1></div>
        <div class="pagenation">&nbsp;<a href="index.php"><?= Lang::string('home') ?></a> <i>/</i> <a href="account.php"><?= Lang::string('account') ?></a> <i>/</i> <a href="send-krw.php"><?= $page_title ?></a></div>
	</div>
</div>
<div class="container">
	<? include 'includes/sidebar_account.php'; ?>
	<div class="content_right">
		<? Errors::display(); ?>
		<? Messages::display(); ?>
		<?= (!empty($notice)) ? '<div class="notice"><div class="message-box-wrap">'.$notice.'</div></div>' : '' ?>
		<div class="testimonials-4">
			<div class="one_half">
				<div class="content">
					<h3 class="section_label">
						<span class="left"><i class="fa fa-krw fa-2x"></i></span>
						<span class="right"><?= Lang::string('withdraw-krw') ?></span>
					</h3>
					<div class="clear"></div>
					<form id="buy_form" action="send-krw.php" method="POST">
						<div class="buyform">
							<div class="spacer"></div>
							<div class="param">
								<label for="btc_address"><?= Lang::string('withdraw-send-to-address') ?></label>
								<input type="text" id="btc_address" name="btc_address" value="<?= $btc_address1 ?>" />
								<div class="clear"></div>
							</div>
							<div class="param">
								<label for="btc_amount"><?= Lang::string('withdraw-send-amount') ?></label>
								<input type="text" id="btc_amount" name="btc_amount" value="<?= number_format($btc_amount1,8) ?>" />
								<div class="qualify">KRW</div>
								<div class="clear"></div>
							</div>
							<div class="spacer"></div>
							<div class="calc">
								<div class="label"><?= Lang::string('withdraw-network-fee') ?> <a title="<?= Lang::string('withdraw-network-fee-explain') ?>" href="javascript:return false;"><i class="fa fa-question-circle"></i></a></div>
								<div class="value"><span id="withdraw_btc_network_fee"><?= $CFG->bitcoin_sending_fee ?></span> KRW</div>
								<div class="clear"></div>
							</div>
							<div class="calc bigger">
								<div class="label">
									<span id="withdraw_btc_total_label"><?= Lang::string('withdraw-total') ?></span>
								</div>
								<div class="value"><span id="withdraw_btc_total"><?= number_format($btc_total1,8) ?></span></div>
								<div class="clear"></div>
							</div>
							<div class="spacer"></div>
							<div class="spacer"></div>
							<div class="spacer"></div>
							<input type="hidden" name="bitcoins" value="1" />
							<input type="submit" name="submit" value="<?= Lang::string('withdraw') ?>" class="but_user" />
						</div>
					</form>
					<div class="clear"></div>
				</div>
			</div>
			<div class="one_half last">
				
			</div>
		</div>
	</div>
</div>
<? include 'includes/foot.php'; ?>
<? } ?>