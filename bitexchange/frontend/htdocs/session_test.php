<?php
	session_start();
	$faultd=$_POST['faultdistribution'];
	$faultdes=$_POST['faultdescription'];
	$faultsev=$_POST['faultseverity'];
	$faultt=$_POST['faulttype'];
	$faultn=$_POST['faultcmnt'];
	$rel=$_SESSION['releasen'];
	echo $rel;
	echo "Data Added sucessfully";
	$_SESSION['releasen'] = md5(uniqid(mt_rand(),true));
	echo $_SESSION['releasen'];
?>
