<?
	require_once('config.php');
	if (!$l) die;	//user not logged in

	require('design_head.php');

	echo 'INST�LLNINGAR<br/><br/>';

	//echo '<a href="user_change_image.php">�NDRA BILD</a><br/>';
	echo '<a href="user_change_password.php">�NDRA L�SENORD</a><br/>';
	echo '<a href="user_change_facts.php">�NDRA FAKTA</a><br/>';
	//echo '<a href="user_change_mms_code.php">�NDRA MMS-KOD</a><br/>';

	require('design_foot.php');
?>