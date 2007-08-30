<?
	require_once('config.php');
	$user->requireLoggedIn();

	$length = array('1' => 10, '3' => 10, '5' => 20, '6' => 40, '8' => 80, '10' => 0);
	$lim = $length[$_SESSION['data']['level_id']];
	$photo_limit = 510;
	$NAME_TITLE = 'LADDA UPP FOTO';

	/*
	if($lim && $sql->queryResult("SELECT COUNT(*) as count FROM s_userphoto WHERE user_id = '".$l['id_id']."' AND status_id = '1'") >= $lim) {
		popupACT('Du har laddat upp maximalt antal foton ( '.$lim.'st )<br />Du måste uppgradera för att kunna ladda upp fler.');
	}
	*/
	if (!empty($_POST['ins_msg']) && !empty($_FILES['ins_file']) && empty($_FILES['ins_file']['error'])) {

		$_POST['ins_msg'] = trim($_POST['ins_msg']);
		if (empty($_POST['ins_msg'])) popupACT('Felaktig beskrivning.');

		/*
		if($lim) {
			$rest = $lim - $sql->queryResult("SELECT COUNT(*) as count FROM s_userphoto WHERE user_id = '".secureINS($l['id_id'])."' AND status_id = '1'");
			if($rest <= 0) popupACT('Du har laddat upp maximalt antal foton.<br />Du måste uppgradera för att kunna ladda upp fler.');
		}
		*/
		$p = $_FILES['ins_file']['tmp_name'];
		$p_name = $old_name = $_FILES['ins_file']['name'];
		$p_size = $_FILES['ins_file']['size'];
		if (verify_uploaded_file($p_name, $p_size)) {

			$p_name = explode('.', $p_name);
			$p_name = strtolower($p_name[count($p_name)-1]);
			$error = 0;
			$unique = md5(microtime()).'.';
			$u2 = md5(microtime().'skitjgaa').'.';
			$file = USER_GALLERY.'/'.$unique.$p_name;
			$file2 = USER_GALLERY.'/'.$u2.$p_name;
			if (!move_uploaded_file($p, $file)) $error++;
			# doResize
			if (!$error) {
				#kolla sajsen
				$done = false;
				$p_s = getimagesize($file);
				if($p_s[0] > $photo_limit) {
					if(make_thumb($file, $file2, $photo_limit, 89)) $done = true;
				} else {
					#if(make_whole($file, $file2, $p_s[0], $p_s[1], 80)) $done = true;
					if(rename($file, $file2)) $done = true;
				}
				if($done) {
					$prv = !empty($_POST['ins_priv']) ? '1' : '0';
					if($prv) {
						$un = md5(microtime().'ghrghrhr');
						$res = $db->insert('INSERT INTO s_userphoto SET user_id = '.$user->id.', old_filename="'.$db->escape($old_name).'", status_id = "1", hidden_id = "1", hidden_value = "'.$db->escape($un).'", pht_name = "'.$db->escape($p_name).'", pht_size = '.filesize($file2).', pht_cmt = "'.$db->escape(substr($_POST['ins_msg'], 0, 40)).'", picd = "'.PD.'", pht_rate = "0", pht_date = NOW()');
					} else {
						$res = $db->insert('INSERT INTO s_userphoto SET user_id = '.$user->id.', old_filename="'.$db->escape($old_name).'", status_id = "1", pht_name = "'.$db->escape($p_name).'", pht_size = '.filesize($file2).', pht_cmt = "'.$db->escape(substr($_POST['ins_msg'], 0, 40)).'", picd = "'.PD.'", pht_rate = "0", pht_date = NOW()');
					}
					if ($res) {
						@unlink($file);
						@rename($file2, USER_GALLERY.PD.'/'.$res.($prv?'_'.$un:'').'.'.$p_name);
						@make_thumb(USER_GALLERY.PD.'/'.$res.($prv?'_'.$un:'').'.'.$p_name, USER_GALLERY.PD.'/'.$res.'-tmb.'.$p_name, '100', 89);
						spyPost($user->id, 'g', $_SESSION['data']['u_alias']);

					} else {
						@unlink($file);
						@unlink($file2);
						popupACT('Felaktigt format, storlek eller bredd & höjd.', '', 'user_gallery.php?id='.$user->id, 1000);
					}
				}
			} else {
				popupACT('Felaktigt format, storlek eller bredd & höjd.', '', 'user_gallery.php?id='.$user->id, 1000);
			}
		} else {
			popupACT('Fotot är alldeles för stort (Max 1.2 MB per foto). Du måste ändra storleken på bilden för att kunna ladda upp.', '', 'user_gallery.php?id='.$user->id, 1000);
		}
		$user->counterIncrease('gal', $user->id);
		if(!empty($_GET['do'])) {
			$msg = 'Uppladdad.<br/>Filen ligger längst ner i listan!';
			$name = secureOUT(substr($_POST['ins_msg'], 0, 40));
			$file = ($prv)?'/'.USER_GALLERY.PD.'/'.$res.'_'.$un.'.'.$p_name:'/'.USER_GALLERY.PD.'/'.$res.'.'.$p_name;

			$script = "<script type=\"text/javascript\">
			var name = 'NY! ".str_replace('"', '&qout;', '#'.$res.' - '.$name).(($prv)?' [privat]':'')."';
			var file = '".$file."';
			if(window.opener && window.opener.document && window.opener.document.getElementById('photo_list')) {
				window.opener.addselOption(name, file);
			}
			</script>";
			popupACT($msg.$script, '', '', 3000);
		} else {
			popupACT('Uppladdad!', '', 'gallery_view.php?ìd='.$user->id.'&n='.$res, 1000);
		}
	}

	require(DESIGN.'head_popup.php');
?>
<script type="text/javascript">
	var sub_dis = false;
	function ActivateByKey(e) {
		if(!e) var e=window.event;
		if (!sub_dis && e['keyCode'] == 27) window.close();
	}
document.onkeydown = ActivateByKey;
var allowedext = Array("jpg", "jpeg", "gif", "png");
var error = false;
var error_image = '1x1.gif';
var oldval = '';
function checkSize(s_obj) {
	if(s_obj.src != error_image) {

		if(s_obj.width > <?=$photo_limit?>) {
			document.getElementById('ins_chk').innerHTML = '<b class="red">Fotot kommer att förminskas.</b><br /><br />';
		} else {
			document.getElementById('ins_chk').innerHTML = '';
		}
		//obj.style.display = 'none';
	}
}

function validateUpl(tForm) {
	if(tForm.ins_file.value.length <= 0) {
		alert('Felaktigt fält: Sökväg');
		return false;
	}
	if(tForm.ins_msg.value.length <= 0) {
		alert('Felaktigt fält: Beskrivning');
		tForm.ins_msg.focus();
		return false;
	}
	tForm.sub.disabled = true;
	sub_dis = false;
	return true;
}

</script>
<body style="border: 6px solid #FFF;">
<form name="msg" action="<?=$_SERVER['PHP_SELF']?><?=(!empty($_GET['do']))?'?do='.secureOUT($_GET['do']):'';?>" method="post" enctype="multipart/form-data" onsubmit="if(validateUpl(this)) { return true; } else return false;">
		<div class="smallWholeContent cnti mrg">
			<div class="smallHeader">ladda upp till galleri</div>
			<div class="smallBody pdg_t">
				bläddra till fil:<br />
				<input type="file" name="ins_file" style="width: 160px; height: 22px; line-height: 14px;" class="txt" accept="image/jpeg, image/gif, image/png, image/pjpeg"/><br />
				beskrivning:<br />
				<input type="text" name="ins_msg" style="width: 160px;" class="txt"/>
				<input type="checkbox" class="chk" name="ins_priv" id="ins_priv"<? if (!empty($_GET['priv'])) echo ' checked="checked"'; ?> value="1"/><label for="ins_priv"> Galleri X (för vänner)</label>
				<input type="submit" class="btn2_sml r" name="sub" value="skicka!" /><br class="clr"/>
			</div>
		</div>
</form>
</body>
</html>
