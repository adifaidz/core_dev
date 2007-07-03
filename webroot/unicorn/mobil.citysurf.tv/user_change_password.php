<?
	require_once('config.php');
	if (!$l) die;	//user not logged in

	require('design_head.php');

	$error = false;
	if (!empty($_POST['ins_opass']) && !empty($_POST['ins_npass']) && !empty($_POST['ins_npass2'])) {
		$error = setNewPassword($_POST['ins_opass'], $_POST['ins_npass'], $_POST['ins_npass2']);
		if ($error === true) {
			echo 'L�senordet har �ndrats!';
			require('design_foot.php');
			die;
		}
	}
?>

	�NDRA L�SENORD<br/>
	<br/>
<?
	if ($error) echo 'Fel: '.$error.'<br/><br/>';
?>

	<form method="post" action="<?=$_SERVER['PHP_SELF']?>">
		Gammalt l�senord:<br/>
		<input name="ins_opass" type="password"/><br/>

		Nytt l�senord:<br/>
		<input name="ins_npass" type="password"/><br/>

		Bekr�fta l�senord:<br/>
		<input name="ins_npass2" type="password"/><br/>
		<br/>
		<input type="submit" value="Spara"/>
	</form>

<?
	require('design_foot.php');
?>