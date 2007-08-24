<?
	require_once('config.php');

	$id = $user->id;
	if (!empty($_GET['id']) && is_numeric($_GET['id'])) $id = $_GET['id'];

	$his = false;
	$allowed = true;

	if (!empty($_GET['del_msg']) && is_numeric($_GET['del_msg'])) {
		if (gbDelete($_GET['del_msg'])) {
			header('Location: '.$_SERVER['PHP_SELF'].'?id='.$id);
			die;
		}
	}

	if (!empty($_GET['key']) && is_numeric($_GET['key'])) {
		//visa historik mellan userid 'id' och userid 'key'
		$his = true;
		if ($id != $user->id && !$user->isAdmin) {
			//tillåt bara admins se historik mellan två olika användare
			die('inte ok');
		} else {
			$limit = 50;
			$c_his = $_GET['key'];
			$id1 = $id;
			$id2 = $_GET['key'];
		}
		$paging = paging(1, $limit);
		$paging['co'] = 1;
		$res = gbHistory($id1, $id2, $paging['slimit'], $paging['limit']);
	} else {
		$offset = $user->getinfo($id, 'gb_offset');
		$paging = paging(@$_GET['p'], 20);
		$paging['co'] = gbCountMsgByUserId($id);
		$ext = $paging['p'] * $paging['limit'];
		$offset = ($offset + $paging['limit']) - $ext;
		$res = gbList($id, $paging['slimit'], $paging['limit']);
	}
	if ($user->id == $id) {
		gbMarkUnread();
	}

	$action = 'gb';
	require(DESIGN.'head_user.php');

	if ($his) {
		?><div class="subHead">gästbok - historik</div><br class="clr"/><?
	} else {
		?><div class="subHead">gästbok</div><br class="clr"/><?
	}

	if ($user->id != $id) {
		makeButton(false, 'makeGb(\''.$id.'\')',	'icon_gb.png', 'skriv i gästboken');
	}
	echo '<br/><br class="clr"/>';


	$odd = true;
	if(!empty($res) && count($res)) {
		dopaging($paging, l('user', 'gb', $s['id_id']).'p=', '', 'med', ((!$his)?STATSTR:'<a href="'.l('user', 'gb', $s['id_id']).'">tillbaka</a>'));
	
		foreach ($res as $val) {
			$prv = ($val['private_id'])?1:0;
			$show_answer = (!$val['is_answered'])?false:true;
			if($user->id.$user->id == $val['user_id'].$val['sender_id']) {
				$arr = array(0, 0, 1, 1, 1, 0, 'skriv');
			} elseif($user->id == $val['user_id']) {
				if($his) $arr = array(1, 0, 1, 1, 1, 1, 'svara');
				else $arr = array(1, 1, 1, 1, 1, 1, 'svara');
			} elseif($user->id == $val['sender_id']) {
				if($his) $arr = array(0, 0, 1, 1, 1, 0, '');
				else $arr = array(0, 1, 1, 1, 1, 0, '');
			} else {
				$arr = array(1, 0, 0, (($prv)?0:1), 1, 0, 'skriv');
				$show_answer = true;
			}
			//if($isOk) $arr[1] = 1;
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
			} else {
				$extra = false;
			}
			$odd = !$odd;
	
			echo '<table summary="" cellspacing="0" class="msgList'.($odd?'':' msgListEven').'">';
			echo '<tr><td class="pdg msgListImage" rowspan="2">'.$user->getimg($val['id_id']).'</td><td class="pdg"><h5 class="l">'.((!$his)?'#'.$offset--.'&nbsp;':'').' '.$user->getstring($val, '', array('noimg' => 1)).' - '.nicedate($val['sent_date']).'</h5><div class="r">'.((!$val['user_read'])?' <b>(oläst inlägg)</b>':((!$show_answer)?' [obesvarat inlägg]':'')).(($prv)?' <span class="off"'.(($isAdmin && !$arr[3])?'':'').'>[privat inlägg]</span>':'').'</div><br class="clr" />';
			echo (($arr[3])?(($val['sent_html'])?(safeOUT($val['sent_cmt'])):secureOUT($val['sent_cmt'], 1)):'<span class="em"'.(($isAdmin)?' id="msg:'.$val['main_id'].'"':'').'>Privat inlägg</span>');
			echo '</td></tr>';
			echo '<tr><td class="btm rgt pdg">';
	
			if ($arr[2] && $user->id == $id) echo '<input type="button" class="btn2_min" onclick="if(confirm(\'Säker ?\')) goLoc(\'user_gb.php?id='.$id.'&del_msg='.$val['main_id'].'\');" value="radera" />';
			if ($arr[1] && $user->id == $id || $user->vip_check(VIP_LEVEL1)) {
				echo '<input type="button" class="btn2_min" onclick="goLoc(\'user_gb.php?id='.$id.'&key='.($val['sender_id'] == $id? $val['user_id'] : $val['sender_id']).'\');" value="historia" />';
			}
			if ($arr[4]) echo '<input type="button" class="btn2_min" onclick="goLoc(\'user_gb.php?id='.$val['id_id'].'\');" value="gästbok " />';
			if ($arr[0]) echo '<input type="button" class="btn2_min" onclick="makeGb(\''.$val['id_id'].'\''.($arr[5]?', \'&a='.$val['main_id'].'\'':'').');" value="'.$arr[6].'" />';

			echo '</td></tr>';
			echo '</table>';
		}
		dopaging($paging, l('user', 'gb', $s['id_id']).'p=', '', 'med');
	} else {
		echo '<table summary="" cellspacing="0" class="msgList">';
		echo '<tr><td class="cnt">Inga gästboksinlägg.</td></tr>';
		echo '</table>';
	}
	
	if ($user->id != $id) {
		makeButton(false, 'makeGb(\''.$id.'\')',	'icon_gb.png', 'skriv i gästboken');
	}

	require(DESIGN.'foot_user.php');
?>
