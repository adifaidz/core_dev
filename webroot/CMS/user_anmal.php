<?
	include('include_all.php');

	if (empty($_GET['id'])) {
		header('Location: '.$config['start_page']);
		die;
	}

	$show = $_GET['id'];
	/* Rapportera anv�ndaren till abuse */
	$queueId = addToModerationQueue($db, $show, MODERATION_REPORTED_USER);

	include('design_head.php');

	/* L�gg till en kommentar till anm�lan */
	if (isset($_POST['motivation'])) {
		addModerationQueueComment($db, $queueId, $_POST['motivation'], $_SESSION['userId']);

		echo 'Anm&auml;l '.getUserName($db, $show).'<br><br>';

		echo getInfoField($db, 'anm�l_anv�ndare_f�rdig').'<br><br>';

		echo '<a href="user_show.php?id='.$show.'">'.$config['text']['link_return'].'</a>';

	} else {

		echo 'Anm&auml;l '.getUserName($db, $show).'<br><br>';
		echo getInfoField($db, 'anm�l_anv�ndare');
		echo '<br><br>';

		echo '<form name="abuse" method="post" action="'.$_SERVER['PHP_SELF'].'?id='.$show.'">';
		echo 'Motivera anm&auml;lningen:<br>';
		echo '<textarea name="motivation" cols=44 rows=5></textarea><br>';
		echo '<input type="submit" class="button" value="'.$config['text']['link_report'].'">';
		echo '</form>';

		echo '<a href="user_show.php?id='.$show.'">'.$config['text']['link_return'].'</a><br>';
	}

	include('design_foot.php');
?>