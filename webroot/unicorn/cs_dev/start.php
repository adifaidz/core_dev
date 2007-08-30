<?
	require_once('config.php');
	$user->requireLoggedIn();

	require(DESIGN.'head.php');

	echo '<div id="bigContent">';

	echo '<table cellpadding="0" cellspacing="0" border="0"><tr><td width="602">';

		//Listar de senaste bloggarna
		$q = 'SELECT b.*,u.* FROM s_userblog b '.
				'LEFT JOIN s_user u ON (b.user_id=u.id_id) '.
				'WHERE hidden_id="0" ORDER BY b.blog_date DESC LIMIT 5';
		$res = $db->getArray($q);
		
		if (count($res)) {
			echo '<div style="float: left">';
			echo '<div class="mediumHeader">senaste bloggarna</div>';
			echo '<div class="mediumBody">';
			foreach($res as $row) {
				$title = stripslashes($row['blog_title']);
				if (!$title) $title = 'Ingen rubrik';
				if (strlen($title) >= 20) $title = substr($title, 0, 20).'[...]';

				echo '<a href="user_blog_read.php?id='.$row['id_id'].'&amp;n='.$row['main_id'].'">'.$title.'</a> av '.$user->getstring($row, '', array('icons' => 1)).'<br/>';
			}
			echo '</div></div>';
		}

		//Listar de senaste blog-kommentarerna
		$res = $db->getArray('SELECT * FROM s_userblogcmt WHERE status_id = "1" ORDER BY c_date DESC LIMIT 5');
		
		if (count($res)) {
			echo '<div style="float: right">';
			echo '<div class="mediumHeader">senaste kommentarerna</div>';
			echo '<div class="mediumBody">';
			foreach($res as $row) {
				$msg = $row['c_msg'];
				if (strlen($msg) >= 14) $msg = substr($msg, 0, 12).'[...]';
				echo '<a href="user_blog_read.php?id='.$row['user_id'].'&amp;n='.$row['blog_id'].'">'.$msg.'</a> av '.$user->getstring($row['id_id'], '', array('icons' => 1)).'<br/>';
			}
			echo '</div></div>';
		}
		echo '<br class="clr" /><br/>';

		//Listar de senaste inloggade
		$res = $db->getArray('SELECT u.* FROM s_userlogin s INNER JOIN s_user u ON u.id_id = s.id_id AND u.status_id = "1" ORDER BY s.main_id DESC LIMIT 11');
		if (count($res)) {
			echo '<div style="clear: both">';
			echo '<div class="bigHeader">senast inloggade</div>';
			echo '<div class="bigBody">';
			foreach($res as $row) {
				echo $user->getimg($row['id_id'], 0, $user->getministring($row));
			}
			echo '</div>';
			echo '</div><br/>';
		}

		//Listar de senaste galleribilderna
		$q = 'SELECT main_id, user_id, picd, pht_name, pht_cmt FROM s_userphoto WHERE status_id = "1" AND hidden_id = "0" AND pht_name != "" ORDER BY main_id DESC LIMIT 11';
		$res = $db->getArray($q);
		if (count($res)) {
			echo '<div class="bigHeader">senaste galleribilder</div>';
			echo '<div class="bigBody">';
			foreach($res as $row) {
				echo '<a href="gallery_view.php?id='.$row['user_id'].'&n='.$row['main_id'].'">';
				echo '<img alt="'.secureOUT($row['pht_cmt']).'" src="'.$config['web_root'].USER_GALLERY.$row['picd'].'/'.$row['main_id'].'-tmb.'.$row['pht_name'].'" style="margin-right: 10px;" />';
				echo '</a>';
			}
			echo '</div><br/>';
		}

		//Visa den senaste krönikan
		$res = $db->getOneRow('SELECT * FROM s_editorial WHERE status_id = "1" ORDER BY ad_date DESC LIMIT 1');
		if(count($res)) {
			echo '<div class="bigHeader">krönika</div>';
			echo '<div class="bigBody">';
			echo nl2br(stripslashes($res['ad_cmt']));
			echo '</div>';
		}

	echo '</td><td width="10">&nbsp;</td><td>';
	
	$val = mt_rand(1, 2);
	if ($val == 1) {
		//förortish-banner
?>
<div id="flash_ad"></div>
<script type="text/javascript">
show_swf("_gfx/ban/forortish.swf", 'flash_ad', 170, 350);
</script>
<?
	} else {
?>
<script type="text/javascript">
var bnum=new Number(Math.floor(99999999 * Math.random())+1);
document.write('<SCR'+'IPT LANGUAGE="JavaScript" ');
document.write('SRC="http://servedby.advertising.com/site=737464/size=160600/bnum='+bnum+'/optn=1"></SCR'+'IPT>');
</script>
<?
	}

	echo '</td></tr></table>';

	echo '</div>';	//id="mainContent"

	require(DESIGN.'foot.php');
?>
