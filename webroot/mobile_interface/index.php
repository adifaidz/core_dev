<?
	require_once('config.php');
	require('design_head.php');

	//print_r($_SESSION);

	if (empty($s['id_id'])) {
		echo '<a href="login.php">LOGGA IN</a><br/>';
	}

	//<a href="gallery.php">DITT GALLERI</a><br/>
	if (!empty($s['id_id'])) {
?>
	<a href="gb.php">DIN G�STBOK</a> (<?=gbCountUnread();?> ol�sta)<br/>
	<a href="mail.php">DIN MAIL</a>(<?=getUnreadMailCount();?> ol�sta)<br/>
	<a href="friends.php">DINA V�NNER</a>(<?=relationsOnlineCount();?> online)<br/>
	<a href="blocked.php">DINA BLOCKERINGAR</a><br/>
	<a href="user.php">DIN PROFIL</a><br/>
	<a href="logout.php">LOGGA UT</a><br/>
	<br/>
	<a href="search_users.php">S�K ANV�NDARE</a><br/>
	<a href="users_last_online.php">SENAST ONLINE</a><br/>
<?
	}

	require('design_foot.php');
?>