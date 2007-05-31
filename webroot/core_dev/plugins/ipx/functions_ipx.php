<?
	/*
	extension=php_soap.dll (required)
	extension=php_openssl.dll (required for https support)
	*/

	set_time_limit(600);
	ini_set('default_socket_timeout', '600');	//10 minute timeout for SOAP requests

	$config['sms']['originating_number'] = '71160';
	$config['sms']['auth_username'] = 'lwcg';
	$config['sms']['auth_password'] = '3koA4enpE';

	//set $tariff & $reference to charge a previous MT-SMS. requires the originating_number to be configured for MT billing
	function sendSMS($dest_number, $msg, $tariff = 'SEK0', $reference = '#NULL#')
	{
		//$msg must be a UTF-8 encoded string for swedish characters to work
		global $db, $config, $session;

		$client = new SoapClient('https://europe.ipx.com/api/services/SmsApi50?wsdl');//, array('trace' => 1));

		try {
			$q = 'INSERT INTO tblSentSMS SET dest="'.$db->escape($dest_number).'",msg="'.$db->escape($msg).'",timeSent=NOW()';
			$corrId = $db->insert($q);
			
			if (!$corrId) die('FAILED TO INSERT tblSentSMS');

			$params = array(
				//element										value					data type
				'correlationId'					=>	$corrId,			//string	- id som klienten s�tter f�r att h�lla reda p� requesten, returneras tillsammans med soap-response fr�n IPX
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
		}
	}

	function ipxOutgoingLog()
	{
		global $db;

		$q = 'SELECT * FROM tblSentSMS ORDER BY timeSent DESC';
		$list = $db->getArray($q);

		foreach ($list as $row) {
			echo $row['timeSent'].' to '.$row['dest'].'<br/>';
			echo 'Message: '.$row['msg'].'<br/>';

			$q = 'SELECT * FROM tblSendResponses WHERE correlationId='.$row['correlationId'];
			$response = $db->getOneRow($q);

			echo 'IPX status response: ';
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

			echo 'SMS from '.$msg['OriginatorAddress'].' operator '.$msg['Operator'].' (to '.$msg['DestinationAddress'].')<br/>';
			echo 'Message: '.$msg['Message'].' (id '.$msg['MessageId'].')<br/>';

			$ts = sql_datetime(strtotime($msg['TimeStamp']));
			echo 'Message sent: '.$ts.' (IPX time)<br/>';
			echo '<hr/>';
		}
	}

	function ipxHandleIncoming()
	{
		global $db, $session;

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

		//1. parse sms, format "POG vipniv� userid"
		$in_cmd = explode(' ', strtoupper($params['Message']));

		if (empty($in_cmd[0]) || empty($in_cmd[1]) || empty($in_cmd[2]) || !is_numeric($in_cmd[2])) {
			$session->log('Invalid SMS cmd: '.$params['Message'], LOGLEVEL_WARNING);
			die;
		}
		$vip_codes = array(
											//days, price i �re, SEK2000 = 20.00 kronor
			'VIP'			=> array(14, 'SEK2000'),
			'VIP-2V'	=> array(14, 'SEK2000')/*,
			'VIP-1M'	=> array(30, 'SEK3000'),
			'VIP-6M'	=> array(180, 'SEK15000')*/
		);

		$vip_delux_codes = array(
			'VIPD'			=> array(10, 'SEK2000'),
			'VIDP-10D'	=> array(10, 'SEK2000')/*,
			'VIPD-1M'		=> array(30, 'SEK5000')*/
		);

		if (!array_key_exists($in_cmd[1], $vip_codes) && !array_key_exists($in_cmd[1], $vip_delux_codes)) {
			$session->log('Unknown incoming SMS code "'.$in_cmd[1].'" ('.$params['Message'].')', LOGLEVEL_WARNING);
			die;
		}

		//identifiera anv�ndaren
		$config['user_db']['host']	= 'localhost';
		$config['user_db']['username']	= 'root';
		$config['user_db']['password']	= 'dravelsql';
		$config['user_db']['database']	= 'platform';
		$user_db = new DB_MySQLi($config['user_db']);

		$q = 'SELECT u_alias FROM s_user WHERE id_id='.$in_cmd[2];
		$username = $user_db->getOneItem($q);
		if (!$username) {
			$session->log('Specified user dont exist: '.$in_cmd[2], LOGLEVEL_WARNING);
			die;
		}

		if (array_key_exists($in_cmd[1], $vip_codes)) {
			$days = $vip_codes[$in_cmd[1]][0];
			$tariff = $vip_codes[$in_cmd[1]][1];
			$vip_level = VIP_LEVEL1;
			$msg = 'Du debiteras nu '.$tariff.' f�r '.$days.' dagar VIP till anv�ndare '.$username;
			$internal_msg = 'Ditt konto har uppgraderats med '.$days.' dagar VIP';

			$session->log('Attempting to charge '.$username.' for '.$days.' days VIP ('.$tariff.') (cmd: '.$in_cmd[1].')');	

		} else if (array_key_exists($in_cmd[1], $vip_delux_codes)) {
			$days = $vip_delux_codes[$in_cmd[1]][0];
			$tariff = $vip_delux_codes[$in_cmd[1]][1];
			$vip_level = VIP_LEVEL2;
			$msg = 'Du debiteras nu '.$tariff.' f�r '.$days.' dagar VIP DELUX till anv�ndare '.$username;
			$internal_msg = 'Ditt konto har uppgraderats med '.$days.' dagar VIP DELUX';

			$session->log('Attempting to charge '.$username.' for '.$days.' days VIP DELUX ('.$tariff.') (cmd: '.$in_cmd[1].')');	

		} else {
			$session->log('SMS - impossible codepath!!', LOGLEVEL_ERROR);
			die;
		}

		//2. skicka ett nytt sms till avs�ndaren, med TARIFF satt samt med messageid fr�n incoming sms satt som "reference id"
		$sms_err = sendSMS($params['OriginatorAddress'], $msg, $tariff, $params['MessageId']);
		if ($sms_err === true) {
			addVIP($in_cmd[2], $vip_level, $days);
			$session->log('Charge to '.$username.' of '.$tariff.' succeeded');
			
			//Leave a confirmation message in the users inbox
			$internal_title = 'VIP-bekr�ftelse';
			$q = 'INSERT INTO s_usermail SET sender_id=0, user_id='.$in_cmd[2].',sent_ttl="'.$internal_title.'",sent_cmt="'.$internal_msg.'",sent_date=NOW()';
			$user_db->insert($q);

			return true;
		}

		$session->log('Charge to '.$username.' of '.$tariff.' failed with error '.$sms_err, LOGLEVEL_ERROR);
	}

?>