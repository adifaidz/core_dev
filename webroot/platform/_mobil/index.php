<?
	require_once('config.php');
	require('design_head.php');

	if (empty($s['id_id'])) {

		echo '<a href="login.php">LOGGA IN</a><br/>';
	} else {
		echo '<a href="relations.php"><img src="gfx/q_relations.png" alt="Relationer"/></a> ';
		echo '<a href="users_last_online.php"><img src="gfx/q_lastonline.png" alt="Senast online"/></a><br/>';

		echo '<a href="search_users.php"><img src="gfx/q_search.png" alt="S�k anv�ndare"/></a> ';
		echo '<a href="logout.php"><img src="gfx/q_logout.png" alt="Logga ut"/></a><br/>';

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