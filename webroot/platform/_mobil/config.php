<?
	session_start();

	require_once('../_config/main.fnc.php');		//l()

	require_once('../_administrator/set_onl.php');		//skapar $sql och $user klasser
	require_once('../_modules/member/auth.php');			//skapar $user_auth klassen f�r logins

	//funktioner
	require_once('../_modules/user/mail.fnc.php');			//funktioner f�r att skicka mail
	require_once('../_modules/user/relations.fnc.php');	//funktioner f�r att hantera relationer
	require_once('../_modules/user/gb.fnc.php');				//funktioner f�r att hantera g�stb�cker
	
	require_once('../_modules/list/search_users.fnc.php');	//funktioner f�r att s�ka anv�ndare
	
	require_once('../_modules/member/settings.fnc.php');	//funktioner f�r anv�ndar-inst�llningar
	
	require_once('functions_general.php');	//f�r min makePager()
	
	//$user->auth() uppdaterar "last online time" i databasen
	$s = &$_SESSION['data'];
	$l = $user->auth(@$_SESSION['data']['id_id'], true);
	
	$isAdmin = (@$_SESSION['data']['level_id'] == '10'?true:false);
	$isOk = true;
?>