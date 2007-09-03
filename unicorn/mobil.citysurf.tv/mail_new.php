<?
	require_once('config.php');
	$user->requireLoggedIn();

	require('design_head.php');

	$_to_alias = $_header = $_body = $error = '';

	$_to_id = 0;
	if (!empty($_GET['id']) && is_numeric($_GET['id'])) $_to_id = $_GET['id'];

	if (!$_to_id && !empty($_POST['to_alias'])) {
		$_to_alias = $_POST['to_alias'];
	} else if ($_to_id) {
		$tmp = $user->getuser($_to_id);
		$_to_alias = $tmp['u_alias'];
	} else {
		//bara "skriv nytt mail", låt användaren fylla i mottagare
	}
	if (!empty($_POST['header'])) $_header = $_POST['header'];
	if (!empty($_POST['body'])) $_body = $_POST['body'];

	if (!empty($_POST['friend_alias'])) $_to_alias = $_POST['friend_alias'];

	if ($_to_alias && $_header && $_body) {
		sendMail($_to_alias, '', $_header, $_body);
		echo 'Ditt mail har skickats!<br/>';
		require('design_foot.php');
		die;
	}
	
/*
	todo: kopiera vald kompis från dropdownlistan till "to_alias" fältet med js
*/

	echo '<div class="h_mail"></div>';
	echo 'SKRIV NYTT MAIL<br/>';
	echo '<br/>';

	if ($_to_alias && ($_header || $_body)) {
		echo 'Fel: Du måste skriva ett meddelande<br/><br/>';
	} else {
		if ($error) echo $error.'<br/>';
	}

	echo '<form method="post" action="'.$_SERVER['PHP_SELF'].'?id='.$_to_id.'">';
	if ($_to_id) {
		echo 'Till: '.$user->getstringMobile($_to_id);
	} else {
		echo 'Till: <input name="to_alias" type="text" size="8" value="'.$_to_alias.'"/> ';
		$list = getRelations($user->id);
	
		if ($list)
		{
			echo '<select name="friend_alias">';
			echo '<option value="">- Mina vänner -</option>';
			foreach ($list as $row) echo '<option value="'.$row['u_alias'].'">'.$row['u_alias'].'</option>';
			echo '</select>';
		}
	}
	echo '<br/>';
	echo 'Rubrik: <input name="header" type="text" value="'.$_header.'"/><br/>';
	echo 'Meddelande:<br/>';
	echo '<textarea name="body">'.$_body.'</textarea><br/>';
	echo '<input type="submit" value="Skicka"/>';
	echo '</form>';

	require('design_foot.php');
?>
