<?
/**
 * ajax_rategadget.php - for rating
 *
 * $_GET['i'] object id
 * $_GET['t'] rating type (RATE_FILE, RATE_BLOG, RATE_NEWS)
 */

	require_once('find_config.php');

	if (!$session->id || empty($_GET['i']) || !is_numeric($_GET['i']) || empty($_GET['t']) || !is_numeric($_GET['t'])) die('bad');

	echo ratingGadget($_GET['t'], $_GET['i']);
?>