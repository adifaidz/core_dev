<?
	include('include_all.php');

	include('design_head.php');

	if ($_SESSION['loggedIn']) {
		echo '<h2>Start page</h2>';
		echo 'You are logged in as '.$_SESSION['userName'];
		if ($_SESSION['isSuperAdmin']) echo ' (super admin)';
		else if ($_SESSION['isAdmin']) echo ' (administrator)';
		else echo ' (normal user)';
		echo '<br><br>';

	} else {
		echo 'You are not logged in ...';
	}
	
	echo getInfoField($db, 'startpage');

	include('design_foot.php');
?>