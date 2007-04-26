<?
	require_once('relations.fnc.php');

	if(isset($_GET['create'])) {
		include('relations_create.php');
		die;
	}

	//detta �ndrar typ av relations-f�rfr�gan f�r p�g�ende f�rfr�gningar (t.ex fr�n "Granne" till "Sambo")
	if(!empty($_POST['ins_rel']) && !$own) {
		$error = sendRelationRequest($s['id_id'], $_POST['ins_rel']);
		if ($error === true) {
			errorACT('Du har nu �ndrat typ av f�rfr�gan.', l('user', 'relations'));
			die;
		}
	}

	if (!empty($_POST['d']) || !empty($_GET['d']))
	{
		$d = !empty($_POST['d']) ? $_POST['d'] : $_GET['d'];

		if (removeRelation($d) === true) reloadACT(l('user', 'relations'));
	}
	else if(!empty($_GET['a']))
	{
		$error = acceptRelationRequest($_GET['a']);
		if ($error === true) reloadACT(l('user', 'relations'));
		errorACT($error, l('user', 'relations'));
	}

	//Detta �r m�jligheter att v�lja hur kompislistan ska sorteras. Funktionen exponeras f�r stunden inte p� citysurf
	$thisord = 'A';
	if(!empty($_POST['ord']) && ($_POST['ord'] == 'A' || $_POST['ord'] == 'L' || $_POST['ord'] == 'R' || $_POST['ord'] == 'O')) {
		$thisord = $_POST['ord'];
	}
	if($thisord == 'L') {
		$page = 'login';
		$ord = 'u.lastonl_date DESC';
	} elseif($thisord == 'R') {
		$page = 'rel';
		$ord = 'rel.rel_id ASC';
	} elseif(!$thisord || $thisord == 'O') {
		$page = 'onl';
		$ord = 'isonline DESC';
	} else {
		$page = 'alpha';
		$ord = 'u.u_alias ASC';
	}
	
	$view = false;
	if(!empty($_GET['key']) && is_numeric($_GET['key']) && $own) {
		$view = $_GET['key'];
	}

	$blocked = false;
	if($own && isset($_GET['blocked'])) {
		$blocked = true;
		if(isset($_GET['del'])) {
			unblockRelation($_GET['del']);
			errorACT('Nu har du slutat att blockera personen.', l('user', 'relations').'&amp;blocked');
		}
		$res = getBlockedRelations();
	} else { 
		$paging = paging(@$_GET['p'], 50);
		$paging['co'] = getRelationsCount($s['id_id']);
		$res = getRelations($s['id_id'], $ord, $paging['slimit'], $paging['limit']);
	}
	$is_blocked = $blocked;
	$page = 'relations';

	require(DESIGN.'head_user.php');

	if ($own && !$blocked) {		
		//paus �r f�rfr�gningar som andra skickat till dig
		$paus = getRelationRequestsToMe();
		
		//wait �r f�rfr�gningar du v�ntar p� svar p�
		$wait = getRelationRequestsFromMe();
		require("relations_user.php");
	}

	$page = 'friends';
	$blocked = $is_blocked;
	if($blocked) $page = 'blocked';
	$menu = array('friends' => array(l('user', 'relations'), 'v�nner'), 'blocked' => array(l('user', 'relations').'&amp;blocked', 'ov�nner'));
?>

<img src="/_gfx/ttl_friends.png" alt="V�nner"/><br/><br/>

<?=($own?'<div class="centerMenuHeader">'.makeMenu($page, $menu).'</div>':'<div class="centerMenuHeader">v�nner</div>')?>
<div class="centerMenuBodyWhite">
	<? if(!$blocked) dopaging($paging, l('user', 'relations', $s['id_id']).'p=', '&amp;ord='.$thisord, 'med', STATSTR); ?>
	<table summary="" cellspacing="0" width="586">
	<?
	if(!empty($res) && count($res)) {
		if(!$blocked) {
			$i = 0;
			foreach($res as $row) {
				$i++;
				$gotpic = ($row['u_picvalid'] == '1')?true:false;
				echo '
				<tr'.(($gotpic && $view != $row['main_id'])?' onmouseover="this.className = \'t1\'; dumblemumble(\''.$row['id_id'].$row['u_picid'].$row['u_picd'].$i.'\', 2);" onmouseout="this.className = \'\'; mumbledumble(\''.$row['id_id'].$row['u_picid'].$row['u_picd'].$i.'\', 0, 2);"':' onmouseover="this.className = \'t1\';" onmouseout="this.className = \'\';"').'>
					<td class="spac pdg"><a name="R'.$row['main_id'].'"></a>'.$user->getstring($row).'</td>
					<td class="cur spac pdg" onclick="goUser(\''.$row['id_id'].'\');">'.secureOUT($row['rel_id']).'</td>
					<td class="cur pdg spac cnt">'.(($row['u_picvalid'] == '1')?'<img src="./_img/icon_gotpic.gif" alt="har bild" style="margin-top: 2px;" />':'&nbsp;').'</td>
					<td class="cur spac pdg rgt" onclick="goUser(\''.$row['id_id'].'\');">'.(($user->isonline($row['account_date']))?'<span class="on">online ('.nicedate($row['lastlog_date']).')</span>':'<span class="off">'.nicedate($row['lastonl_date']).'</span>').'</td>
					'.(($own)?'<td class="spac rgt pdg_tt"><a href="'.l('user', 'relations', $s['id_id'], $row['main_id']).'#R'.$row['main_id'].'"><img src="'.OBJ.'icon_change.gif" alt="" title="�ndra" style="margin-bottom: -4px;" /></a> - <a class="cur" onclick="if(confirm(\'S�ker ?\')) goLoc(\''.l('user', 'relations', $row['id_id'], '0').'&amp;d='.$row['id_id'].'\');"><img src="'.OBJ.'icon_del.gif" alt="" title="Radera" style="margin-bottom: -4px;" /></a></td>':'').'
				</tr>';
		
				if($view == $row['main_id']) {
					//Visar "�ndra typ av relation"
					echo '<tr><td colspan="5" class="pdg">';
					echo '<form name="do" action="'.l('user', 'relations', $row['id_id']).'" method="post">';
					echo '<select name="ins_rel" class="txt">';
					foreach($rel as $r) {
						$sel = ($r[1] == $row['rel_id'])?' selected':'';
						echo '<option value="'.$r[0].'"'.$sel.'>'.secureOUT($r[1]).'</option>';
					}
					echo '</select>';
					echo '<input type="submit" class="br" value="spara" style="margin-left: 10px;"></form>';
					echo '</td></tr>';
				} else if($gotpic) {
					echo '<tr id="m_pic:'.$i.'" style="display: none;"><td colspan="2">'.$user->getphoto($row['id_id'].$row['u_picid'].$row['u_picd'], $row['u_picvalid'], 0, 0, '', ' ').'<span style="display: none;">'.$row['id_id'].$row['u_picid'].$row['u_picd'].$i.'</span></td></tr>';
				}
			}
		} else {
		  foreach($res as $row) {
				echo '
				<tr>
					<td class="spac pdg">'.$user->getstring($row, '', array('nolink' => 1)).'</td>
					<td class="spac pdg rgt">'.nicedate($row['activated_date']).'</td>
					<td class="spac pdg rgt"><a class="cur" onclick="return confirm(\'S�ker ?\')" href="'.l('user', 'relations').'&amp;blocked&amp;del='.$row['id_id'].'"><img src="'.OBJ.'icon_del.gif" title="Avblockera" style="margin-bottom: -4px;" /></a></td>
				</tr>';
		  }
		}
		
	} else {
		echo '<tr><td class="spac pdg cnt">Inga '.($blocked?'ov�nner':'v�nner').'.</td></tr>';
	}
	?>
	</table>
</div>

<?
	require(DESIGN.'foot_user.php');
?>
