<?
	require_once('config.php');
	require('design_head.php');

	if (empty($s['id_id'])) {

		echo '<a href="login.php">LOGGA IN</a><br/><br/>';

		echo 'V�lkommen till f�rsta versionen av Citysurf i mobilen. Vi tar g�rna emot synpunkter via tyck till p� Huvudsajten.<br/>';

	} else {
		echo '<a href="relations.php"><img src="gfx/btn_friends.png" alt="V�nner" width="44" height="44"/></a>&nbsp;';
		echo '<a href="users_last_online.php"><img src="gfx/btn_online.png" alt="Senast online" width="44" height="44"/></a>&nbsp;';
		echo '<a href="search_users.php"><img src="gfx/btn_search.png" alt="S�k anv�ndare" width="44" height="44"/></a><br/>';

		echo '<a href="surftalk.php"><img src="gfx/btn_surftalk.png" alt="Surftalk" width="44" height="44"/></a>&nbsp;';
		echo '<a href="info.php"><img src="gfx/btn_info.png" alt="Info" width="44" height="44"/></a>&nbsp;';
		echo '<a href="logout.php"><img src="gfx/btn_logout.png" alt="Logga ut" width="44" height="44"/></a><br/>';

		//echo '<a href="gb.php">DIN G�STBOK</a> ('.gbCountUnread().' ol�sta)<br/>';
		//echo '<a href="mail.php">DIN MAIL</a>('.getUnreadMailCount().' ol�sta)<br/>';
		//echo '<a href="friends.php">DINA V�NNER</a>('.relationsOnlineCount().' online)<br/>';
		//echo '<a href="blocked.php">DINA BLOCKERINGAR</a><br/>';
		//echo '<a href="user.php">DIN PROFIL</a><br/>';
		//echo '<a href="search_users.php">S�K ANV�NDARE</a><br/>';
		//echo '<a href="users_last_online.php">SENAST ONLINE</a><br/>';
		//echo '<br/>';
		//echo '<a href="logout.php">LOGGA UT</a><br/>';
	}

	require('design_foot.php');
?>