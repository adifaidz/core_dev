<?
	if (empty($_GET['id']) || !is_numeric($_GET['id'])) die;
	$_id = $_GET['id'];

	require('config.php');
	
	$gb = gbGetById($_id);
	if (!$gb) die;
	
	gbMarkAsRead($_id);

	require('design_head.php');
	
	//print_r($gb);
	
	echo 'DIN G�STBOK<br/><br/>';
	
	echo ($gb['user_read']?'L�st':'Ol�st').' inl�gg:<br/>';

	echo 'Fr�n '.$gb['u_alias'].', '.$gb['sent_date'].'<br/>';
	echo $gb['sent_cmt'].'<br/><br/>';
	echo '<a href="guestbook_write.php?id='.$gb['main_id'].'&amp;reply">SVARA</a><br/>';
	echo '<a href="guestbook_history.php?id='.$gb['sender_id'].'">SE HISTORIK</a>';

	require('design_foot.php');
?>