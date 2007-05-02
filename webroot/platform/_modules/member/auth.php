<?
class user_auth {

	var $sql, $t, $user;

	function __construct() {
		global $sql, $t, $user;
		$this->sql = $sql;
		$this->t = $t;
		$this->user = $user;
	}

	function login_data($result) {
		cookieSET("a65", $result[1]);
		$this->user->counterIncrease('login', $result[0]);
		# enable for hidden login
		#if($this->user->level($result[2], 5) && $this->user->getinfo($result[0], 'hidden_login')) {
		#	$_SESSION['c_h'] = true;
		#} else {
		$res = now();
		#if($this->user->level($result[2], 5) && $this->user->getinfo($result[0], 'hidden_slogin')) {
		#} elseif($result[4])
		$this->sql->queryInsert("REPLACE INTO {$this->t}userlogin SET id_id = '".secureINS($result[0])."', sess_date = '".$res."'");
		$this->sql->queryInsert("INSERT INTO {$this->t}usersess SET id_id = '".secureINS($result[0])."', sess_ip = '".secureINS($_SERVER['REMOTE_ADDR'])."', sess_id = '".secureINS($this->sql->gc())."', sess_date = NOW(), type_inf = 'i'");
		$this->sql->queryUpdate("UPDATE {$this->t}user SET lastlog_date = '".$res."', lastonl_date = '".$res."', account_date = '".$res."' WHERE id_id = '".secureINS($result[0])."'");
		$this->sql->queryUpdate("REPLACE INTO {$this->t}useronline SET account_date = '".$res."', id_id = '".secureINS($result[0])."', u_sex = '".$result[6]."'");
		$_SESSION['data'] = array('u_pst' => $result[9], 'u_pstlan_id' => $result[8], 'lastlog_date' => $res, 'lastonl_date' => $res, 'u_regdate' => $result[12], 'u_picvalid' => $result[15], 'status_id' => $result[5], 'id_id' => $result[0], 'u_alias' => $result[1], 'u_sex' => $result[6], 'u_picid' => $result[3], 'u_picd' => $result[4], 'u_birth' => $result[7], 'level_id' => $result[2]);
		$this->user->counterSet($result[0]);
		$_SESSION['data']['account_date'] = $res;
		$_SESSION['data']['cachestr'] = $this->user->cachestr($result[0]);
		#}
		#if(!empty($_POST['redir']) && is_md5($_POST['redir']))
		#	reloadACT('frameset.php?redir='.$_POST['redir']);
		#else {
		#if($result[1] == 'demo2') splashACT('Du har adminr�ttigheter. Kontot �r endast till f�r presentation och allting loggas. V�nta...', 'frameset.php');
		#}
	}

	function notify_user($id, $msg, $alias = '') {
		if(DEFAULT_USER == $id) return;
		$msg = sprintf($msg, $alias);
		$this->sql->queryInsert("INSERT INTO {$this->t}userchat SET user_id = '".$id."', sender_id = '".DEFAULT_USER."', sent_cmt = '".secureINS($msg)."', sent_date = NOW()");
	}

	function login($a, $p) {
			$online = gettxt('stat_online');
			$online = explode(':', $online);
			$online = intval($online[0]);
			$result = $this->sql->queryLine("SELECT id_id, u_alias, level_id, u_picid, u_picd, status_id, u_sex, u_birth, u_pstlan_id, CONCAT(u_pstort, ', ', u_pstlan) as u_pst, lastlog_date, lastonl_date, u_regdate, u_pass, location_id, u_picvalid FROM {$this->t}user WHERE u_alias = '".secureINS($a)."' LIMIT 1");
			if(!empty($result) && count($result)) {
				if($online > MAXIMUM_USERS && $result[2] == '1') errorACT('Det �r �ver '.MAXIMUM_USERS.' inloggade. Du m�ste vara VIP f�r att kunna logga in nu.', l('main', 'index'));
				if($result[5] == '1' || $result[5] == '4') {
					if($result[13] === $p) {
						$this->login_data($result);
						$this->user->setRelCount($result[0]);
						if(!empty($_POST['redir'])) {
							$this->notify_user($result[0], gettxt('moved_login'), $result[1]);
							echo '<center><a href="'.l('main', 'start').'"><img border=0 src="http://www.360sundsvall.nu/mellansida" /></a><script type="text/javascript">window.setTimeout(\'document.location.href = "'.l('main', 'start').'"\', 6000);</script></center>';
							die();
						}
						reloadACT(l('main', 'start'));
					} else {
						$this->sql->queryInsert("INSERT INTO {$this->t}usersess SET id_id = '".secureINS($result[0])."', sess_ip = '".secureINS($_SERVER['REMOTE_ADDR'])."', sess_id = '".secureINS($this->sql->gc())."', sess_date = NOW(), type_inf = 'f'");
						#if(isset($_GET['s']))
						#	splashACT('Felaktigt alias eller l�senord.', './');
						#else
						errorACT('Felaktigt alias eller l�senord.', '1'.l('main', 'start'));
					}
				} elseif($result[5] == '3') {
						errorACT('Du �r blockerad.');
				} else {
					#if(isset($_GET['s']))
					#	splashACT('Felaktigt alias eller l�senord.', './');
					#else
					errorACT('Felaktigt alias eller l�senord.', '1'.l('main', 'start'));
				}
			} else {
				$result = $this->sql->queryLine("SELECT id_id, u_alias, level_id, u_picid, u_picd, status_id, u_sex, u_birth, u_pstlan_id, CONCAT(u_pstort, ', ', u_pstlan) as u_pst, lastlog_date, lastonl_date, u_regdate, u_pass, location_id FROM {$this->t}user WHERE u_email = '".secureINS($a)."' LIMIT 1");
				if(!empty($result) && count($result) && !empty($result[5]) && $result[5] == '1') {
				if($online > MAXIMUM_USERS && $result[2] == '1') errorACT('Det �r �ver '.MAXIMUM_USERS.' inloggade. Du m�ste vara VIP f�r att kunna logga in nu.', l('main', 'index'));
					if($result[13] === $p) {
						$this->login_data($result);
						reloadACT(l('main', 'start'));
					} else {
						$this->sql->queryInsert("INSERT INTO {$this->t}usersess SET id_id = '".secureINS($result[0])."', sess_ip = '".secureINS($_SERVER['REMOTE_ADDR'])."', sess_id = '".secureINS($this->sql->gc())."', sess_date = NOW(), type_inf = 'f'");
						#if(isset($_GET['s']))
						#	splashACT('Felaktigt alias eller l�senord.', './');
						#else
						errorACT('Felaktigt alias eller l�senord.', '1'.l('main', 'start'));
					}
				} else {
					errorACT('Felaktigt alias eller l�senord.', '1'.l('main', 'start'));
/*
					$result = $this->sql->queryLine("SELECT id_id, u_alias, level_id, u_pass FROM {$this->t}user WHERE u_alias = '".secureINS($a)."' AND status_id = 'F' LIMIT 1");
					if(!empty($result) && count($result)) {
						if($result[3] === $p) {
							$_SESSION['c_i'] = $result[0];
							$_SESSION['c_a'] = $result[1];
							$_SESSION['c_l'] = $result[2];
							reloadACT('settings_activate.php');
						} else {
							$this->sql->queryInsert("INSERT INTO {$this->t}usersess SET id_id = '".secureINS($result[0])."', sess_ip = '".secureINS($_SERVER['REMOTE_ADDR'])."', sess_id = '".secureINS($this->sql->gc())."', sess_date = NOW(), type_inf = 'f'");
							errorACT('Felaktigt alias eller l�senord.', 'frameset.php');
						}
					} else {
						if(isset($_GET['s']))
							errorACT('Felaktigt alias eller l�senord.', './');
						else
							errorACT('Felaktigt alias eller l�senord.', 'frameset.php');
					}
*/
				}
			}
	}

	function logout($empty = false) {
		if(!empty($_SESSION['data']['id_id'])) {
			if(!$empty) {
				$this->sql->queryInsert("INSERT INTO {$this->t}usersess SET id_id = '".@secureINS($_SESSION['data']['id_id'])."', sess_ip = '".secureINS($_SERVER['REMOTE_ADDR'])."', sess_id = '".secureINS($this->sql->gc())."', sess_date = NOW(), type_inf = 'o'");
				$this->sql->queryUpdate("UPDATE {$this->t}user SET lastonl_date = account_date, account_date = '".date("Y-m-d H:i:s", strtotime("-1 HOUR"))."' WHERE id_id = '".secureINS($_SESSION['data']['id_id'])."' LIMIT 1");
				$this->sql->queryUpdate("UPDATE {$this->t}useronline SET account_date = '".date("Y-m-d H:i:s", strtotime("-1 HOUR"))."' WHERE id_id = '".secureINS($_SESSION['data']['id_id'])."' LIMIT 1");
			}
			$_SESSION['data']['id_id'] = false;
		}
		unset($_SESSION['data']['id_id']); unset($_SESSION['data']); unset($_SESSION);
		if(!$empty)
			reloadACT(l('main', 'index'));
		else
			reloadACT(l('main', 'index', '1'));
	}

}
	$user_auth = new user_auth();
?>