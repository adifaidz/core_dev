<?
	include("include_all.php");

	if (!$_SESSION['loggedIn'] || empty($_GET['id']) || !is_numeric($_GET['id'])) {
		header('Location: '.$config['start_page']);
		die;
	}

	$itemId = $_GET['id'];
	$item = getForumItem($db, $itemId);
	if (!$item) {
		header('Location: '.$config['start_page']);
		die;
	}

	/* L�gg till en kommentar till anm�lan */
	if (isset($_POST['motivation'])) {

		/* Rapportera inl�gget till abuse */
		$queueId = addToModerationQueue($db, $itemId, MODERATION_REPORTED_POST);
		addComment($db, COMMENT_MODERATION_QUEUE, $queueId, $_POST['motivation']);

		header('Location: forum.php?id='.$item['parentId']);
		die;
	}

	include('design_head.php');
	include('design_forum_head.php');

	$content = '';

	//echo getInfoField($db, 'anm�l_inl�gg').'<br><br>';

	$content .= showForumPost($db, $item, '', false).'<br>';

	$content .= '<form name="abuse" method="post" action="'.$_SERVER['PHP_SELF'].'?id='.$itemId.'">';
	$content .= 'Begrunn anmeldelsen:<br>';
	$content .= '<textarea name="motivation" cols=50 rows=5></textarea><br><br>';
	$content .= '<input type="submit" class="button" value="'.$config['text']['link_report'].'">';
	$content .= '</form><br><br>';
	$content .= '<a href="javascript:history.go(-1);">'.$config['text']['link_return'].'</a>';

	echo '<div id="user_forum_content">';
	echo MakeBox('<a href="forum.php">Forum</a>|Rapporter innlegg', $content, 500);
	echo '</div>';

	include('design_forum_foot.php');
	include('design_foot.php');
?>