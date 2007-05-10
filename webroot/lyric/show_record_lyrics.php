<?
	require_once('config.php');
	require('design_head.php');
	
	if (empty($_GET['id']) || !is_numeric($_GET['id'])) die;

	$record_id = $_GET['id'];
		
	$band_id = getBandIdFromRecordId($record_id);

	echo '<a name="top"></a>';
	if ($band_id == 0) {
		echo '<b>V/A - '.getRecordName($record_id).'</b>';
	} else {
		echo '<b><a href="show_band.php?id='.$band_id.'">'.getBandName($band_id).'</a>';
		echo ' - '.getRecordName($record_id).'</b>';
	}
	echo '<br/><br/>';

	$list = getRecordTracks($record_id);
	
	/* First list track titles */
	for ($i=0; $i<count($list); $i++)
	{
		$track = $list[$i]['trackNumber'];
		$lyric_id = $list[$i]['lyricId'];

		if ($band_id == 0) {
			echo '<b>'.$track.'. '.$list[$i]['bandName'] .' - '.$list[$i]['lyricName'].'</b>';
		} else {
			echo '<b><a href="#'.$i.'">'.$track.'. '.stripslashes($list[$i]['lyricName']).'</a></b>';
		}
		
		if ($list[$i]['authorId'] != $list[$i]['bandId']) {
			echo ' (Cover by <a href="show_band.php?id='.$list[$i]['authorId'].'">'.getBandName($list[$i]['authorId']).'</a>)';
		}
		echo '<br/>';
	}
	echo '<br/><br/><br/>';
	
	/* Then list the lyrics */
	for ($i=0; $i<count($list); $i++)
	{
		echo '<a name="'.$i.'"></a><br/>';
		$track = $list[$i]['trackNumber'];
		$lyric_id = $list[$i]['lyricId'];

		if ($band_id == 0) {
			echo '<b>'.$track.'. '.$list[$i]['bandName'] .' - '.stripslashes($list[$i]['lyricName']).'</b>';
		} else {
			echo '<b>'.$track.'. '.stripslashes($list[$i]['lyricName']).'</b>';
		}

		if ($list[$i]['authorId'] != $list[$i]['bandId']) {
			echo ' (Cover by <a href="show_band.php?id='.$list[$i]['authorId'].'">'.getBandName($list[$i]['authorId']).'</a>)';
		}
		if ($session->id) echo ' <a href="edit_lyric.php?id='.$lyric_id.'">Edit</a><br/>';
		
		$lyric = stripslashes($list[$i]['lyricText']);
		if ($lyric)
		{
			$lyric = str_replace('&amp;', '&', $lyric);
			$lyric = str_replace('&', '&amp;', $lyric);
			echo nl2br($lyric);

		} else {
			echo 'Lyric missing.';
		}
		echo '<br/>';
		echo '<a href="#top">To top</a><br/>';
		echo '<br/><br/><br/>';
	}

	require('design_foot.php');
?>