<div class="left_sidebar">
	<div class="sidebar_widget">
    	<div class="sidebar_title"><h3><?= Lang::string('home-basic-nav') ?></h3></div>
		<ul class="arrows_list1">
			<li><a href="about.php" <?= ($CFG->self == 'about.php') ? 'class="active"' : '' ?>><i class="fa fa-angle-right"></i> <?= Lang::string('about') ?></a></li>
			<li><a href="how-to-register.php" <?= ($CFG->self == 'how-to-register.php') ? 'class="active"' : '' ?>><i class="fa fa-angle-right"></i> <?= Lang::string('how-to-register') ?></a></li>
			<li><a href="help.php" <?= ($CFG->self == 'help.php') ? 'class="active"' : '' ?>><i class="fa fa-angle-right"></i> <?= Lang::string('help') ?></a></li>
		</ul>
	</div>
</div>