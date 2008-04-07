<?
	require_once('find_config.php');

	$session->requireAdmin();

	require($project.'design_head.php');

	echo createMenu($admin_menu, 'blog_menu');
	
	if (empty($config['moderation']['enabled'])) {
		echo 'Moderation feature is not enabled';
		require($project.'design_foot.php');
		die;
	}

	$tot_cnt = getModerationQueueCount();
	
	$pager = makePager($tot_cnt, 5);

	$changed_list = false;
	
	$list = getModerationQueue(0, $pager['limit']);
	foreach ($list as $row) {
		if (!isset($_POST['method_'.$row['queueId']])) continue;
		$changed_list = true;

		if ($_POST['method_'.$row['queueId']] == 'accept') {
			/* Accepts forum item and removes it from queue */
			deleteComments(COMMENT_MODERATION, $row['queueId']);
			removeFromModerationQueue($row['queueId']);
			continue;
		}

		switch ($row['queueType']) {
			case MODERATION_GUESTBOOK:
				removeGuestbookEntry($row['itemId']);
				removeFromModerationQueue($row['queueId']);
				break;

			case MODERATION_BLOG:
				deleteBlog($row['itemId']);
				removeFromModerationQueue($row['queueId']);
				break;
				
			case MODERATION_FORUM:
				deleteForumItem($row['itemId']);
				removeFromModerationQueue($row['queueId']);
				break;

			case MODERATION_FILE:
				if (isset($_POST['delete_'.$row['queueId'].'_message'])) {
					$subject = 'Fil borttagen';
					$msg  = 'En av dina uppladdade filer har tagits bort.<br/>';
					$msg .= 'Anledning: '.$_POST['delete_'.$row['queueId'].'_message'];
					$owner = Files::getUploader($row['itemId']);
					systemMessage($owner, $subject, $msg);
				}
				$files->deleteFile($row['itemId']);
				removeFromModerationQueue($row['queueId']);
				break;

			case MODERATION_PRES_IMAGE:
//			echo $_POST['delete_'.$row['queueId'].'_message'];
				$owner = Files::getUploader($row['itemId']);
				if (isset($_POST['delete_'.$row['queueId'].'_message'])) {
					$subject = 'Presentationsbild borttagen';
					$msg  = 'Din presentationsbild har tagits bort.'."\n";
					$msg .= 'Anledning: '.$_POST['delete_'.$row['queueId'].'_message'];
					systemMessage($owner, $subject, $msg);
				}
/*
echo $owner.' AAA ';
echo loadSetting(SETTING_USERDATA, $owner, getUserdataFieldIdByType(USERDATA_TYPE_IMAGE));
exit(1);
*/
				deleteSetting(SETTING_USERDATA, $owner, getUserdataFieldIdByType(USERDATA_TYPE_IMAGE));
				$files->deleteFile($row['itemId']);
				removeFromModerationQueue($row['queueId']);
				break;

			default: die('cant delete unknown type');
		}
	}

	if (!empty($_GET['comments'])) {

		showComments(COMMENT_MODERATION, $_GET['comments']);

		require($project.'design_foot.php');
		die;
	}

	if ($changed_list) $list = getModerationQueue(0, $pager['limit']);

	echo $pager['head'];

	if (count($list)) {

		echo 'Displaying '.count($list).' object(s) in the moderation queue. Showing oldest items first.<br/><br/>';
		
		echo '<form method="post" action="">';

		foreach ($list as $row) {
			echo '<div class="item">';

			switch ($row['queueType']) {
				case MODERATION_GUESTBOOK:	$title = 'Guestbook entry'; break;
				case MODERATION_BLOG:		$title = 'Blog'; break;
				case MODERATION_FORUM:		$title = 'Forum'; break;
				case MODERATION_USER:		$title = 'Reported user: '.Users::link($row['itemId']); break;
				case MODERATION_FILE:		$title = 'Reported file '.showFile($row['itemId'], '', '', false); break;
				case MODERATION_PRES_IMAGE:	$title = 'Reported presentation image '.showFile($row['itemId'], '', '', false); break;

				default: $title = '<div class="critical">Unknown queueType '.$row['queueType'].', itemId '.$row['itemId'].'</div>';
			}
			echo '<div class="item_head">'.$title;
			if ($row['autoTriggered']) echo ' (auto-triggered)';
			echo '</div>';
			
			if (!$row['autoTriggered']) echo 'Reported by '.Users::link($row['creatorId'], $row['creatorName']).' at '.$row['timeCreated'].'<br/>';

			switch ($row['queueType']) {
				case MODERATION_GUESTBOOK:
					$gb = getGuestbookItem($row['itemId']);
					echo '<a href="'.$project.'guestbook.php?id='.$gb['userId'].getProjectPath().'#gb'.$row['itemId'].'" target="_blank">Read the entry</a>';
					break;

				case MODERATION_BLOG:
					echo '<a href="'.$project.'blog.php?Blog:'.$row['itemId'].getProjectPath().'" target="_blank">Read the blog</a>';
					break;

				case MODERATION_FORUM:
					$item = getForumItem($row['itemId']);
					showForumPost($item);
					echo '<a href="'.$project.'forum.php?id='.$item['parentId'].'#post='.$item['itemId'].getProjectPath().'" target="_blank">Read the topic</a>';
					break;
			}

			echo '<table summary="" width="100%"><tr><td width="50%">';
			echo '<input type="radio" class="radio" name="method_'.$row['queueId'].'" id="accept_'.$row['queueId'].'" value="accept"/>';
			echo '<label for="accept_'.$row['queueId'].'"> Accept</label>';
			echo '</td>';
			echo '<td>';
			echo '<input type="radio" class="radio" name="method_'.$row['queueId'].'" id="delete_'.$row['queueId'].'" value="delete" onClick="getElementById(\'deletediv'.$row['queueId'].'\').style.display=\'block\'"/>';
			echo '<label for="delete_'.$row['queueId'].'"> Delete</label>';
			echo '<div id="deletediv'.$row['queueId'].'" style="display:none;">';
				echo '<input type="radio" class="radio" name="delete_'.$row['queueId'].'_message" value="Reklam"/> Reklam';
				echo '<br/><input type="radio" class="radio" name="delete_'.$row['queueId'].'_message" value="Stötande"/> Stötande';
			echo '</div>';
			echo '</td></tr></table>';

			if (!$row['autoTriggered']) {
				$mcnt = getCommentsCount(COMMENT_MODERATION, $row['queueId']);
				if ($mcnt) {
					echo '<a href="?comments='.$row['queueId'].getProjectPath().'">Motivations ('.$mcnt.')</a>';
				} else {
					echo 'Motivations (0)';
				}
			}

			echo '</div>'; //class="item"
			echo '<br/>';
		}
		echo '<input type="submit" class="button" value="Commit changes"/>';
		echo '</form>';
	} else {
		echo 'The moderation queue is empty!<br/>';
	}

	require($project.'design_foot.php');
?>
