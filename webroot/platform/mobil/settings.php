<?
	require_once('config.php');
	if (!$l) die;	//user not logged in

	require('design_head.php');
?>
	INST�LLNINGAR<br/>
	<br/>
	<a href="user_change_image.php">�NDRA BILD</a><br/>
	<a href="user_change_password.php">�NDRA L�SENORD</a><br/>
	<a href="user_change_facts.php">�NDRA FAKTA</a><br/>

	<a href="user_change_mms_code.php">�NDRA MMS-KOD</a><br/>
<?
	//todo: g�r mej till admin
	if ($isAdmin) {
		echo 'xx';
	}

	require('design_foot.php');
?>