<?
	if (empty($_GET['id']) || !is_numeric($_GET['id'])) $_id = $l['id_id'];
	else $_id = $_GET['id'];

	require('config.php');
	require('design_head.php');
	
	$user_data = $user->getuser($_id);

	echo 'PROFIL - <b>'.$user_data['u_alias'].'</b> k�n&�lder (onlinestatus?)<br/>';
	echo '<br/>';
	echo '<a href="relations_create.php?id='.$_id.'">BLI V�N</a> ';
	echo '<a href="relations_block.php?id='.$_id.'">BLOCKERA</a> ';
	echo '<a href="gallery.php?id='.$_id.'">GALLERI</a> ';
	echo '<a href="relations.php?id='.$_id.'">V�NNER</a> ';
	echo '<a href="mail_new.php?id='.$_id.'">MAILA</a> ';
	echo '<a href="guestbook.php?id='.$_id.'">G�STBOK</a>';
	echo '<br/>';

	echo 'Mina fakta h�r.<br/>';
	echo 'Min persentation h�r...<br/>';
	echo '<br/>';
	echo '<a href="user_change_facts.php">�NDRA FAKTA</a><br/>';
	echo '<a href="user_change_password.php">�NDRA L�SENORD</a><br/>';

	require('design_foot.php');
?>