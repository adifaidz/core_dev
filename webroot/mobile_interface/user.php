<?
	require('config.php');

	if (empty($_GET['id']) || !is_numeric($_GET['id'])) $_id = $l['id_id'];
	else $_id = $_GET['id'];

	require('design_head.php');

	$user_data = $user->getuser($_id);

	if ($_id == $l['id_id']) {
		echo 'DIN PROFIL<br/>';
		echo '<br/>';
		echo '<a href="mail.php?id='.$_id.'">MAIL</a> ';
		//echo '<a href="gallery.php?id='.$_id.'">GALLERI</a> ';
		echo '<a href="relations.php?id='.$_id.'">V�NNER</a> ';
		echo '<a href="guestbook.php?id='.$_id.'">G�STBOK</a>';
		echo '<br/>';
		echo '<a href="settings.php">INST�LLNINGAR</a>';
	} else {
		$friends = areTheyFriends($l['id_id'], $_id);
		
		
		echo 'PROFIL - <b>'.$user_data['u_alias'].'</b> k�n &amp; �lder (onlinestatus?)<br/>';
		echo '<br/>';
		if (!$friends) {
			echo '<a href="relations_create.php?id='.$_id.'">BLI V�N</a> ';
			echo '<a href="relations_block.php?id='.$_id.'">BLOCKERA</a> ';
		} else {
			echo '<a href="relations.php?remove='.$_id.'">TA BORT RELATION</a> ';
		}
		echo '<a href="mail_new.php?id='.$_id.'">MAILA</a> ';
		echo '<a href="gallery.php?id='.$_id.'">GALLERI</a> ';
		echo '<a href="relations.php?id='.$_id.'">V�NNER</a> ';
		echo '<a href="guestbook.php?id='.$_id.'">G�STBOK</a>';
	}
	echo '<br/>';

	echo 'Mina fakta h�r.<br/>';
	echo 'Min persentation h�r...<br/>';

	require('design_foot.php');
?>