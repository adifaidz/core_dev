<?
	if (empty($_GET['id']) || !is_numeric($_GET['id'])) die;
	$_id = $_GET['id'];

	require_once('config.php');

	if (!$l) die;	//user not logged in
	
	$gb = gbGetById($_id);
	if (!$gb) die;
	
	if ($gb['user_id'] == $l['id_id']) gbMarkAsRead($_id);

	require('design_head.php');
	
	if ($gb['user_id'] == $l['id_id']) echo '<div class="h_gb"></div>';
	else {
		echo $user->getstringMobile($gb['user_id']).'s G�STBOK<br/><br/>';
	}
	
	if ($gb['user_id'] == $l['id_id']) echo ($gb['user_read']?'L�st':'Ol�st').' inl�gg:<br/>';

	echo 'Avs�ndare: '.$user->getstringMobile($gb['sender_id']).'<br/>';
	echo 'Skickat: '.$gb['sent_date'].'<br/><br/>';

	echo '<div class="mid_content">';
	echo $gb['sent_cmt'];
	echo '</div>';

	if ($gb['user_id'] == $l['id_id']) {
		echo '<a href="gb_write.php?id='.$gb['main_id'].'&amp;reply">SVARA</a><br/>';
		echo '<a href="gb_history.php?id='.$gb['sender_id'].'">SE HISTORIK</a><br/>';
	}

	require('design_foot.php');
?>