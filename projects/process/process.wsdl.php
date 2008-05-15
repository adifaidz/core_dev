<?php

$config['no_session'] = true;	//force session "last active" update to be skipped
require_once('config.php');
require_core('class.SOAP.php');

$serv = new SOAP('Process', $config['app']['full_url'].'soap_server.php');
$serv->message('fetchAndConvert',
	array(
	'uri' => 'string', 'callback' => 'string',
	'response' => 'integer')
);
$serv->output();

?>
