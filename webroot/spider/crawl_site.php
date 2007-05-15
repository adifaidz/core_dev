<?
	require_once('config.php');
	require('design_head.php');

	/*
		Crawls a site from a base url, extracting all url's found on pages linked from the first page

		Dumps all found url's to a file, for later processing
	*/

	set_time_limit(60*3);


	//Bas-url:en som vi b�rjar spindla ifr�n, normalt sett roten p� webbservern:
	//$site['url'] = 'http://localhost/adblock/';
	//$site['url'] = 'http://www.unicorn.tv/';
	$site['url'] = 'http://www.dn.se/';
	$site['url_parsed'] = nice_parse_url($site['url']);
	

	$http_request_counter = 0;

	//Ladda ner startsidan fr�n denna URL
	//fixme: g�r hela detta rekursivt p� n�t s�tt, s� �ven detta get_http_contents() f�r samma error handling som n�sta
	$data = get_http_contents($site['url'], $errno);
	if ($errno) die('get_http_contents() failed');

	$list = extract_filenames($data);
	
	$site['page'][$site['url']] = generate_absolute_urls($list, $site['url']);

	$site['all_urls'] = $site['page'][$site['url']];
	//echo '<pre>'; print_r($site['all_urls']); die;

	$loop_cnt = 0;
	echo 'Started digging in '.$site['url'].', '.count($site['all_urls']).' pages discovered<br>';
	do {
		$loop_cnt++;
		echo '<hr>';
		echo '<b>Loop '.$loop_cnt.' started. I know '.count($site['all_urls']).' URLs</b><br>';
		
		$pages_processed = 0;
		$pages_discovered = count($site['all_urls']);

		foreach ($site['all_urls'] as $val)
		{
			if (isset($site['page'][$val]) || isset($site['404'][$val])) continue;

			//Look up this page too
			$data = get_http_contents($val, $errno);
			if ($errno) {
				echo '<b>FATAL! Unhandled get_http_contents() error occured: '.$errno.'</b>, requested '.$val.'<br>';
			}
			$list = extract_filenames($data);
			$site['page'][$val] = generate_absolute_urls($list, $val);
			$site['all_urls'] = array_merge($site['all_urls'], $site['page'][$val]);
			$pages_processed++;
		}
		$site['all_urls'] = array_unique($site['all_urls']);

		//Loop through 'all_urls' to see if we discovered any new pages
		echo '<b>Loop '.$loop_cnt.' finished, '.$pages_processed.' pages were processed, '.(count($site['all_urls'])-$pages_discovered).' new pages were discovered</b><br>';
	} while ($pages_processed != 0);

	//Then finally we clean up the array
	
	//todo: beh�vs array_unique() & array_merge() ens anropas h�r? koden ska v�l se till att inte dupes hamnar i arrayen???
	$site['all_urls'] = array_unique($site['all_urls']);
	natsort($site['all_urls']);
	$site['all_urls'] = array_merge($site['all_urls']);


	echo "Identified ".count($site['all_urls'])." URL's, through ".$http_request_counter." HTTP requests:\n";
	d($site['all_urls']);

	file_put_contents('dump.txt', serialize($site['all_urls']) );
	
	if (!empty($site['404'])) {
		echo 'File not found:<br>';
		d($site['404']);
	}

	if (!empty($site['403'])) {
		echo 'Forbidden:<br>';
		d($site['403']);
	}

	if (!empty($site['302'])) {
		echo 'Objects moved:<br>';
		d($site['302']);
	}

	require('design_foot.php');
?>