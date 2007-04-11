<?
	include(CONFIG.'secure.fnc.php');
	$res = $sql->queryLine("SELECT ".CH." main_id, status_id, user_id, pht_name, picd, pht_date, pht_cmt, hidden_id, hidden_value, pht_cmts FROM {$t}userphoto WHERE main_id = '".secureINS($key)."' LIMIT 1", 1);
	if(empty($res) || !count($res) || empty($res['status_id']) || $res['status_id'] != '1' || $s['id_id'] != $res['user_id'] || ($res['hidden_id'] && !$allowed)) {
		errorACT('Felaktigt galleriinl�gg.', l('user', 'gallery', $s['id_id']));
	}
	if(!empty($_GET['del_msg']) && is_numeric($_GET['del_msg'])) {
		$r = $sql->queryLine("SELECT main_id, status_id, user_id, id_id FROM {$t}userphotocmt WHERE main_id = '".secureINS($_GET['del_msg'])."' LIMIT 1");
		if(!empty($r) && count($r) && $r[1] == '1') {
			if($isAdmin || $r[2] == $l['id_id'] || $r[3] == $l['id_id']) {
				$re = $sql->queryUpdate("UPDATE {$t}userphotocmt SET status_id = '2' WHERE main_id = '".secureINS($r[0])."' LIMIT 1");
			}
			if($re) {
				$sql->queryUpdate("UPDATE {$t}userphoto SET pht_cmts = pht_cmts - 1 WHERE main_id = '".$res['main_id']."' LIMIT 1");
			}
			reloadACT(l('user', 'gallery', $s['id_id'], $res['main_id']));
		}
	}

	if(!$own) {
		$hidden = $user->getinfo($l['id_id'], 'hidden_bview');
		if($isAdmin && $res['hidden_id']) {
			$beenhere = true;
		} else {
			if(!$hidden) {
				$visit = @$sql->queryUpdate("REPLACE INTO {$t}userphotovisit SET status_id = '1', visit_date = NOW(), visitor_id = '".secureINS($l['id_id'])."', photo_id = '".secureINS($res['main_id'])."'");
				$beenhere = ($visit != '2')?false:true;
			} else {
				$visit = @$sql->queryUpdate("REPLACE INTO {$t}userphotovisit SET status_id = '2', visit_date = NOW(), visitor_id = '".secureINS($l['id_id'])."', photo_id = '".secureINS($res['main_id'])."'");
				$beenhere = ($visit != '2')?false:true;
			}
		}
		if(!$beenhere) {
			$sql->queryUpdate("UPDATE {$t}userphotovisit SET visit_item = visit_item + 1 WHERE main_id = '".$res['main_id']."' LIMIT 1");
			if(!$hidden) {
				$sql->queryUpdate("UPDATE {$t}userphotovisit SET status_id = '1', visit_date = NOW() WHERE visitor_id = '".secureINS($l['id_id'])."' AND photo_id = '".secureINS($res['main_id'])."' LIMIT 1");
			} else {
				$sql->queryUpdate("UPDATE {$t}userphotovisit SET status_id = '2', visit_date = NOW() WHERE visitor_id = '".secureINS($l['id_id'])."' AND photo_id = '".secureINS($res['main_id'])."' LIMIT 1");
			}
		}
	}

	$page = 'gallery';

	require(DESIGN.'head_user.php');
?>
		<div class="mainHeader2"><h4><?=secureOUT($res['pht_cmt'])?> - publicerad: <?=nicedate($res['pht_date'])?> - <a class="wht" href="<?=l('user', 'gallery', $s['id_id'])?>">tillbaka</a></h4></div>
		<div class="mainBoxed2"><a name="view"></a>
	<div class="cnt">
	<img class="cnti mrg" src="<?='/_input/usergallery/'.$res['picd'].'/'.$res['main_id'].($res['hidden_id']?'_'.$res['hidden_value']:'').'.'.$res['pht_name']?>" border="0" />
	</div>
		</div>
		<div class="mainHeader2"><a name="cmt"></a><h4>kommentarer - <a href="javascript:makePhotoComment('<?=$s['id_id']?>', '<?=$res['main_id']?>');">skriv kommentar</a></h4></div>
		<div class="mainBoxed2">
<?
	$c_paging = paging(@$_GET['p'], 20);
	$c_paging['co'] = $sql->queryResult("SELECT ".CH." COUNT(*) as count FROM {$t}userphotocmt WHERE photo_id = '".$res['main_id']."' AND status_id = '1'");
	#dopaging($c_paging, '', '', 'bigmed', STATSTR);
	$odd = 1;
	$cmt = $sql->query("SELECT ".CH." b.main_id, b.c_msg, b.c_date, b.c_html, b.private_id, u.id_id, u.u_alias, u.u_sex, u.level_id, u.u_birth, u.u_picd, u.u_picid, u.u_picvalid, u.account_date FROM {$t}userphotocmt b LEFT JOIN {$t}user u ON u.id_id = b.id_id AND u.status_id = '1' WHERE b.photo_id = '".$res['main_id']."' AND b.status_id = '1' ORDER BY b.main_id DESC LIMIT {$c_paging['slimit']}, {$c_paging['limit']}", 0, 1);
	if(count($cmt) && !empty($cmt)) { foreach($cmt as $val) {
		$msg_own = ($val['id_id'] == $l['id_id'] || $own || $isAdmin)?true:false;
		$odd = !$odd;
		echo
'
	<table cellspacing="0" style="width: 594px;'.($odd?'':' background: #ecf1ea;').'">
	<tr><td class="pdg" style="width: 55px;" rowspan="2">'.$user->getimg($val['id_id'].$val['u_picid'].$val['u_picd'].$val['u_sex'], $val['u_picvalid']).'</td><td class="pdg"><h5 class="l">'.$user->getstring($val, '', array('noimg' => 1)).' - '.nicedate($val['c_date']).'</h5><div class="r"></div><br class="clr" />
	'.secureOUT($val['c_msg']).'
	</td></tr>
	<tr><td class="btm rgt pdg">&nbsp;'.(($msg_own)?'<a href="'.l('user', 'gallery', $s['id_id'], $res['main_id']).'&del_msg='.$val['main_id'].'" onclick="if(confirm(\'S�ker ?\')) goLoc(\''.l('user', 'gallery', $s['id_id'], $res['main_id']).'del_msg='.$val['main_id'].'\');"><img src="'.OBJ.'icon_del.gif" style="margin-bottom: -2px;" /></a>':'').'</td></tr>
	</table>
';

	} } else { echo '<table cellspacing="0" width="100%"><tr><td class="cnt pdg spac">Inga kommentarer.</td></tr></table>'; }
?>
		</div>
	</div>
<?
	require(DESIGN.'foot_user.php');
	require(DESIGN.'foot.php');
?>