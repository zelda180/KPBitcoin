<?php
include '../lib/common.php';

if (empty($_REQUEST['currency']) && empty($_SESSION['currency']) && !empty(User::$info['default_currency_abbr']))
	$_SESSION['currency'] = User::$info['default_currency_abbr'];
elseif (empty($_REQUEST['currency']) && empty($_SESSION['currency']) && empty(User::$info['default_currency_abbr']))
	$_SESSION['currency'] = 'usd';
elseif (!empty($_REQUEST['currency']))
	$_SESSION['currency'] = preg_replace("/[^a-z]/", "",$_REQUEST['currency']);

$page_title = Lang::string('home-title');
$currency1 = (!empty($CFG->currencies[strtoupper($_SESSION['currency'])])) ? strtolower($_SESSION['currency']) : 'usd';
$currency_symbol = strtoupper($currency1);
$usd_field = 'usd_ask';
$currency_info = $CFG->currencies[strtoupper($currency1)];
$currency_majors = array('USD','EUR','CNY','RUB','CHF','JPY','GBP','CAD','AUD', 'KRW');
$c_majors = count($currency_majors);
$currencies = $CFG->currencies;

$currencies1 = array();
foreach ($currency_majors as $currency) {
	$currencies1[$currency] = $currencies[$currency];
	unset($currencies[$currency]);
}
$currencies = array_merge($currencies1,$currencies);

if (!User::isLoggedIn()) {
	API::add('Content','getRecord',array('home'));
}

API::add('Stats','getCurrent',array($currency_info['id']));
API::add('Transactions','get',array(false,false,5,$currency1));
API::add('Orders','get',array(false,false,5,$currency1,false,false,1));
API::add('Orders','get',array(false,false,5,$currency1,false,false,false,false,1));
API::add('News','get',array(false,false,3));
$query = API::send();

if (!User::isLoggedIn())
	$content = $query['Content']['getRecord']['results'][0];

$stats = $query['Stats']['getCurrent']['results'][0];
$transactions = $query['Transactions']['get']['results'][0];
$bids = $query['Orders']['get']['results'][0];
$asks = $query['Orders']['get']['results'][1];
$news = $query['News']['get']['results'][0];

if ($stats['daily_change'] > 0)
	$arrow = '<i id="up_or_down" class="fa fa-caret-up price-green"></i> ';
elseif ($stats['daily_change'] < 0)
$arrow = '<i id="up_or_down" class="fa fa-caret-down price-red"></i> ';
else
	$arrow = '<i id="up_or_down" class="fa fa-minus"></i> ';

if ($query['Transactions']['get']['results'][0][0]['maker_type'] == 'sell') {
	$arrow1 = '<i id="up_or_down1" class="fa fa-caret-up price-green"></i> ';
	$p_color = 'price-green';
}
elseif ($query['Transactions']['get']['results'][0][0]['maker_type'] == 'buy') {
	$arrow1 = '<i id="up_or_down1" class="fa fa-caret-down price-red"></i> ';
	$p_color = 'price-red';
}
else {
	$arrow1 = '<i id="up_or_down1" class="fa fa-minus"></i> ';
	$p_color = '';
}

include 'includes/head.php';

if (!User::isLoggedIn()) {
?>

<div class="container_full">
	<?php 
	if ($CFG->language == 'en' || $CFG->language == 'es' || empty($CFG->language))
		$wordwrap = 80;
	elseif ($CFG->language == 'ru')
		$wordwrap = 150;
	elseif ($CFG->language == 'zh')
		$wordwrap = 150;
	elseif ($CFG->language == 'kr')
		$wordwrap = 150;
	?>
</div>

<div class="clearfix"></div>
<? } ?>

<div class="fresh_projects global_stats">
	<a name="global_stats"></a>
	<div class="clearfix mar_top6"></div>
	<div class="container">
		<? if (!User::isLoggedIn()) { ?>
    	<h2><?= Lang::string('home-service-welcome') ?></h2>
        <p class="explain"><?= Lang::string('home-service-explain') ?></p>
        <? } else { ?>
        <h2><?= Lang::string('home-overview') ?></h2>
        <? } ?>
        
         <div class="panel panel-default">
        	<div class="panel-heading non-mobile">
        	<img alt="1" src="images/trading-floor.jpg">
        	</div>
        </div>
    </div>
</div>


<? include 'includes/foot.php'; ?>
