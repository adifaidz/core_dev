<?
	require_once('config.php');

	$session->requireLoggedIn();

	$userId = $session->id;
	if (!empty($_GET['id']) && is_numeric($_GET['id'])) {
		$userId = $_GET['id'];
	}

	require('design_head.php');

	createMenu($profile_menu, 'blog_menu');

	if ($session->isAdmin || $session->id == $userId) {
		if (!empty($_GET['remove'])) {
			removeGuestbookEntry($db, $_GET['remove']);
		}
	}

	if ($session->id != $userId && !empty($_POST['body'])) {
		addGuestbookEntry($userId, '', $_POST['body']);
	}

	$tot_cnt = getGuestbookCount($userId);
	echo 'Guestbook:'.getUserName($userId).' contains '.$tot_cnt.' messages.<br/><br/>';

	$pager = makePager($tot_cnt, 5);

	echo $pager['head'];

	$list = getGuestbook($userId, $pager['limit']);
	foreach ($list as $row) {
		echo '<a name="gb'.$row['entryId'].'"></a>';
		echo '<div class="guestbook_entry">';

		echo '<div class="guestbook_entry_head">';
		echo 'From '.nameLink($row['authorId'], $row['authorName']);
		echo ', '.$row['timeCreated'];
		echo '</div>';

		if ($session->id == $userId) {
			if ($row['entryRead'] == 0) {
				echo '<img src="/gfx/icon_mail.png" alt="Unread">';
			}
		}
		echo stripslashes($row['body']).'<br/>';

		if ($session->isAdmin || $session->id == $userId) {
			echo '<a href="'.$_SERVER['PHP_SELF'].'?id='.$userId.'&amp;remove='.$row['entryId'].'">Remove</a>';
		}
		echo '</div><br/>';
	}

	if ($session->id) {
		if ($session->id != $userId) {
			echo 'New entry:<br/>';
			echo '<form name="addGuestbook" method="post" action="'.$_SERVER['PHP_SELF'].'?id='.$userId.'">';
			echo '<textarea name="body" cols="40" rows="6"></textarea><br/><br/>';
			echo '<input type="submit" class="button" value="Save"/>';
			echo '</form>';
		} else {
			/* Mark all entries as read */
			markGuestbookRead();
		}
	}

	require('design_foot.php');
?>