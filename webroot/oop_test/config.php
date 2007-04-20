<?
	$time_start = microtime(true);
	
	error_reporting(E_ALL);
	mb_internal_encoding('UTF-8');
	date_default_timezone_set('Europe/Stockholm');

	$config['core_root'] = '../';
	require_once($config['core_root'].'core/class.DB_MySQLi.php');
	require_once($config['core_root'].'core/class.Session.php');
	require_once($config['core_root'].'core/class.Files.php');

	$config['debug'] = true;

	$config['database']['username']	= 'root';
	$config['database']['password']	= '';
	$config['database']['database']	= 'dbOOPHP';
	$db = new DB_MySQLi($config['database']);

	$config['session']['timeout'] = (60*60)*12;
	$config['session']['name'] = 'OOPtest';
	$config['session']['sha1_key'] = 'sitecode_uReply';		//todo: byt ut sitecode
	$session = new Session($config['session']);
	
	$config['files']['upload_dir'] = 'E:/Devel/webupload_ooptest/';
	$config['files']['thumbs_dir'] = 'E:/Devel/webupload_ooptest/thumbs/';
	$files = new Files($config['files']);
?>