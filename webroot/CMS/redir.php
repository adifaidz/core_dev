<?
	//redir.php - redirect url click counter

	$url = '';
	if (!empty($_GET['url'])) $url = $_GET['url'];
	
	if ($url) {
		//todo: r�kna klicken
		header('Location: '.$url);
	}

	die;
?>