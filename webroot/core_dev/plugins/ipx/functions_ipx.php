<?
	/*
	extension=php_soap.dll (required)
	extension=php_openssl.dll (required for https support)
	*/

	set_time_limit(600);
	ini_set('default_socket_timeout', '600');	//10 minute timeout for SOAP requests

														//level 1=normal user
	define('VIP_LEVEL1',	2);	//Normal VIP
	define('VIP_LEVEL2',	3);	//VIP delux

	$config['sms']['originating_number'] = '123';
	$config['sms']['auth_username'] = '';
	$config['sms']['auth_password'] = '';

	//set $tariff & $reference to charge a previous MT-SMS. requires the originating_number to be configured for MT billing
	function sendSMS($dest_number, $msg, $from_number = '', $tariff = 'SEK0', $reference = '#NULL#')
	{
		//$msg must be a UTF-8 encoded string for swedish characters to work
		global $db, $config, $session;

		//använder det nummret som inkommande sms kom på för att skicka utgående, för mt billing
		//detta nummer ska bara användas för utgående sms (MO)
		if (!$from_number) $from_number = $config['sms']['originating_number'];

		$client = new SoapClient('https://europe.ipx.com/api/services/SmsApi50?wsdl');//, array('trace' => 1));

		try {
			$q = 'INSERT INTO tblSentSMS SET dest="'.$db->escape($dest_number).'",msg="'.$db->escape($msg).'",timeSent=NOW()';
			$corrId = $db->insert($q);

			if (!$corrId) die('FAILED TO INSERT tblSentSMS');

			$params = array(
				//element										value					data type
				'correlationId'					=>	$corrId,			//string	- id som klienten sätter för att hålla reda på requesten, returneras tillsammans med soap-response från IPX
				'originatingAddress'		=>	$config['sms']['originating_number'],	//string	- orginating number for SMS sent by us
				'destinationAddress'		=>	$dest_number,	//string	- mottagare till sms:et, med landskod, format: 46707308763
				'originatorAlpha'				=>	'0',					//bool		- ?
				'userData'							=>	$msg,					//string	- meddelandetexten
				'userDataHeader'				=>	'#NULL#',			//string	- ?
				'dcs'										=>	'-1',					//int			- data coding scheme, how the userData text are encoded
				'pid'										=>	'-1',					//int			- reserved
				'relativeValidityTime'	=>	'-1',					//int			- relative validity time in seconds, from the time of submiussion to IPX
				'deliveryTime'					=>	'#NULL#',			//string	- used for delayed delivery of sms
				'statusReportFlags'			=>	'0',					//int			- 0 = no delivery report, 1 = delivery report requested
				'accountName'						=>	'#NULL#',			//string	- ?
				'blocking'							=>	'1',					//bool		- reserved
				'tariffClass'						=>	$tariff,			//string	- price of the premium message in the format "SEK0"
				'referenceId'						=>	$reference,		//string	- reference order of premium message
				'contentCategory'				=>	'#NULL#',			//string	- reserved
				'username'							=>	$config['sms']['auth_username'],	//string
				'password'							=>	$config['sms']['auth_password']		//string
			);

			$response = $client->send($params);
			
			$q = 'INSERT INTO tblSendResponses SET correlationId='.$corrId.',messageId="'.$db->escape($response->messageId).'",responseCode='.$response->responseCode.',responseMessage="'.$db->escape($response->responseMessage).'",temporaryError='.intval($response->temporaryError).',timeCreated=NOW()';
			$q .= ',params="'.$db->escape(serialize($params)).'"';
			$db->insert($q);

			if ($response->responseCode == 0) return true;
			return $response->responseCode.' ('.$response->responseMessage.')';

		} catch (Exception $e) {
			echo 'Exception: '.$e.'<br/><br/>';
			echo 'Request header: '.htmlspecialchars($client->__getLastRequestHeaders()).'<br/>';
			echo 'Request: '.htmlspecialchars($client->__getLastRequest()).'<br/>';
			echo 'Response: '.htmlspecialchars($client->__getLastResponse()).'<br/>';

			$session->log('Exception in sendSMS(): '.$e, LOGLEVEL_ERROR);
			return false;
		}
	}

	function ipxOutgoingLog()
	{
		global $db;

		$q = 'SELECT * FROM tblSentSMS ORDER BY timeSent DESC';
		$list = $db->getArray($q);

		foreach ($list as $row) {
			echo $row['timeSent'].' to <b>'.$row['dest'].'</b><br/>';
			echo 'Message: '.mb_convert_encoding($row['msg'], 'ISO-8859-1', 'utf8').'<br/><br/>';	//fixme: unicode probs. strängen ska va unicode men visas inte korrekt :(

			$q = 'SELECT * FROM tblSendResponses WHERE correlationId='.$row['correlationId'];
			$response = $db->getOneRow($q);

			echo 'Sent message parameters:<br/>';
			d(unserialize($response['params']));

			echo 'IPX status response: ';
			unset($response['params']);
			d($response);
			echo '<hr/>';
		}
	}

	function ipxIncomingLog()
	{
		global $db, $config;

		$q = 'SELECT * FROM tblIncomingSMS ORDER BY timeReceived DESC';
		$list = $db->getArray($q);

		foreach ($list as $row) {
			$ipv4 = GeoIP_to_IPv4($row['IP']);
			echo $row['timeReceived'].' (local time) incoming data from <a href="'.$config['core_web_root'].'admin/admin_ip.php?ip='.$ipv4.getProjectPath().'">'.$ipv4.'</a><br/>';
			$msg = unserialize($row['params']);

			echo 'SMS from <b>'.$msg['OriginatorAddress'].'</b> operator <b>'.$msg['Operator'].'</b> (to <b>'.$msg['DestinationAddress'].'</b>)<br/>';
			echo 'Message: <b>'.$msg['Message'].'</b> (id <b>'.$msg['MessageId'].'</b>)<br/>';

			$ts = sql_datetime(strtotime($msg['TimeStamp']));
			echo 'Message sent: '.$ts.' (IPX time)<br/>';

			echo 'Incoming message parameters:<br/>';
			d(unserialize($row['params']));

			echo '<hr/>';
		}
	}

	function ipxHandleIncoming()
	{
		global $db, $session, $config;

		//All incoming data is set as GET parameters
		$params = '';
		if (!empty($_GET)) $params = $_GET;
		if (!$params) die('nothing to do');
		
		//Log the incoming SMS
		$q = 'INSERT INTO tblIncomingSMS SET params="'.$db->escape(serialize($params)).'",IP='.$session->ip.',timeReceived=NOW()';
		$db->insert($q);

		//Acknowledgment - Tell IPX that the SMS was received
		header('HTTP/1.1 200 OK');
		header('Content-Type: text/plain');
		echo '<DeliveryResponse ack="true"/>';

		//1. parse sms, format "POG vipnivå userid"
		$in_cmd = explode(' ', strtoupper($params['Message']));

		if (empty($in_cmd[0]) || empty($in_cmd[1]) || empty($in_cmd[2]) || !is_numeric($in_cmd[2])) {
			$session->log('Invalid SMS cmd: '.$params['Message'], LOGLEVEL_WARNING);
			die;
		}
		$vip_codes = array(
											//days, price i öre, SEK2000 = 20.00 kronor
			'VIP'			=> array(14, '20'),
			'VIP-2V'	=> array(14, '20')/*,
			'VIP-1M'	=> array(30, '30'),
			'VIP-6M'	=> array(180, '150')*/
		);

		$vip_delux_codes = array(
			'VIPD'			=> array(10, '20'),
			'VIDP-10D'	=> array(10, '20')/*,
			'VIPD-1M'		=> array(30, '50')*/
		);

		if (!array_key_exists($in_cmd[1], $vip_codes) && !array_key_exists($in_cmd[1], $vip_delux_codes)) {
			$session->log('Unknown incoming SMS code "'.$in_cmd[1].'" ('.$params['Message'].')', LOGLEVEL_WARNING);
			die;
		}

		//identifiera användaren
		$user_db = new DB_MySQLi($config['user_db']);

		$q = 'SELECT u_alias FROM s_user WHERE id_id='.$in_cmd[2];
		$username = $user_db->getOneItem($q);
		if (!$username) {
			$session->log('Specified user dont exist: '.$in_cmd[2], LOGLEVEL_WARNING);
			die;
		}

		if (array_key_exists($in_cmd[1], $vip_codes)) {
			$days = $vip_codes[$in_cmd[1]][0];
			$price = $vip_codes[$in_cmd[1]][1];
			$tariff = 'SEK'.$price.'00';	//de två nollorna är för ören
			$vip_level = VIP_LEVEL1;
			$msg = 'Du debiteras nu '.$price.' kr för '.$days.' dagar VIP till användare '.$username;
			$internal_msg = 'Ditt konto har uppgraderats med '.$days.' dagar VIP';

			$session->log('Attempting to charge '.$username.' for '.$days.' days VIP ('.$tariff.') (cmd: '.$in_cmd[1].')');	

		} else if (array_key_exists($in_cmd[1], $vip_delux_codes)) {
			$days = $vip_delux_codes[$in_cmd[1]][0];
			$price = $vip_delux_codes[$in_cmd[1]][1];
			$tariff = 'SEK'.$price.'00';	//de två nollorna är för ören
			$vip_level = VIP_LEVEL2;
			$msg = 'Du debiteras nu '.$price.' kr för '.$days.' dagar VIP DELUX till användare '.$username;
			$internal_msg = 'Ditt konto har uppgraderats med '.$days.' dagar VIP DELUX';

			$session->log('Attempting to charge '.$username.' for '.$days.' days VIP DELUX ('.$tariff.') (cmd: '.$in_cmd[1].')');	
		}

		//2. skicka ett nytt sms till avsändaren, med TARIFF satt samt med messageid från incoming sms satt som "reference id"
		//	använder samma avsändar-nummer som det inkommande SMS:et skickades till

		//"Testa att sätta referenceID-parametern till messageID:t utan det inledande "1-" delen. Det bör fungera då."
		$referenceId = $params['MessageId'];
		//if (substr($referenceId, 0, 2) == '1-') $referenceId = substr($referenceId, 2);

		$sms_err = sendSMS($params['OriginatorAddress'], $msg, $params['DestinationAddress'], $tariff, $referenceId);
		if ($sms_err === true) {
			addVIP($in_cmd[2], $vip_level, $days);
			
			$session->log('Charge to '.$username.' of '.$tariff.' succeeded');
			
			//Leave a confirmation message in the users inbox
			$internal_title = 'VIP-bekräftelse';
			$q = 'INSERT INTO s_usermail SET sender_id=0, user_id='.$in_cmd[2].',sent_ttl="'.$internal_title.'",sent_cmt="'.$internal_msg.'",sent_date=NOW()';
			$user_db->insert($q);

			return true;
		}

		$session->log('Charge to '.$username.' of '.$tariff.' failed with error '.$sms_err, LOGLEVEL_ERROR);
	}

	function nvoxHandleIncoming($_user_id, $_days, $_level)
	{
		global $db, $session, $config;
		if (!is_numeric($_user_id) || !is_numeric($_days) || !is_numeric($_level)) return false;
		if ($_level > 3) return false;

		//identifiera användaren
		$user_db = new DB_MySQLi($config['user_db']);

		$q = 'SELECT u_alias FROM s_user WHERE id_id='.$_user_id;
		$username = $user_db->getOneItem($q);
		if (!$username) {
			echo '1';	//error code
			$session->log('Specified user dont exist: '.$_user_id, LOGLEVEL_WARNING);
			die;
		}
		
		//Acknowledgment - Tell NVOX that the data was received
		echo '0';	//ok code

		$internal_msg = 'Ditt konto har uppgraderats med '.$_days.' dagar VIP';
		if ($_level == 3) $internal_msg .= ' Deluxe';

		addVIP($_user_id, $_level, $_days);

		if ($_level == 3) {
			$log_msg = 'Gave '.$username.' '.$_days.' days of VIP deluxe from NVOX';
		} else {
			$log_msg = 'Gave '.$username.' '.$_days.' days of VIP from NVOX';
		}
		$session->log($log_msg);
			
		//Leave a confirmation message in the users inbox
		$internal_title = 'VIP-bekräftelse';
		$q = 'INSERT INTO s_usermail SET sender_id=0, user_id='.$_user_id.',sent_ttl="'.$internal_title.'",sent_cmt="'.$internal_msg.'",sent_date=NOW()';
		$user_db->insert($q);

		return true;
	}

//av martin. används häråvar, mot cs databasen
function addVIP($user_id, $vip_level, $days)
{
	global $session, $config;

	$user_db = new DB_MySQLi($config['user_db']);

	//$session->log('addVIP user='.$user_id.', level: '.$vip_level.',days: '.$days );
	
	if (!is_numeric($user_id) || !is_numeric($vip_level) || !is_numeric($days)) return false;

	$q = 'SELECT userId FROM s_vip WHERE userId='.$user_id.' AND level='.$vip_level;

	if ($user_db->getOneItem($q)) {
		$q = 'UPDATE s_vip SET days=days+'.$days.',timeSet=NOW() WHERE userId='.$user_id.' AND level='.$vip_level;
		$user_db->update($q);
	} else {
		$q = 'INSERT INTO s_vip SET userId='.$user_id.',level='.$vip_level.',days='.$days.',timeSet=NOW()';
		$user_db->insert($q);
	}

	$q = 'SELECT level_id FROM s_user WHERE id_id='.$user_id;
	$old_level = $user_db->getOneItem($q);
	//$session->log( 'old: '.$old_level.', new: '.$vip_level );
	if ($old_level >= $vip_level) return true;

	$q = 'UPDATE s_user SET level_id="'.$vip_level.'" WHERE id_id='.$user_id;
	$user_db->update($q);
	//$session->log('updated');
}
?>
