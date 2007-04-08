<?
	require_once('config.php');

	if (!$session->isAdmin) {
		header('Location: '.$config['start_page']);
		die;
	}

	if (isset($_GET['clearlog'])) {
		clearLog($db, LOGLEVEL_ALL);
	}

	require('design_head.php');

	echo getInfoField('page_admin_eventlog');
	
	$list = $db->getLogEntries(LOGLEVEL_ALL);
	echo '<br/>'.count($list).' entries in event log<br/><br/>';

	for ($i=0; $i<count($list); $i++) {
		switch ($list[$i]['entryLevel']) {
			case LOGLEVEL_NOTICE:  echo 'Notice: '; break; 
			case LOGLEVEL_WARNING: echo 'Warning: '; break; 
			case LOGLEVEL_ERROR:   echo 'Error: '; break; 
			default: echo 'Errorx2: '; break;
		}
		
		echo '<b>Entry #'.$list[$i]['entryId'].'</b> - ';
		echo $list[$i]['entryText'].'<br/>';
		echo '<span style="color: #808080;"><i>Generated by <b>';
		if ($list[$i]['userId']) echo $db->getUserName($list[$i]['userId']);
		else echo 'Unregistered';
		echo '</b> at '.$list[$i]['timeCreated'];

		$ip_v4 = GeoIP_to_IPv4($list[$i]['userIP']);
		echo ' from <b><a href="admin_ip.php?ip='.$ip_v4.'">'.$ip_v4.'</a></b>';
		
		echo '</i></span><br/><br/>';
	}

	echo '<a href="'.$_SERVER['PHP_SELF'].'?clearlog">Clear log</a>';

	require('design_foot.php');
?>