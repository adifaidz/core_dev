<?
	/*
	
	This script is called by the user, it needs two parameters.
	The userId and the remotePassword generated by get_login_code.php
	
	*/

	include('include_all.php');

	if (empty($_GET['guid'])) die;
	$remoteGUID = dbAddSlashes($db, $_GET['guid']);

	if (empty($_GET['r']) || !is_numeric($_GET['r'])) die;
	$randomPassword = $_GET['r'];
	if (!is_numeric($randomPassword)) die;

	$sql = 'SELECT userId FROM tblRemoteUsers WHERE remoteUserGUID="'.$remoteGUID.'" AND randomPassword='.$randomPassword;
	$localUserId = dbOneResultItem($db, $sql);	
	if (!$localUserId) {
		echo 'error1 - guid or password is wrong!';
		die;
	}

	$sql = 'UPDATE tblRemoteUsers SET randomPassword=0 WHERE userId='.$localUserId;
	dbQuery($db, $sql);

	$check = loginUser($db, $localUserId, '', true);
	if (!$check) {
		echo 'error2 - login failed';
		die;
	}

	header('Location: '.$config['start_page']);
?>