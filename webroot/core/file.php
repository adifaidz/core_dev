<?
	/* file.php - takes a file id, returns the file */

	if (empty($_GET['id']) || !is_numeric($_GET['id'])) die;
	$fileId = $_GET['id'];

	$download = false;
	if (isset($_GET['dl'])) $download = true;

	//fixme: l�s s�kv�gen!!!
	include('../adblock/config.php');

	$files->sendFile($fileId, $download);
?>