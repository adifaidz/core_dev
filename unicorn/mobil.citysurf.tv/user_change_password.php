<?
	require_once('config.php');
	$user->requireLoggedIn();

	require('design_head.php');

	$error = false;
	if (!empty($_POST['ins_opass']) && !empty($_POST['ins_npass']) && !empty($_POST['ins_npass2'])) {
		$error = setNewPassword($_POST['ins_opass'], $_POST['ins_npass'], $_POST['ins_npass2']);
		if ($error === true) {
			echo 'Lösenordet har ändrats!';
			require('design_foot.php');
			die;
		}
	}
?>

	ÄNDRA LÖSENORD<br/>
	<br/>
<?
	if ($error) echo 'Fel: '.$error.'<br/><br/>';
?>

	<form method="post" action="<?=$_SERVER['PHP_SELF']?>">
		Gammalt lösenord:<br/>
		<input name="ins_opass" type="password"/><br/>

		Nytt lösenord:<br/>
		<input name="ins_npass" type="password"/><br/>

		Bekräfta lösenord:<br/>
		<input name="ins_npass2" type="password"/><br/>
		<br/>
		<input type="submit" value="Spara"/>
	</form>

<?
	require('design_foot.php');
?>
