<?
	require('config.php');

	require('design_head.php');

	print_r($_SESSION);



	if (!empty($s['id_id'])) {
		echo '<a href="logout.php">LOGGA UT</a><br/>';
	} else {
		echo '<a href="login.php">LOGGA IN</a><br/>';
	}
?>
	<br/>
	<br/>

	inloggad:<br/>
	<a href="guestbook.php">DIN G�STBOK</a> (0 ol�sta)<br/>
	<a href="mail.php">DIN MAIL</a>(2 ol�sta)<br/>
	<a href="relations.php">DINA V�NNER</a>(2 online)<br/>
	<a href="blocked.php">DINA BLOCKERINGAR</a><br/>
	<a href="user.php">DIN PROFIL</a><br/>
	<a href="gallery.php">DITT GALLERI</a><br/>
	
	<br/>
	<a href="search_users.php">S�K ANV�NDARE</a><br/>
	<a href="users_last_online.php">SENAST ONLINE</a><br/>

<?
	require('design_foot.php');
?>