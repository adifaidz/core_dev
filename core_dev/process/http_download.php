<?
/**
 * From here you ask the server to fetch a remote media for later processing
 */

	require_once('config.php');
	$session->requireLoggedIn();

	require('design_head.php');

	if (!empty($_POST['url'])) {

		//fixme: en isURL() funktion som kollar om str�ngen �r en valid url
		$eventId = addProcessEvent(PROCESSFETCH, $_POST['url']);

		echo '<div class="okay">URL to process has been enqueued.</div><br/>';
		echo '<a href="http_enqueue.php?id='.$eventId.'">Click here</a> to perform further actions on this file.';
		require('design_foot.php');
		die;
	}

	wiki('ProcessQueueDownload');

	$url = 'http://localhost/video.wmv';
	echo 'Enter resource URL:<br/>';
	echo '<form method="post" action="'.$_SERVER['PHP_SELF'].'">';
	echo '<input type="text" name="url" size="60" id="url" value="'.$url.'"/>';
	echo '<img src="'.$config['core_web_root'].'gfx/arrow_next.png" align="absmiddle" onclick="expand_input(\'url\')"/><br/>';
	echo '<input type="submit" class="button" value="Add"/>';
	echo '</form>';

	require('design_foot.php');
?>