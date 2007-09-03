<?
	/*
		todo: kanske 2 separata filer för write & reply?
	*/

	if (empty($_GET['id']) || !is_numeric($_GET['id'])) die;

	$_reply_to_msg_id = 0;
	$_write_to_user_id = 0;
	if (isset($_GET['reply'])) $_reply_to_msg_id = $_GET['id'];
	else $_write_to_user_id = $_GET['id'];

	require_once('config.php');
	$user->requireLoggedIn();
	
	require('design_head.php');

	$gb = gbGetById($_reply_to_msg_id);
	if (isset($_GET['reply'])) $_write_to_user_id = $gb['sender_id'];

	if (!empty($_POST['msg'])) {
		//send guestbook message
		gbWrite($_POST['msg'], $_write_to_user_id, $_reply_to_msg_id);
		echo 'Gästboksinlägg till '.$user->getstringMobile($_write_to_user_id).' skickat!<br/>';
		require('design_foot.php');
		die;
	}

	echo '<div class="h_gb"></div>';
	if (isset($_GET['reply'])) echo 'SKRIV SVAR';
	else echo 'SKRIV NYTT MEDDELANDE';
?>
	<br/>
	<br/>
	Skriv <?if (isset($_GET['reply'])) echo 'svar '; ?>till <?=$user->getstringMobile($_write_to_user_id)?>:<br/>
	<form method="post" action="<?=$_SERVER['PHP_SELF'].'?id='.$_GET['id'].(isset($_GET['reply'])?'&amp;reply':'')?>">
		<textarea name="msg"></textarea><br/>
		<input type="submit" value="Skicka"/>
	</form>

<?
	require('design_foot.php');
?>
