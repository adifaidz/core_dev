<?
	$type = '';
	if (!empty($_GET['type'])) $type = $_GET['type'];

	require_once('config.php');

	$result = performSearch($type, 0, 0, 200);

	require(DESIGN.'head.php');
?>

<div id="bigContent">
	
	<div class="subHead">leta</div><br class="clr"/>
<?
	makeButton(!$type, "document.location='list_users.php'", 'icon_profile.png', 'senast inloggade');
	makeButton($type=='1', "document.location='list_users.php?type=1'", 'icon_profile.png', 'visa online');
	makeButton($type=='F', "document.location='list_users.php?type=F'", 'icon_profile.png', 'tjejer online');
	makeButton($type=='M', "document.location='list_users.php?type=M'", 'icon_profile.png', 'killar online');
?>
<br/><br/><br/>

	<div class="centerMenuBodyWide">
		<form name="search" action="<?=$_SERVER['PHP_SELF']?>?type=2" method="post">
		<input type="hidden" name="do" value="1" />
		<input type="hidden" name="p" value="0" />

		<table cellspacing="0" class="mrg" summary=""><tr>
			<td style="padding-right: 30px;">
				alias:<br />
				<input type="text" class="txt" style="width: 170px;" name="alias" value="<?=secureOUT($result['alias'])?>" /><br/>

				ålder:<br />
				<select name="age" class="txt" onchange="this.form.submit();">
				<option value="0"<?=(!$result['age'])?' selected':'';?>>alla åldrar</option>
				<option value="1"<?=($result['age'] == '1')?' selected':'';?>>mellan 0-20 år</option>
				<option value="2"<?=($result['age'] == '2')?' selected':'';?>>mellan 21-25 år</option>
				<option value="3"<?=($result['age'] == '3')?' selected':'';?>>mellan 26-30 år</option>
				<option value="4"<?=($result['age'] == '4')?' selected':'';?>>mellan 31-35 år</option>
				<option value="5"<?=($result['age'] == '5')?' selected':'';?>>mellan 36-40 år</option>
				<option value="6"<?=($result['age'] == '6')?' selected':'';?>>mellan 41-45 år</option>
				<option value="7"<?=($result['age'] == '7')?' selected':'';?>>mellan 46-50 år</option>
				<option value="8"<?=($result['age'] == '8')?' selected':'';?>>mellan 51-55 år</option>
				<option value="9"<?=($result['age'] == '9')?' selected':'';?>>56 år och äldre</option>
				</select>

			</td>
			<td style="padding-right: 30px;">bor i:<br />
				<select class="txt" name="lan" onchange="this.form.submit();" style="width: 170px;">
				<option value="0">alla län</option><? optionLan($result['lan']) ?>
				</select><br />

				<select name="ort"<?=(empty($result['lan'])?' disabled="disabled"':'')?> style="width: 170px;" class="txt" onchange="this.form.submit();">
				<option value="0">i alla orter</option><? optionOrt($result['lan'], $result['ort']) ?>
				</select>
			</td>
			<td style="padding-right: 30px;">alternativ:<br />
				<input type="checkbox" class="chk" value="1" name="pic" id="pic1" onclick="this.form.submit();"<?=($result['pic'])?' checked="checked"':'';?>/><label for="pic1"> har bild</label><br />
				<input type="checkbox" class="chk" value="1" name="online" id="online1" onclick="this.form.submit();"<?=($result['online'])?' checked="checked"':'';?>/><label for="online1"> är online</label><br />
				<!-- <input type="checkbox" class="chk" value="6" name="l_6" id="l_6" onclick="this.form.submit();"<?=($result['level'] == '6')?' checked="checked"':'';?>/><label for="l_6"> VIP</label> -->
			</td>
			<td style="padding-right: 30px;">kön:<br />
				<input type="radio" class="chk" name="sex" value="0" id="s_0" onclick="this.form.submit();"<?=(!$result['sex'])?' checked="checked"':'';?>/><label for="s_0"> alla</label><br />
				<input type="radio" class="chk" name="sex" value="M" id="s_m" onclick="this.form.submit();"<?=($result['sex'] == 'M')?' checked="checked"':'';?>/><label for="s_m"> killar</label><br />
				<input type="radio" class="chk" name="sex" value="F" id="s_f" onclick="this.form.submit();"<?=($result['sex'] == 'F')?' checked="checked"':'';?>/><label for="s_f"> tjejer</label>
			</td>
		</tr></table>
		<input type="submit" class="btn2_sml" value="sök" /><br class="clr" /><br/><br/>
		</form>
	</div>

	<div>
		<?
		echo 'Visar '.count($result['res']).' användare';
		 if(count($result['res'])) dopaging($result['paging'], 'javascript:changePage(\'', '\');', 'biggest', STATSTR, 0); ?>
	</div>
	<table cellspacing="1" summary="">
<?
	if(empty($result['res']) || !count($result['res'])) {
		echo '<tr><td class="spac pdg cnt">Inga listade.</td></tr>';
	} else {
		$i = 0;
		$nl = true;
		if($result['pic']) {
			foreach($result['res'] as $row) {
				//if (empty($row['u_alias'])) continue;	//skippa icke-existerande users
				if($nl) echo (($i)?'</tr>':'').'<tr>';
				$i++;
				echo '<td>';
				echo $user->getimg($row['id_id'].$row['u_picid'].$row['u_picd'].$row['u_sex'], $row['u_picvalid'], 0, array('text' => $row['u_alias'].' '.$sex[$row['u_sex']].$user->doage($row['u_birth'], 0)));
				echo '</td>';
				if($i % 11 == 0) $nl = true; else $nl = false;
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