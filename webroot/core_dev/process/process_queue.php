<?
	//this script is intended to be called regularry. every 30-60 seconds or so
	set_time_limit(60*10);	//10 minute max, for long video recodings

	require_once('config.php');

	processQueue();

	//include('design_head.php'); $db->showProfile();

?>