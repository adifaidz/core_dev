<div class="centerMenuBodyWhite">
<?		
	dopaging($paging, l('user', 'gallery', $s['id_id'], '0').'p=', '', 'med', STATSTR);


	$showall = $user->vip_check(VIP_LEVEL1);	//visa alla bilderna ist�llet f�r klickbara rubriker (VIP Delux enbart)

	$view = 0;	//aktuelll bild, f�r showall=true
	$change = false;//redigera bilden?

	echo '<table summary="" cellspacing="0" width="100%">';
	$ti = 0;
	if (!empty($res) && count($res)) {
		if ($all) {
			foreach($res as $row) {
				if(!$row['hidden_id'])
					$file = '/'.USER_GALLERY.$row['picd'].'/'.$row['main_id'].'-tmb.'.$row['pht_name'];
				else
					//$file = '/'.USER_GALLERY.$row['picd'].'/'.$row['main_id'].'_'.$row['hidden_value'].'.'.$row['pht_name'];
					$file = '/'.USER_GALLERY.$row['picd'].'/'.$row['main_id'].'-tmb.'.$row['pht_name'];
				$ti++;
				$cls = ($showall)?'':' spac';
				$url = 'goLoc(first + \''.l('user', 'gallery', $s['id_id'], $row['main_id']).'p='.$paging['p'].'#view'.$row['main_id'].'\');';
				echo '
				<tr>
				<td class="cur'.$cls.' pdg" onclick="'.$url.'"><div style="width: 100%; overflow: hidden;"><a href="'.l('user', 'gallery', $s['id_id'], $row['main_id']).'p='.$paging['p'].'#view" id="lnk'.$row['main_id'].'" class="bld '.(($view && $row['main_id'] == $res[0])?'on up':'up').'">'.secureOUT($row['pht_cmt']).'</a></div></td>
				<td class="cur'.$cls.' pdg cnt nobr" onclick="'.$url.'">'.round($row['pht_size']/1024, 1).'kb</td>
				<td class="cur'.$cls.' pdg" onclick="'.$url.'">'.$row['pht_cmts'].' kommentarer</td>
				<td class="cur'.$cls.' pdg" onclick="'.$url.'">'.secureOUT($row['pht_click']).' visningar</td>
				<td class="cur'.$cls.' pdg rgt nobr" onclick="'.$url.'">'.nicedate($row['pht_date'], 2).'</td>';

				if ($own) {
					echo '<td class="'.$cls.' rgt pdg_tt nobr" width="150">';
					makeButton(false, 'goLoc(\''.l('user', 'gallery', $s['id_id'], $row['main_id']).'c=1#view'.$row['main_id'].'\')', 'icon_gallery.png', '�ndra');
					makeButton(false, 'if(confirm(\'S�ker ?\')) goLoc(\''.l('user', 'gallery', $s['id_id'], '0').'&amp;d='.$row['main_id'].'\');', 'icon_delete.png', 'radera');
					echo '</td>';
				}

				echo '</tr>';
				if ($own && $change && $change == $row['main_id'] && $l) {
					echo '<tr>';
					echo '<td colspan="8" class="pdg wht com_bg">';
					echo '<form name="do" action="'.l('user', 'gallery').'" method="post"><input type="hidden" name="c_id" value="'.$row['main_id'].'"><input type="text" class="txt" name="ins_cmt" onfocus="this.select();" value="'.secureOUT($row['pht_cmt']).'" maxlength="40" style="width: 205px; margin-right: 10px;"><input type="checkbox" class="chk" id="ins_priv" name="ins_priv" value="1"'.(($row['hidden_id'])?' checked':'').'><label for="ins_priv"> Privat foto [endast f�r v�nner]</label> <input type="submit" class="br" value="spara" style="margin-left: 10px;"></form>';
					echo '</td></tr>';
				}
				if ($showall || $view == $row['main_id']) {
					echo '<tr>';
						echo '<td colspan="6" style="padding-bottom: 6px;">';
						echo '<div class="cnt" style="width: 586px; overflow: hidden;"><a href="'.l('user','gallery',$s['id_id'],$row['main_id']).'"><img src="'.$file.'" alt="" onload="if(this.width > 510) this.width = 510;" /></a></div>';
						echo '</td>';
					echo '</tr>';
					echo '<tr>';
						echo '<td align="right" colspan="8" class="pdg wht com_bg">';
						makeButton(false, 'makePhotoComment('.$s['id_id'].','.$row['main_id'].')', 'icon_blog.png', 'skriv kommentar');
						echo '</td>';
					echo '</tr>';
					
				}

			}
			/*
			$res_cmts=$sql->query("SELECT p.main_id, p.user_id, p.c_msg, p.c_date, p.status_id, u.id_id, u.u_alias, u.u_picid, u.u_picd, u.u_picvalid, u.u_birth, u.u_sex, u.account_date, u.level_id FROM s_userphotocmt p LEFT JOIN s_user u ON p.user_id = u.id_id AND u.status_id = '1' WHERE p.photo_id = '".$_GET['key']."' ORDER BY main_id DESC", 0, 1);
			foreach($res_cmts as $line) {
				echo '<tr><td class="spac pdg"><div style="width: 100%; overflow: hidden;">'.$line['u_alias'].'<br />'.$line['u_picd'].'<br />'.$line['u_birth'].'<br />'.$line['u_sex'].'<br />'.$line['c_msg'].'</div></td></tr>';
			}*/
		} else {
			foreach($res as $row) {
				echo '<tr><td class="spac pdg"><div style="width: 100%; overflow: hidden;"><b>[privat]</b></div></td></tr>';
			}
		}
	} else {
		echo '<tr><td class="cnt">Inga foton uppladdade.</td></tr>';
	}
	echo '</table>';
?>
</div><br/>