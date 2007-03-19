<?
	$time_start = microtime(true);
	
	error_reporting(E_ALL);
	mb_internal_encoding('UTF-8');
	date_default_timezone_set('Europe/Stockholm');

/*
	//autoload seems to slow down page load ALOT (PHP 5.2.1, Apache 2.2.4 WinXP)
	function __autoload($class_name) {
  	require_once('../functions/class.'.$class_name.'.php');
	}
*/
//	require_once('../functions/class.DB_MySQL.php');
	require_once('../functions/class.DB_MySQLi.php');
	require_once('../functions/class.Session.php');
	require_once('../functions/class.Files.php');

	$config['database']['username']	= 'root';
	$config['database']['password']	= '';
	$config['database']['database']	= 'dbJanina';
	$config['database']['debug']		= true;

	/* A variable named $db must exist for all future functions to work. */
	$db = new DB_MySQLi($config['database']);

	$config['session']['timeout'] = 30*60;
	$config['session']['name'] = 'Janina';
	$config['session']['sha1_key'] = 'janinaSHAxyxtybhge3bbexudud81cujnm11wbvwcvvw';
	$config['session']['allow_registration'] = false;

	/* A variable named $session must exist for all future functions to work. */
	$session = new Session($config['session']);
	
	$config['files']['upload_dir'] = 'E:/Devel/webupload_janina/';
	$config['files']['thumbs_dir'] = 'E:/Devel/webupload_janina/thumbs/';
	$files = new Files($config['files']);
?>