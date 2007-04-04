<?
	include ('include_all.php');
	include('body_header.php');

	if (isset($_GET['band']) && $_GET['band']) {
		$band_id = $_GET['band'];
	}

	if ($_SESSION['loggedIn'] && !empty($_POST['band']) && isset($_POST['recordname']) && isset($_POST['info']) && !empty($_POST['tracks']))
	{
		$band_id = $_POST['band'];
		$record_name = trim($_POST['recordname']);
		$record_info = trim($_POST['info']);
		$tracks = $_POST['tracks'];
		
		$record_id = addRecord($db, $_SESSION['userId'], $band_id, $record_name, $record_info);
		if (!$record_id)
		{
			echo 'Problems adding record.<br/>';
		}
		else
		{
			createTracks($db, $record_id, $tracks);
			
			echo 'Record "'.$record_name.'" added.<br/><br/>';
			
			if ($_SESSION['userMode'] == 0) {
				/* Add recordId to moderation queue */
				addModerationItem($db, $record_id, MODERATION_RECORD);
				echo 'Record added to moderation queue aswell.<br/><br/>';
			}

			echo '<a href="show_record.php?id='.$record_id.'">Click here to go to it now</a>.<br/><br/>';
		}
	}

	echo '<form name="addrecord" method="post" action="'.$_SERVER['PHP_SELF'].'">';
	echo '<table width="400" cellpadding="0" cellspacing="0" border="0">';
	echo '<tr><td width="120">Band name:</td><td><select name="band">';
	echo '<option>--- Select band ---</option>';
	$list = getBands($db);
	for ($i=0; $i<count($list); $i++)
	{
		echo '<option value="'.$list[$i]['bandId'].'"';
		if (isset($band_id) && $band_id == $list[$i]['bandId']) echo ' selected="selected"';
		echo '>'.$list[$i]['bandName'].'</option>';
	}
	echo '</select></td></tr>';
	
	echo '<tr><td>Record name:</td><td><input type="text" name="recordname"/> (leave empty for s/t)</td></tr>';
	echo '<tr><td>Number of tracks:</td><td><input type="text" name="tracks" value="3"/></td></tr>';
	echo '<tr><td valign="top">Record info:<br/>(optional)</td><td><textarea name="info" cols="40" rows="8"></textarea></td></tr>';
	echo '<tr><td colspan="2"><input type="submit" value="Add" class="buttonstyle"/></td></tr>';
	echo '</table>';
	echo '</form>';

	if (isset($band_id)) {
?>
<script type="text/javascript">
document.addrecord.recordname.focus();
</script>
<?
	}
	
	include('body_footer.php');
?>