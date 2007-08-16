<?
	require_once('config.php');

	if(empty($_GET['id']) || !is_numeric($_GET['id'])) die('Tråden existerar inte');

	$res = $db->getOneRow("SELECT f.main_id, f.topic_id, f.sent_html, f.top_id, f.subject_id, f.status_id, f.parent_id, f.view_id, f.sender_id, f.sent_cmt, f.sent_ttl, f.sent_date, u.id_id, u.u_alias, u.account_date, u.u_sex, u.u_birth, u.u_picid, u.u_picd, u.u_picvalid, u.level_id FROM s_f f LEFT JOIN s_user u ON u.id_id = f.sender_id AND u.status_id = '1' WHERE f.parent_id = '0' AND f.main_id = '".$db->escape($_GET['id'])."' AND f.status_id = '1' LIMIT 1");
	if(empty($res) || !count($res) || $_GET['id'] != $res['top_id']) {
		errorACT('Tråden existerar inte', l('forum'));
	}
	$r = $db->getOneRow("SELECT main_id, main_ttl, main_cmt, subjects FROM s_ftopic f WHERE f.main_id = '".$res['topic_id']."' AND f.status_id = '1' LIMIT 1");
	if (empty($r) || !count($r)) {
		errorACT('Rubriken existerar inte', l('forum'));
	}

	$item = '';
	$check = false;
	$ok = false;
	$doRead = true;
	$flimit = 16;
	$ftot = 36;
	$locatelast = (!empty($_GET['last']))?true:false;
	$getlast = true;
	$item = 0;
	if(!empty($_GET['item'])) {
		$item = intval($_GET['item']);
	}

	if(!empty($_GET['del']) && is_numeric($_GET['del'])) {
		$check = $sql->queryResult("SELECT main_id FROM s_f WHERE parent_id = '".secureINS($_GET['del'])."' AND status_id = '1' LIMIT 1");
		if(!$check) {
			@$sql->queryUpdate("UPDATE s_f SET status_id = '2' WHERE main_id = '".secureINS($_GET['del'])."' AND sender_id = '".secureINS($l['id_id'])."' LIMIT 1");
		} else {
			$s = @$sql->queryUpdate("UPDATE s_f SET view_id = '2', check_id = '1' WHERE main_id = '".secureINS($_GET['del'])."' AND sender_id = '".secureINS($l['id_id'])."' LIMIT 1");
		}
		reloadACT(l('forum', 'read', $res['main_id']));
	}

	/* bevakningar */
	if ($res['main_id'] && isset($_GET['subscribe'])) {
		spyAdd($res['main_id'], 'f');
	}

	if ($res['main_id'] && isset($_GET['unsubscribe'])) {
		spyDelete($res['main_id'], 'f');
	}

	if($user->isAdmin) {
		if(!empty($_GET['delete']) && is_numeric($_GET['delete'])) {
			$check = $sql->queryResult("SELECT main_id FROM s_f WHERE parent_id = '".secureINS($_GET['delete'])."' LIMIT 1");
			if(!$check) {
				@$sql->queryUpdate("DELETE FROM s_f WHERE main_id = '".secureINS($_GET['delete'])."' LIMIT 1");
			} else {
				errorACT('Det finns foruminlägg i en lägre nivå, ta bort dessa först.', l('forum','read',$res['main_id']));
			}
			reloadACT(l('forum', 'read', $res['main_id']));
		}
		if(!empty($_GET['hide']) && is_numeric($_GET['hide'])) {
			@$sql->queryUpdate("UPDATE s_f SET view_id = '2', check_id = '1' WHERE main_id = '".secureINS($_GET['hide'])."' LIMIT 1");
			reloadACT('forum','read',$res['main_id'].'&amp;item='.$item);
		}
		if(!empty($_GET['off']) && is_numeric($_GET['off'])) {
			@$sql->queryUpdate("UPDATE s_f SET status_id = '2' WHERE main_id = '".secureINS($_GET['off'])."' LIMIT 1");
			reloadACT(l('forum','read',$res['main_id']).'&amp;item='.$item);
		}
		if(!empty($_GET['show']) && is_numeric($_GET['show'])) {
			@$sql->queryUpdate("UPDATE s_f SET view_id = '1' WHERE main_id = '".secureINS($_GET['show'])."' LIMIT 1");
			reloadACT(l('forum','read',$res['main_id']).'&amp;item='.$item);
		}
	}
	if(!empty($_GET['spy'])) { $user->cleanspy($l['id_id'], $res['main_id'], 'FAN'); }
	$c = $db->getOneItem("SELECT COUNT(*) FROM s_f WHERE topic_id = '".$db->escape($r['main_id'])."' AND parent_id = '0' AND status_id = '1'");
	$d = $db->getOneItem("SELECT COUNT(*) FROM s_f WHERE topic_id = '".$db->escape($r['main_id'])."' AND parent_id != '0' AND status_id = '1'");
	$own = $db->getArray("SELECT o.main_id, o.top_id, o.sent_ttl, o.sent_date FROM s_f o INNER JOIN s_f p ON p.main_id = o.top_id AND p.status_id = '1' WHERE o.sender_id = '".$user->id."' AND o.status_id = '1' AND o.view_id = '1' ORDER BY o.main_id DESC LIMIT 5");

	$list = $db->getArray("SELECT f.view_id, f.main_id, f.sent_html, f.sent_cmt, f.sent_ttl, f.parent_id, f.sent_date, f.status_id, u.level_id, u.id_id, u.u_alias, u.account_date, u.u_sex, u.u_birth, u.u_picid, u.u_picvalid, u.u_picd FROM s_f f LEFT JOIN s_user u ON u.id_id = f.sender_id AND u.status_id = '1' WHERE f.parent_id = '".$res['top_id']."' AND f.status_id = '1' ORDER BY f.main_id");
	$page = 'read';
	$menu = array(
		'start' => array('forum.php', 'start'),
		'list' => array('forum_list.php?id='.$r['main_id'], secureOUT($r['main_ttl'])),
		'read' => array('forum_read.php?id='.$res['top_id'], 'läs tråd')
	);

	require(DESIGN."head.php");
?>
	<div id="mainContent">
		<div class="subHead">forum</div><br class="clr"/>
		
		<table summary="" cellspacing="0" style="margin-bottom: 20px;">
			<tr>
				<td style="width: 157px;"><a href="<?=l('forum','list', $r['main_id'])?>" class="bld"><img src="<?=$config['web_root'].'_objects/'.$r['main_id']?>.jpg" alt="" onerror="this.src = '<?=$config['web_root']?>_objects/forum_nopic.jpg';" width="157" height="74" /></a></td>
				<td class="pdg"><?=secureOUT($r['main_cmt'])?></td>
			</tr>
			<tr><td><?='<h4>'.secureOUT($r['main_ttl']).'</h4><b>'.$c.'</b> tråd'.(($c != '1')?'ar':'').'<br /><b>'.($d+$c).'</b> inlägg'?></td></tr>
		</table>

		<?
			if ($user->vip_check(VIP_LEVEL1)) {
				if (spyActive($res['main_id'], 'f')) {
					makeButton(false, 'goLoc(\''.l('forum', 'read', $res['main_id']).'&unsubscribe'.'\')', 'icon_settings.png', 'sluta spana');
				} else {
					makeButton(false, 'goLoc(\''.l('forum', 'read', $res['main_id']).'&subscribe'.'\')', 'icon_settings.png', 'spana');
				}
			}
			
			//makeButton(false, 'makeForumAns('.$res['main_id'].')', 'icon_forum.png', 'skriv inlägg');
		?>
		<input type="button" class="btn2_min" onclick="makeForumAns('<?=$res['main_id']?>')" value="Skriv inlägg"/>
		
		<br/><br/><br/>

		<div class="bigHeader"><?=makeMenu($page, $menu)?></div>
		<div class="bigBody">
<?
	array_unshift($list, $res);
	$odd = true;
	$i = 0;
	$id = 0;
	foreach($list as $row) {
		$odd = !$odd;
		echo '
			<table summary="" cellspacing="0" style="width: 594px;'.($odd?'':' background: #ecf1ea;').'">
			'.($i && $row['sent_ttl']?'<tr><td colspan="2" style="padding-bottom: 0;" class="em pdg">Svar på <b>'.secureOUT($row['sent_ttl']).'</b></td></tr>':(!$i?'<tr><td colspan="2" style="padding-bottom: 0;" class="pdg"><h3>'.secureOUT($row['sent_ttl']).'</h3></td></tr>':'')).'
			<tr><td class="pdg" style="width: 55px;" rowspan="2"><a name="R'.$row['main_id'].'"></a>'.$user->getimg($row['id_id'].$row['u_picid'].$row['u_picd'].$row['u_sex'], $row['u_picvalid']).'</td>
			<td class="pdg" style="width: 544px; padding-left: 0;"><h5 class="l">'.$user->getstring($row, '', array('noimg' => 1)).' - '.nicedate($row['sent_date']).'</h5><br class="clr" />
			'.($row['status_id'] != '1'?'<em>Meddelande raderat</em>':secureOUT($row['sent_cmt'], 1)).'
			</td></tr>
			<tr><td class="btm rgt pdg">';
		//echo '<input type="button" onclick="makeForumAns('.$row['main_id'].')" class="btn2_sml" value="svara" />';
		if ($isAdmin) {
			//echo '<input type="button" class="btn2_med" value="släck ner allt" style="margin-left: 5px;" onclick="document.location.href = \''.l('forum','read',$res['main_id']).'&amp;item='.$row['main_id'].'&amp;off='.$row['main_id'].'\';"/>';
			echo '<input type="button" class="btn2_sml" value="radera" style="margin-left: 5px;" onclick="document.location.href = \''.l('forum','read',$res['main_id']).'&amp;item='.$row['main_id'].'&amp;delete='.$row['main_id'].'\';"/>';
			//echo '<input type="button" class="btn2_med" value="'.(($row['view_id'] == '2')?'tänd upp':'släck ner').'" style="margin-left: 5px;" onclick="document.location.href = \''.l('forum','read',$res['main_id']).'&amp;item='.$row['main_id'].'&amp;u='.$row['id_id'].'&amp;'.(($row['view_id'] == '2')?'show':'hide').'='.$row['main_id'].'\';"/>';
		} else {
			/*
			if ($l['id_id'] == $row['id_id'] && $row['view_id'] == '1' && ($i || !$i && !count($list))) {
				echo '<input type="button" class="btn2_sml" value="radera" style="margin-right: 5px;" onclick="if(confirm(\'Säker ?\')) document.location.href = \''.l('forum','read',$res['main_id']).'&amp;item='.$row['main_id'].'&amp;del='.$row['main_id'].'\';"/>';
			}
			*/
		}
		echo '</td></tr>';
		echo '</table>';
		$i++;
		$id = $row['main_id'];
	}
	if(isset($_GET['showlast']) && $id) echo '<script type="text/javascript">document.location.hash = \'#R'.$id.'\';</script>';
?>
			</div>
		</div>
<?
	require(DESIGN."foot.php");
?>