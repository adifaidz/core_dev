<?
	require_once('search_users.fnc.php');

	$result = performSearch($id);

	require(DESIGN.'head.php');
?>

<div id="bigContent">
	
	<img src="/_gfx/ttl_search.png" alt="S�k anv�ndare"/><br/><br/>
<?
	makeButton(!$id, 'goLoc(\''.l('list', 'users').'\')', 'icon_profile.png', 'senast inloggade');
	makeButton($id=='1', 'goLoc(\''.l('list', 'users', '1').'\')', 'icon_profile.png', 'visa online');
	makeButton($id=='F', 'goLoc(\''.l('list', 'users', 'F').'\')', 'icon_profile.png', 'tjejer online');
	makeButton($id=='M', 'goLoc(\''.l('list', 'users', 'M').'\')', 'icon_profile.png', 'killar online');
?>
<br/><br/><br/>

	<div class="centerMenuBodyWide">
		<form name="search" action="<?=l('list', 'users', '2')?>" method="post">
		<input type="hidden" name="do" value="1" />
		<input type="hidden" name="p" value="0" />

		<table cellspacing="0" class="mrg" summary=""><tr>
			<td style="padding-right: 30px;">alias:<br /><input type="text" class="txt" style="width: 170px;" name="alias" value="<?=secureOUT($result['alias'])?>" /></td>
			<td style="padding-right: 30px;">bor i:<br />
				<select class="txt" name="lan" onchange="this.form.submit();" style="width: 170px;">
				<option value="0">alla l�n</option><? optionLan($result['lan']) ?>
				</select><br />

				<select name="ort"<?=(empty($result['lan'])?' disabled="disabled"':'')?> style="width: 170px;" class="txt" onchange="this.form.submit();">
				<option value="0">i alla orter</option><? optionOrt($result['lan'], $result['ort']) ?>
				</select>
			</td>
			<td style="padding-right: 30px;">alternativ:<br />
				<input type="checkbox" class="chk" value="1" name="pic" id="pic1" onclick="this.form.submit();"<?=($result['pic'])?' checked="checked"':'';?>/><label for="pic1"> har bild</label><br />
				<input type="checkbox" class="chk" value="1" name="online" id="online1" onclick="this.form.submit();"<?=($result['online'])?' checked="checked"':'';?>/><label for="online1"> �r online</label><br />
				<input type="checkbox" class="chk" value="6" name="l_6" id="l_6" onclick="this.form.submit();"<?=($result['level'] == '6')?' checked="checked"':'';?>/><label for="l_6"> VIP</label>
			</td>
			<td style="padding-right: 30px;">k�n:<br />
				<input type="radio" class="chk" name="sex" value="0" id="s_0" onclick="this.form.submit();"<?=(!$result['sex'])?' checked="checked"':'';?>/><label for="s_0"> alla</label><br />
				<input type="radio" class="chk" name="sex" value="M" id="s_m" onclick="this.form.submit();"<?=($result['sex'] == 'M')?' checked="checked"':'';?>/><label for="s_m"> killar</label><br />
				<input type="radio" class="chk" name="sex" value="F" id="s_f" onclick="this.form.submit();"<?=($result['sex'] == 'F')?' checked="checked"':'';?>/><label for="s_f"> tjejer</label>
			</td>
			<td>�lder:<br />
				<select name="age" class="txt" onchange="this.form.submit();">
				<option value="0"<?=(!$result['age'])?' selected':'';?>>alla �ldrar</option>
				<option value="1"<?=($result['age'] == '1')?' selected':'';?>>mellan 0-20 �r</option>
				<option value="2"<?=($result['age'] == '2')?' selected':'';?>>mellan 21-25 �r</option>
				<option value="3"<?=($result['age'] == '3')?' selected':'';?>>mellan 26-30 �r</option>
				<option value="4"<?=($result['age'] == '4')?' selected':'';?>>mellan 31-35 �r</option>
				<option value="5"<?=($result['age'] == '5')?' selected':'';?>>mellan 36-40 �r</option>
				<option value="6"<?=($result['age'] == '6')?' selected':'';?>>mellan 41-45 �r</option>
				<option value="7"<?=($result['age'] == '7')?' selected':'';?>>mellan 46-50 �r</option>
				<option value="8"<?=($result['age'] == '8')?' selected':'';?>>mellan 51-55 �r</option>
				<option value="9"<?=($result['age'] == '9')?' selected':'';?>>56 �r och �ldre</option>
				</select>
			</td>
		</tr></table>
		<input type="submit" class="btn2_sml r" value="s�k" /><br class="clr" />
		</form>
	</div>

	<div>
		<? if(count($result['res'])) dopaging($result['paging'], 'javascript:changePage(\'', '\');', 'biggest', STATSTR, 0); ?>
	</div>
	<table cellspacing="0" summary=""<?=($result['pic'])?'':' width="783"';?>>
<?
	if(empty($result['res']) || !count($result['res'])) {
		echo '<tr><td class="spac pdg cnt" width="786">Inga listade.</td></tr>';
	} else {
		$i = 0;
		$nl = true;
		if($result['pic']) {
			foreach($result['res'] as $row) {
				if($nl) echo (($i)?'</tr>':'').'<tr>';
				$i++;
				echo '<td style="padding: 0 0 6px '.((!$nl)?'5':'0').'px;">'.$user->getimg($row['id_id'].$row['u_picid'].$row['u_picd'].$row['u_sex'], $row['u_picvalid'], 0, array('text' => $row['u_alias'].' '.$sex[$row['u_sex']].$user->doage($row['u_birth'], 0))).'</td>';
				if($i % 16 == 0) $nl = true; else $nl = false;
			}
		} else {
			$i = 0;
			foreach($result['res'] as $row) {
				$i++;
				$gotpic = false;
				echo '
					<tr'.(($gotpic)?' onmouseover="this.className = \'t1\'; dumblemumble(\''.$row['id_id'].$row['u_picid'].$row['u_picd'].$i.'\', 1);" onmouseout="this.className = \'\'; mumbledumble(\''.$row['id_id'].$row['u_picid'].$row['u_picd'].$i.'\', 0, 1);"':' onmouseover="this.className = \'t1\';" onmouseout="this.className = \'\';"').'>
						<td class="cur pdg spac" width="250">'.$user->getstring($row, '', array('icons' => 1)).'</td>
						<td class="cur pdg spac nobr" onclick="goUser(\''.$row['id_id'].'\');">'.ucwords(strtolower($row['u_pstort'].($row['u_pstlan']?', ':'').$row['u_pstlan'])).'</td>
						<td class="cur pdg spac cnt" onclick="goUser(\''.$row['id_id'].'\');">'.(($gotpic)?'<img src="./_img/icon_gotpic.gif" alt="har bild" style="margin-top: 2px;" />':'&nbsp;').'</td>
						<td class="cur pdg spac rgt nobr" onclick="goUser(\''.$row['id_id'].'\');">'.(($user->isonline($row['account_date']))?'<span class="on">online ('.nicedate($row['lastlog_date'], 2).')</span>':'<span class="off">'.nicedate($row['lastonl_date'], 2).'</span>').'</td>
					</tr>';
				if($gotpic) echo '<tr id="pic:'.$i.'" style="display: none;"><td colspan="2">'.$user->getimg($row['id_id'].$row['u_picid'].$row['u_picd'].$row['u_sex'], $row['u_picvalid']).'</td></tr>';
			}
		}
	}
?>
	</table>
<?
	if(count($result['res'])) dopaging($result['paging'], 'javascript:changePage(\'', '\');', 'biggest', '&nbsp;', 0);
?>
</div>
<?
	include(DESIGN.'foot.php');
?>