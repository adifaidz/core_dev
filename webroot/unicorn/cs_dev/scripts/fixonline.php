<?
	require_once('../config.php');

	$o = array();
	$o[0] = $db->getOneItem('SELECT COUNT(*) FROM s_user WHERE status_id = "1" AND account_date > "'.$user->timeout(UO).'"');
	$o[1] = $db->getOneItem('SELECT COUNT(*) FROM s_user WHERE status_id = "1" AND u_sex = "M" AND account_date > "'.$user->timeout(UO).'"');
	$o[2] = $db->getOneItem('SELECT COUNT(*) FROM s_user WHERE status_id = "1" AND u_sex = "F" AND account_date > "'.$user->timeout(UO).'"');
	$db->update('UPDATE s_text SET text_cmt = "'.implode(':', $o).'" WHERE main_id = "stat_online" LIMIT 1');
	die;
?> 
