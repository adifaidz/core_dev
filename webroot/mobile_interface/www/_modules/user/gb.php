<?
	include('gb.fnc.php');
	$his = false;
	#$isOk = $user->level($l['level_id'], 2);
	#$allowed = array('1' => '30', '3' => '60', '5' => '120');
	#if(!$allowed = @$allowed[$l['level_id']]) $allowed = false;
	#if($allowed) {
	#	$deadline = date("Y-m-d H:i:s", strtotime('-'.$allowed.' DAYS'));
	#}
	$allowed = true;
	$page = 'gb';

	if(!empty($_GET['del_msg']) && is_numeric($_GET['del_msg'])) {
		if(gbDelete($_GET['del_msg'])) {
			reloadACT(l('user', 'gb', $s['id_id']));
			exit;
		}
	} else if(!empty($_GET['key']) && is_numeric($_GET['key'])) {
		$his = true;
		if($isOk) {
			$limit = 50;
			$c_his = $_GET['key'];
			$id1 = $s['id_id'];
			$id2 = $_GET['key'];
		} else {
			$limit = 10;
			$c_his = $l['id_id'];
			$id1 = $s['id_id'];
			$id2 = $l['id_id'];
		}
		$paging = paging(1, $limit);
		$paging['co'] = 1;
		$res = gbHistory($id1, $id2, $paging['slimit'], $paging['limit']);
	} else {
		$offset = $user->getinfo($s['id_id'], 'gb_offset');
		$paging = paging(@$_GET['p'], 20);
		$paging['co'] = gbCountMsgByUserId($s['id_id']);
		$ext = $paging['p'] * $paging['limit'];
		$offset = ($offset + $paging['limit']) - $ext;
		$res = gbList($s['id_id'], $paging['slimit'], $paging['limit']);
	}
	if($own) {
		gbMarkUnread();
	}
	if(!$own) define('U_GBWRITE', 1);
	require(DESIGN.'head_user.php');
	$odd = true;
	if(!empty($res) && count($res)) {
	dopaging($paging, l('user', 'gb', $s['id_id']).'p=', '', 'med', ((!$his)?STATSTR:'<a href="'.l('user', 'gb', $s['id_id']).'">tillbaka</a>'));
	foreach($res as $val) {
	if(($own || $his && $c_his == $l['id_id']) && $allowed && $deadline > $val['sent_date']) { echo '<table summary="" cellspacing="0" style="width: 658px;"><tr><td class="pdg cnt spac">Meddelandena skrivna tidigare �n <b>'.doDate($deadline, 1, 1).'</b> �r nedst�ngda.<br>Du kan v�lja att uppgradera ditt medlemskap om du vill l�sa �ldre inl�gg.</td></tr></table>'; break; }
	$prv = ($val['private_id'])?1:0;
	$show_answer = (!$val['is_answered'])?false:true;
	if($l['id_id'].$l['id_id'] == $val['user_id'].$val['sender_id']) {
		$arr = array(0, 0, 1, 1, 1, 0, 'skriv');
	} elseif($l['id_id'] == $val['user_id']) {
		if($his) $arr = array(1, 0, 1, 1, 1, 1, 'svara');
		else $arr = array(1, 1, 1, 1, 1, 1, 'svara');
	} elseif($l['id_id'] == $val['sender_id']) {
		if($his) $arr = array(0, 0, 1, 1, 1, 0, '');
		else $arr = array(0, 1, 1, 1, 1, 0, '');
	} else {
		$arr = array(1, 0, 0, (($prv)?0:1), 1, 0, 'skriv');
		$show_answer = true;
	}
	if($isOk) $arr[1] = 1;
	if($isAdmin && $s['id_id'] != $val['sender_id'] && !$his) $arr[1] = 1;
	if($isAdmin) $arr[2] = 1;
	if($val['sender_id'] == 'SYS' || empty($val['id_id'])) {
		$arr[0] = 0;
		$arr[1] = 0;
		$arr[4] = 0;
	}
	if($his) $arr[1] = 0;
	if(!empty($val['extra_info'])) {
		$extra = true;
		$extra_id = $val['extra_info'];
	} else $extra = false;
	$odd = !$odd;
echo '
	<table summary="" cellspacing="0" class="msgList'.($odd?'':' msgListEven').'">
	<tr><td class="pdg msgListImage" rowspan="2">'.$user->getimg($val['id_id'].$val['u_picid'].$val['u_picd'].$val['u_sex'], $val['u_picvalid']).'</td><td class="pdg"><h5 class="l">'.((!$his)?'#'.$offset--.'&nbsp;':'').' '.$user->getstring($val, '', array('noimg' => 1)).' - '.nicedate($val['sent_date']).'</h5><div class="r">'.((!$val['user_read'])?' <b>(ol�st inl�gg)</b>':((!$show_answer)?' [obesvarat inl�gg]':'')).(($prv)?' <span class="off"'.(($isAdmin && !$arr[3])?'':'').'>[privat inl�gg]</span>':'').'</div><br class="clr" />
	'.(($arr[3])?(($val['sent_html'])?(safeOUT($val['sent_cmt'])):secureOUT($val['sent_cmt'])):'<span class="em"'.(($isAdmin)?' id="msg:'.$val['main_id'].'"':'').'>Privat inl�gg</span>').'
	</td></tr>
	<tr><td class="btm rgt pdg">'.(($arr[4])?''.(($arr[0])?'<input type="button" class="btn2_min" onclick="makeGb(\''.$val['id_id'].'\''.(($arr[5])?', \'&a='.$val['main_id'].'\'':'').');" value="'.$arr[6].'" />':'').(($arr[1])?'<input type="button" class="btn2_min" onclick="goLoc(\''.l('user', 'gb', ($val['sender_id'] == $s['id_id']?$val['sender_id']:$val['user_id']), ($val['sender_id'] == $s['id_id']?$val['user_id']:$val['sender_id'])).'\');" value="historia" />':'').'<input type="button" class="btn2_min" onclick="goLoc(\''.l('user', 'gb', $val['id_id']).'\');" value="g�stbok " />':'').(($arr[2])?'<input type="button" class="btn2_min" onclick="if(confirm(\'S�ker ?\')) goLoc(\''.l('user', 'gb', $s['id_id']).'del_msg='.$val['main_id'].'\');" value="radera" />':'').'</td></tr>
	</table>
';
	}
	dopaging($paging, l('user', 'gb', $s['id_id']).'p=', '', 'med');
	} else {
echo <<<E
	<table summary="" cellspacing="0" class="msgList">
	<tr><td class="cnt">Inga g�stboksinl�gg.</td></tr>
	</table>
E;
	}
echo '</div>';
	require(DESIGN.'foot_user.php');
	require(DESIGN.'foot.php');
?>
