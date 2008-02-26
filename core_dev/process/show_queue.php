<?
	require_once('config.php');
	$session->requireLoggedIn();

	require('design_head.php');

	wiki('ProcessShowQueue');

	$list = getProcessQueue(50, isset($_GET['completed']));
	if (!empty($list)) {
		foreach ($list as $row) {
			echo '<div class="item">';
			echo '#'.$row['entryId'].': ';

			switch ($row['orderType']) {
				case PROCESSQUEUE_AUDIO_RECODE:
					echo 'Audio recode to <b>"'.$row['orderParams'].'"</b><br/>';
					break;
					
				case PROCESSQUEUE_IMAGE_RECODE:
					echo 'Image recode to <b>"'.$row['orderParams'].'"</b><br/>';
					break;

				case PROCESSQUEUE_VIDEO_RECODE:
					echo 'Video recode to <b>"'.$row['orderParams'].'"</b><br/>';
					break;

				case PROCESS_FETCH:
					echo 'Fetch remote media from <b>'.$row['orderParams'].'</b><br/>';
					break;

				case PROCESS_UPLOAD:
					echo 'Uploaded remote media from client<br/>';
					break;

				case PROCESSMONITOR_SERVER:
					$d = unserialize($row['orderParams']);
					echo 'Monitor remote server <b>'.$d['adr'].'</b> for '.$d['type'].' uptime<br/>';
					break;

				case PROCESS_CONVERT_TO_DEFAULT:
					echo 'Convert media to default type for entry #'.$row['referId'].'<br/>';
					break;

				default:
					die('unknown processqueue type: '.$row['orderType']);
			}

			if ($row['orderType'] != PROCESS_CONVERT_TO_DEFAULT) {
				if ($row['referId']) {
					echo '<a href="show_file_status.php?id='.$row['referId'].'">Show file status</a><br/>';
				}

				$file = $files->getFileInfo($row['referId']);
				if ($file) {
					echo '<h3>Source file:</h3><br/>';
					echo $file['fileName'].' ('.$file['fileMime'].')<br/>';
					echo 'size: '.formatDataSize($file['fileSize']).'<br/>';
					echo 'sha1: '.$files->sha1($row['referId']).'<br/>';
				}
			}

			echo $row['timeCreated'].' added by '.Users::link($row['creatorId']).'<br/>';

			if ($row['orderStatus'] == ORDER_COMPLETED) {
				echo '<b>Order completed</b><br/>';
				echo 'Exec time: '.round($row['timeExec'], 3).'s<br/>';
			}

			echo '</div>';
		}
	} else {
		echo 'Queue is empty.<br/>';
	}

	if (!isset($_GET['completed'])) {
		echo '<a href="?completed">Show completed queue items</a>';
	} else {
		echo '<a href="?">Show pending queue items</a>';
	}

	require('design_foot.php');
?>