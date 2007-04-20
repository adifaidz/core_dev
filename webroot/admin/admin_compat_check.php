<?
	/* Returns true if $curr_ver is in the range of $ver_range */
	function version_compare_array($ver_range, $curr_ver)
	{
		list($min_ver, $max_ver) = $ver_range;
		
		//version_compare() returns -1 if the first version is lower than the second, 0 if they are equal, and +1 if the second is lower.
		if (version_compare($min_ver, $curr_ver, "<=") && version_compare($max_ver, $curr_ver, ">=")) {
			return true;
		}


		return false;
	}

	require_once('find_config.php');
	$session->requireAdmin();

	require($project.'design_head.php');

	echo '<h1>Compatiblity check</h1>';

	echo 'core version 0.1<br/>';
	echo 'Debug: '.($db->debug?'<span class="critical">On - turn off for production use</span>':'<span class="okay">OFF</span>').'<br/>';
	echo '<br/>';

	/************************************
	* Apache version checks             *
	************************************/
	$supported_apache = array('2.2.3', '2.2.4');
	$current_apache = apache_get_version();
	if ($current_apache == 'Apache') {
		echo '<span class="okay">Apache version information is turned off</span><br/>';
	} else {
		echo 'Apache web server version: '.$current_apache.' '.(version_compare_array($supported_apache, $current_apache)?'<span class="okay">OK</span>':'<span class="critical">NOT TESTED</span>').'<br/>';
	}


	/************************************
	* PHP version checks                *
	************************************/
	$supported_php = array('5.2.0', '5.2.2');
	$current_php = phpversion();

	$supported_php_gd = array('2.0.34', '2.0.34');
	$current_php_gd = phpversion('gd');	//fixme: returnerar ingenting

	$supported_php_apc = array('3.0.14', '3.0.14');
	$current_php_apc = phpversion('apc');

	echo 'PHP script language version: '.$current_php.' '.(version_compare_array($supported_php, $current_php)?'<span class="okay">OK</span>':'<span class="critical">NOT TESTED</span>').'<br/>';
	echo 'Required PHP extension "gd": '.$current_php_gd.' '.(version_compare_array($supported_php_gd, $current_php_gd)?'<span class="okay">OK</span>':'<span class="critical">NOT TESTED</span>').'<br/>';
	echo 'Optional PHP extension "apc": '.$current_php_apc.' '.(version_compare_array($supported_php_apc, $current_php_apc)?'<span class="okay">OK</span>':'<span class="critical">NOT TESTED</span>').'<br/>';

	//Settings checks
	echo 'display_errors = '. ini_get('display_errors').'<br/>';
	if (!$db->debug && ini_get('display_errors')) echo '<span class="critical">display_errors are turned ON on a production server!</span><br/>';


	/************************************
	* MySQL version checks              *
	************************************/
	if ($db->dialect == 'mysql') {
		$supported_mysql = array('5.0.36', '5.1.17');
		$current_mysql_server = $db->server_version;
		$current_mysql_client = $db->client_version;

		echo 'MySQL database server version: '.$current_mysql_server.' '.(version_compare_array($supported_mysql, $current_mysql_server)?'<span class="okay">OK</span>':'<span class="critical">NOT TESTED</span>').'<br/>';
		echo 'MySQL database client version: '.$current_mysql_client.' '.(version_compare_array($supported_mysql, $current_mysql_client)?'<span class="okay">OK</span>':'<span class="critical">NOT TESTED</span>').'<br/>';
	}

	require($project.'design_foot.php');
?>