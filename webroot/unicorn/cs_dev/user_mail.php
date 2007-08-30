<?
	require_once('config.php');
	$user->requireLoggedIn();
	
	$id = $user->id;

	$page = 'in';
	if (isset($_GET['out'])) $page = 'out';

	if (!empty($_GET['del_msg'])) {
		if (mailDelete($_GET['del_msg'])) {
			reloadACT('user_mail.php?'.$page);
		}

	} else if (!empty($_POST['chg'])) {
		
		if (mailDeleteArray($_POST['chg'])) {		
			reloadACT('user_mail.php?'.$page);
		}

	}
	$paging = paging(@$_GET['p'], 20);
	if($page == 'in') {
		$paging['co'] = mailInboxCount();
		$res = mailInboxContent($paging['slimit'], $paging['limit']);
	} else {
		$paging['co'] = mailOutboxCount();
		$res = mailOutboxContent();
	}
	require(DESIGN."head_user.php");
?>

	<div class="subHead">brev</div><br class="clr"/>
	<? makeButton(!isset($_GET['out']), 	'goLoc(\'user_mail.php\')',	'icon_mail.png', 'inkorg'); ?>
	<? makeButton(isset($_GET['out']), 	'goLoc(\'user_mail.php?out\')',	'icon_mail.png', 'utkorg'); ?>
	<br/><br/><br/>

	<div class="centerMenuBodyWhite">
<?dopaging($paging, 'user_mail.php?'.$page.'&amp;p=', '', 'big', STATSTR);?>
<form name="m" action="<?=$_SERVER['PHP_SELF'].'?'.$page?>" method="post">
<?
if(count($res) && !empty($res)) {
echo '<input type="checkbox" onclick="toggle2(this);" class="chk" style="margin-bottom: 3px;"/><input type="submit" value="radera mark." class="btn2_min" /><hr />';
}
?>
<table summary="" cellspacing="0" width="586">
<?
if(count($res) && !empty($res)) {
	
	foreach($res as $row) {
		$c = ($page == 'in' && !$row['user_read'])?' bld':'';
		
		$title = ($row['sent_ttl']?secureOUT($row['sent_ttl']):'<em>Ingen titel</em>');
		if (strlen($title) > 30) $title = substr($title, 0, 30).'...';

		echo '<tr'.($c?' class="'.$c.'"':'').'>
			<td style="width: 10px; padding-right: 10px;"><input type="checkbox" class="chk" name="chg[]" value="'.$row['main_id'].'" /></td>
			<td class="cur"><div style="overflow: hidden; height: 20px; width: 200px; padding-top: 4px;">'.($row['is_answered']?'<img src="'.$config['web_root'].'_gfx/icon_answered.png" align="top" alt="Besvarat brev">':'').'<a href="user_mailread.php?id='.$row['main_id'].'&amp;'.$page.'">'.$title.'</a>&nbsp;</div></td>
			<td style="padding-top: 4px;">'.($row['sender_id']?$user->getstring($row):'SYSTEM').'</td>
			<td class="rgt" style="padding-top: 4px;">'.nicedate($row['sent_date'], 1, 1).'&nbsp;</td>';
		
		echo '<td width="66">';
		makeButton(false, 'if(confirm(\'S�ker ?\n\nMeddelandet kommer att raderas.\')) goLoc(\'user_mail.php?'.$page.'&amp;del_msg='.$row['main_id'].'\');', 'icon_delete.png', 'radera');
		echo '</td>';

		echo '</tr>';
	}
} else {
	echo '<tr><td class="pdg cnt">Inga brev.</td></tr>';
}
?>
</table>
</form>
	</div>
<?
	require(DESIGN.'foot_user.php');
?>
