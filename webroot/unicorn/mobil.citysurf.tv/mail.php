<?
	require_once('config.php');

	if (!$l) die;	//user not logged in

	require('design_head.php');
?>

	DIN MAIL<br/><br/>
	
	<a href="mail_new.php">SKRIV NYTT MAIL</a><br/>
	<br/>

<?
	$tot_cnt = mailInboxCount();
	$pager = makePager($tot_cnt, 5);

	$list = mailInboxContent($pager['index'], $pager['items_per_page']);

	echo '<div class="mid_content">';
	foreach($list as $row) {
		echo ($row['user_read']?'<img src="gfx/icon_mail_opened.png" alt="L�st" title="L�st" width="16" height="16"/> ':'<img src="gfx/icon_mail_unread.png" alt="Ol�st" title="Ol�st" width="16" height="16"/> ');

		$rubrik = $row['sent_ttl'];
		if (!$rubrik) $rubrik = '(ingen rubrik)';
		if (strlen($rubrik) > 20) $rubrik = substr($rubrik, 0, 18).'...';
		echo '<a href="mail_read.php?id='.$row['main_id'].'">'.$rubrik.'</a><br/>';
		echo 'fr�n '.$user->getstringMobile($row['sender_id']).' ';
		//echo nicedate($row['sent_date']);
		echo '<br/>';
	}
	echo '</div>';

	echo '<br/>'.$pager['head'];
	
	require('design_foot.php');
?>