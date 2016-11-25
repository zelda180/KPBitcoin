<?php
include '../lib/common.php';

if (User::$info['locked'] == 'Y' || User::$info['deactivated'] == 'Y')
	Link::redirect('settings.php');
elseif (User::$awaiting_token)
	Link::redirect('verify-token.php');
elseif (!User::isLoggedIn())
	Link::redirect('login.php');

if ($_SERVER['HTTP_REFERER'] == 'first_login.php') {
	API::add('User','disableNeverLoggedIn');
	API::send();
}

API::add('User','getAvailable');
API::add('User','getVolume');
$query = API::send();

$currencies = $CFG->currencies;
$on_hold = $query['User']['getOnHold']['results'][0];
$available = $query['User']['getAvailable']['results'][0];

$referer = substr($_SERVER['HTTP_REFERER'],strrpos($_SERVER['HTTP_REFERER'],'/')+1);
if ($referer == 'login.php' || $referer == 'verify-token.php' || $referer == 'first-login.php') {
	if (!empty(User::$info['default_currency_abbr']))
		$_SESSION['currency'] = strtolower(User::$info['default_currency_abbr']);
	
	API::add('User','notifyLogin');
	$query = API::send();
}

if (!empty($_REQUEST['message'])) {
	if ($_REQUEST['message'] == 'settings-personal-message')
		Messages::add(Lang::string('settings-personal-message'));
}

$page_title = Lang::string('account');
include 'includes/head.php';
?>
<div class="page_title">
	<div class="container">
		<div class="title"><h1><?= $page_title ?></h1></div>
        <div class="pagenation">&nbsp;<a href="index.php"><?= Lang::string('home') ?></a> <i>/</i> <a href="account.php"><?= $page_title ?></a></div>
	</div>
</div>
<div class="container">
	<? include 'includes/sidebar_account.php'; ?>
	<div class="content_right">
		<div class="testimonials-4">
			<? Messages::display(); ?>
			<div class="mar_top2"></div>
			<ul class="list_empty">
				<li><a href="deposit.php" class="but_user"><i class="fa fa-download fa-lg"></i> <?= Lang::string('deposit') ?></a></li>
				<li><a href="send-krw.php" class="but_user"><i class="fa fa-krw fa-lg"></i> <?= Lang::string('send') ?></a></li>
				<li><a href="withdraw.php" class="but_user"><i class="fa fa-upload fa-lg"></i> <?= Lang::string('withdraw') ?></a></li>
			</ul>
			<div class="clear"></div>
            <div class="content">
            	<h3 class="section_label">
                    <span class="left"><i class="fa fa-check fa-2x"></i></span>
                    <span class="right"><?= Lang::string('account-balance') ?></span>
                </h3>
                <div class="clear"></div>
                <div class="balances">
	            	<?
	            	$i = 2;
	            	foreach ($available as $currency => $balance) {
						if ($currency == 'BTC')
							continue;
						
						$last_class = ($i % 2 == 0) ? 'last' : '';
					?>
					<div class="one_half <?= $last_class ?>">
                		<div class="label"><?= $currency.' '.Lang::string('account-available') ?>:</div>
                		<div class="amount"><?= $CFG->currencies[$currency]['fa_symbol'].number_format($balance,2) ?></div>
                	</div>
					<?
						$i++;
					} 
	            	?>
	            	<div class="clear"></div>
            	</div>
            	<div class="clear"></div>
            </div>
            <div class="mar_top8"></div>
        </div>
	</div>
</div>
<? include 'includes/foot.php'; ?>

