<?
	require_once('config.php');
	
	$session->requireLoggedIn();
	
	if (isset($_GET['id'])) $itemId = $_GET['id'];
	else die;

	require('design_head.php');

	/* L�gg till en kommentar till anm�lan */
	if (isset($_POST['mail'])) {

		if (ValidEmail($_POST['mail'])) {
			
			$item = getForumItem($itemId);
			
			if (isset($_POST['namn']) && $_POST['namn']) {
				$mail = "Hej ".$_POST["namn"]."!\n\n";
			} else {
				$mail = "Hej!\n\n";
			}

			$mail .= $_SESSION["userName"]." har skickat dig den h�r l�nken till dig fr�n communityt\n";
			$mail .= "p� v�r sajt, ".$config['site_url']."/.\n\n";
			
			if ($item["authorId"]) {
				$mail .= $item["itemSubject"]." av ".$item["authorName"].", ".getRelativeTimeLong($item["timestamp"]).":\n";
			} else {
				$mail .= $item["itemSubject"]." av g�st, ".getRelativeTimeLong($item["timestamp"])."\n";
			}

			$mail .= "F�r att l�sa inl�gget i sin helhet, klicka p� l�nken nedan:\n";
			$mail .= $config['site_url']."/forum.php?id=".$itemId."#".$itemId."\n\n";

			if (isset($_POST["comment"]) && $_POST["comment"]) {
				$mail .= "\n";
				$mail .= "Din kompis l�mnade �ven f�ljande h�lsning:\n";
				$mail .= $_POST["comment"]."\n\n";
			}
			
			$subject = "Meddelande fr�n communityt";
			//fixme: from mail ska va konfigurerbar blabla
			if (sendMail($_POST['mail'], $subject, $mail, "xxx <no@mail.com>") == true) {

				echo 'Tipset iv�gskickat<br>';

			} else {
				echo 'Problem med utskicket<br>';
			}
			echo '<br><br>';
			echo '<a href="forum.php?id='.$itemId.'#'.$itemId.'">Return</a>';
			
		} else {
			echo 'Ogiltig mailaddress!';
		}

	} else {

		echo '<b>Tipsa om inl&auml;gg</b><br>';

		wiki('Tell someone about this post');
		echo '<br><br>';
		
		
		$data = getForumItem($itemId);
		echo showForumPost($data).'<br>';
	
		echo '<form name="tipsa" method="post" action="'.$_SERVER['PHP_SELF'].'?id='.$itemId.'">';
		echo 'Din kompis namn: <input name="namn" type="text" maxlength=30 size=20><br>';
		echo 'E-post: <input name="mail" type="text" maxlength=50 size=40><br>';
		echo '<br>';
		echo 'H&auml;lsning:<br>';
		echo '<textarea name="comment" cols=40 rows=6></textarea><br>';
		echo '<input type="submit" class="button" value="Tipsa">';
		echo '</form>';

		echo '<a href="forum.php?id='.$itemId.'#'.$itemId.'">Return</a>';
	}

	require('design_foot.php');
?>