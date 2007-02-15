<?
	include ('../site_functions/functions_spider.php');
	include ('../site_functions/functions_google_pagerank.php');
	
	$config['path_functions'] = 'd:/webroot/site_functions/';
	include('../tracker/config.php');

	set_time_limit(60*10);


	//framtida alternativ: skicka en HEAD request till webbservern och kolla "Content-Type" responsen.
	//PDF:		Content-Type: application/pdf
	$config['allowed_extensions'] = array('.html', '.htm', '.asp', '.aspx', '.jsp', '.php', '.php4', '.php5', '.pl');


/*
	echo '<pre>';
	$url = 'http://www.telgeenergi.se/ten/pdf/taxa2006_A4.pdf';
	$data = get_http_contents($url, $errno);
	//$data = file_get_contents('elnat.htm');
	//echo $errno;
	//echo $data;
	$list = extract_filenames($data);
	$urls = generate_absolute_urls($list, $url);
	echo '<pre>'; print_r($urls);
	die;*/


/*************************************
***************************************
 ************************************/

	//Bas-url:en som vi b�rjar spindla ifr�n, normalt sett roten p� webbservern:
	$site['url'] = 'http://www.agentinteractive.se/';
	//$site['url'] = 'http://www.telgeenergi.se/ten/';
	//$site['url'] = 'http://www.ulvhall.se/';
	$site['url_parsed'] = nice_parse_url($site['url']);
	

	$http_request_counter = 0;

	//Ladda ner startsidan fr�n denna URL
	//fixme: g�r hela detta rekursivt p� n�t s�tt, s� �ven detta get_http_contents() f�r samma error handling som n�sta
	$data = get_http_contents($site['url'], $errno);
	if ($errno) {
		echo 'get_http_contents() failed with the error code: '.$errno.'<br>';
		die('KAOS');
	}
	//$data = file_get_contents('www.agentinteractive.se.htm');

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



	echo '<pre>';

	echo "Identified ".count($site['all_urls'])." URL's, through ".$http_request_counter." HTTP requests:\n";

	print_r($site['all_urls']);
	
	
	$list = perform_google_pr_lookups($db, $site['all_urls']);
	print_r($list);

/*
	$text = generate_google_sitemap($site['all_urls']);
	file_put_contents('C:/test-google.xml', $text);

	$text = generate_robots_txt('www.agentinteractive.se', $site['all_urls']);
	file_put_contents('C:/robots.txt.test', $text);
*/

	if (!empty($site['404'])) {
		echo "File not found:\n";
		print_r($site['404']);
	}

	if (!empty($site['302'])) {
		echo "Objects moved:\n";
		print_r($site['302']);
	}

?>