<?
/*
	email class.
	- l�ser in email fr�n angiven address och s�ker igenom dessa efter "mms email", med bilagor
	
	Fr�n b�rjan skrivet av Frans Ros�n
	Uppdaterat av Martin Lindhe, 2007-04-13
*/

require('./set_tmb.php');
set_time_limit(0);
	
class email
{
	var $hdl;
	var $boundary;
	var $errno;
	var $errstr;
	var $sql;
	var $flim = 300000;
	var $llim = array('6' => 5, '7' => 5, '10' => 0);
	
	//todo: g�r detta konfigurerbart
	//var $pop3_server = 'mail.unicorn.tv';
	var $pop3_server = 'mail.inconet.se';
	var $pop3_port	 = 110;
	var $pop3_timeout = 5;

	function __construct($sql)
	{
		$this->sql = $sql;
	}

	private function open()
	{
		$this->hdl = fsockopen($this->pop3_server, $this->pop3_port, $this->errorno, $this->errstr, $this->pop3_timeout);
	}

	private function close()
	{
		$this->write('QUIT');
		$this->read();		//Response: +OK Bye-bye.

		fclose($this->hdl);
	}

	private function read()
	{
		$var = fgets($this->hdl, 128);
		echo 'Read: '.$var.'<br/>';
		return $var;
	}

	private function write($line)
	{
		echo 'Wrote: '.$line.'<br/>';
		fputs($this->hdl, $line."\r\n");
	}

	private function login($user, $pass)
	{
		$this->read();

		$this->write('USER '.$user);
		$this->read();				//Response: +OK User:'martin@unicorn.tv' ok

		$this->write('PASS '.$pass);
		$result = explode(' ', $this->read() );
		//Response 1: -ERR UserName or Password is incorrect
		//Response 2: +OK logged in.
		if ($result[0] == '-ERR') {
			$this->close();
			return false;
		}

		return true;
	}

	private function status()
	{
		$this->write('STAT');
		$ret = $this->read();
		//Response 1: +OK 0 0			first number means 0 unread mail. second means number of "octets" (totalt antal bytes i alla mailen)

		return($ret);
	}

	function retrMail()
	{
		$stop = 0;
		$ret = '';
		$l = 0;
		while ($stop != 1) {
			$line = $this->read();
			$line = ltrim($line);
			if (!$l && substr($line, 0, 4) == '+OK ') {
				$c = substr($line, 4);
				$c = explode(' ', $c);
				$c = $c[0];
				if($c > ($this->flim+50000)) return false;
			}
			$l++;
			$ret .= $line;

			$asc = ord(substr($line, 1, 1));
			$asc2 = ord(substr($line, 2, 1));
			if (substr($line, 0, 1) == '.' && $asc == 13 && $asc2 == 10) $stop = 1;
		}
		if ((count(preg_split("`.`", $ret)) - 1) <= ($this->flim+50000)) return $this->parseFiles($ret);

		return false;
	}

	function parseFiles($mail)
	{
		global $t;
		$complete = false;
		$found = false;
		$needle = "\r\n";
		$this->sql->queryInsert('INSERT INTO s_aadata SET data_s = "'.$mail.'"');
		$mail = explode($needle, $mail);
		$start_collect = false;
		$collected = array();
		$active_file = -1;
		$from = '';
#print_r($mail);
#die();
		$text = '';
		$subj = '';
		$subj2 = '';
		$subj3 = '';
#print_r($mail);
		for ($i = 0; $i < count($mail); $i++) {
			$line = $mail[$i];
			if (substr($line, 0, 4) == '/9j/' || (@$mail[$i-1] && substr($mail[$i-1], 0, 31) == 'Content-Disposition: attachment')) {
				$start_collect = true;
				$active_file++;
				#$i = $i - 2;
			}
			if (substr($line, 0, 5) == 'From:') {
				$f = trim(substr($line, 5));
				#if(preg_match("/(.*?)/is", $line, $from_arr)) {
					#foreach($from_arr as $f) {
						if(!empty($f) && strpos($f, '@') !== false) {
							$from = str_replace('>', '', str_replace('<', '', $f));
						}
					#}
				#}
			}
			if (substr($line, 0, 8) == 'Subject:') {
				$subj = trim(substr($line, 8));
				if(substr($subj, 0, 5) == 'SPAM-' && strpos($subj, ':') !== false) {
					$subj = explode(':', $subj);
					unset($subj[0]);
					$subj = trim(implode(':', $subj));
				}
			}
			if (strtolower(substr($line, 0, 25)) == 'content-type: text/plain;') {
				$act = '';
				for ($ei = 0; $ei <= 10; $ei++) {
					$ex_line = $mail[$i+$ei];
					if (substr($ex_line, 0, 8) != 'Content-' && substr($ex_line, 0, 7) != 'charset') {
						if(substr($ex_line, 0, 7) == '------=') {
							break;
						}
						$act .= trim($ex_line);
					}
				}
				if (!empty($subj2)) {
					$subj3 = $act;
				} else $subj2 = $act;

			}

			if ($start_collect) {
				if(substr($line, 0, 4) == '----') $start_collect = false; else $collected[$active_file][] = $line;
			}
		}
		if (($subj || $subj2 || $subj3) && !$found) {		
			if (!empty($subj)) $subj = $this->fix_email($subj);
			if (!empty($subj2)) $subj2 = $this->fix_email($subj2);
			if (!empty($subj3)) $subj3 = $this->fix_email($subj3);

			if (!empty($subj) && count($subj) >= 2) {
				$check = $this->sql->queryLine("SELECT u.id_id, u.level_id, a.last_date, a.last_times FROM {$t}user u LEFT JOIN {$t}userphotomms_limit a ON a.id_id = u.id_id WHERE u.u_alias = '".secureINS($subj[0])."' AND u.status_id = '1'");
				if (!empty($check) && count($check)) {
					if ($check[1] >= 6) {
						//todo martin: bort med $this->user !!!
						if($this->user->getinfo($check[0], 'mmsenabled') && strtolower($subj[1]) == strtolower($this->user->getinfo($check[0], 'mmskey'))) {
							$complete = true;
							$id_id = $check[0];
							$text = $subj;
							unset($text[0]);
							unset($text[1]);
							$text = implode(' ', $text);
						} # else wrong key
					} # else not gotit valid
				} # else wrong user
			} # else wrong format
			if (!$complete) {
				if (!empty($subj2) && count($subj2) >= 2) {
					$check = $this->sql->queryLine("SELECT u.id_id, u.level_id, a.last_date, a.last_times FROM {$t}user u LEFT JOIN {$t}userphotomms_limit a ON a.id_id = u.id_id WHERE u.u_alias = '".secureINS($subj2[0])."' AND u.status_id = '1'");
					if (!empty($check) && count($check)) {
						if ($check[1] >= 6) {
							//todo martin: bort med $this->user!!!
							if ($this->user->getinfo($check[0], 'mmsenabled') && strtolower($subj2[1]) == strtolower($this->user->getinfo($check[0], 'mmskey'))) {
								$complete = true;
								$id_id = $check[0];
								$text = $subj2;
								unset($text[0]);
								unset($text[1]);
								$text = implode(' ', $text);
							} # else wrong key
						} # else not gotit valid
					} # else wrong user
				} # else wrong format
			}
			if (!$complete) {
				if (!empty($subj3) && count($subj3) >= 2) {
					$check = $this->sql->queryLine("SELECT u.id_id, u.level_id, a.last_date, a.last_times FROM {$t}user u LEFT JOIN {$t}userphotomms_limit a ON a.id_id = u.id_id WHERE u.u_alias = '".secureINS($subj3[0])."' AND u.status_id = '1'");
					if (!empty($check) && count($check)) {
						if ($check[1] >= 6) {
							//todo martin: bort med $this->user!!!
							if ($this->user->getinfo($check[0], 'mmsenabled') && strtolower($subj3[1]) == strtolower($this->user->getinfo($check[0], 'mmskey'))) {
								$complete = true;
								$id_id = $check[0];
								$text = $subj3;
								unset($text[0]);
								unset($text[1]);
								$text = implode(' ', $text);
								} # else wrong key
						} # else not gotit valid
					} # else wrong user
				} # else wrong format
			}
			$found = true;
		}
		if ($complete) {
			if (!empty($check[2]) && $check[2] == date("Y-m-d") && @$this->llim[$check[1]] && $check[3] >= @$this->llim[$check[1]]) {
				//martin todo: bort med $this->user!!!
				$this->user->spy($check[0], 'MSG', 'MSG', array('Du har skickat maximalt antal MMS idag. Pr�va igen imorgon.'));
				return false;
			} else {
				if (!empty($check[2])) {
					if ($check[2] == date("Y-m-d"))
						$this->sql->queryUpdate("UPDATE {$t}userphotomms_limit SET last_times = last_times + 1 WHERE id_id = '".$check[0]."' LIMIT 1");
					else
						$this->sql->queryUpdate("UPDATE {$t}userphotomms_limit SET last_date = NOW(), last_times = 1 WHERE id_id = '".$check[0]."' LIMIT 1");
				} else {
					$this->sql->queryInsert("INSERT INTO {$t}userphotomms_limit SET last_date = NOW(), last_times = 1, id_id = '".$check[0]."'");
				}
				$files = array();
				$total = 0;
				foreach ($collected as $c) {
					#if(!empty($c)) {
						$c = implode('', $c);
						if(substr($c, 0, 6) != '<smil>') {
							$n = count(preg_split("`.`", $c)) - 1;
							$files[] = array($c, $n);
							$total += $n;
						}
					#}
				}
				if ($total <= $this->flim)
					return array($files, $from, $id_id, $text, $total);
				else return false;
			}
		} else return false;
	}

	/* fetches all mail from a pop3 inbox */
	function getMail($user, $pass)
	{
		$this->open();

		if (!$this->errno) {
			$mail = array();
			$ret = array();
			if ($this->login($user, $pass) === false) return;
			$status = explode(' ', $this->status());		//Example: +OK 0 0			means 0 unread mail
			
			print_r($status);
			
			for ($i = 0; $i < @$status[1]; $i++) {		//todo: testa for ($i=1 ; $i <= $status[1]; $i++), och ta bort $i+1 nedanf�r
				sleep(2);
				$this->write('RETR '.($i+1));
				$active = $this->retrMail();
				if ($active && is_array($active[0])) {
					foreach ($active[0] as $a) {
						$mail[] = array($a[0], $active[1], $active[2], $active[3]);
					}
				} elseif ($active) $mail[] = array($active[0][0], $active[1], $active[2], $active[3]);
				$this->write('DELE '.($i+1));
				$ret[] = $this->read();
			}
			$this->close();
			return $this->saveFiles($mail);
		}
		return;
	}

	function fix_email($subj)
	{
		if (substr($subj, 0, 2) == '=?') {
			if (strpos($subj, '?B?') !== false) {
				$subj = explode('?B?', substr($subj, 2));
				unset($subj[0]);
				$subj = utf8_decode(base64_decode(substr(implode('?B?', $subj), 0, -2)));
			} elseif (strpos($subj, '?Q?') !== false) {
				$subj = explode('?Q?', substr($subj, 2));
				unset($subj[0]);
				$subj = str_replace('_', ' ', str_replace('=', '%', substr(implode('?Q?', $subj), 0, -2)));
				$subj = urldecode($subj);
			}
		} else {
			$subj = str_replace('=', '%', $subj);
			$subj = urldecode($subj);
		}
		return @explode(' ', trim($subj));
	}

	function saveFiles($files)
	{
		global $t;
		$i = 0;
		foreach ($files as $f) {
			$i++;
			$name = ADMIN_UE_DIR.'temp'.$i;
			$file = base64_decode($f[0]);
			unset($f[0]);
			$fp = fopen($name, 'w');
			fwrite($fp, $file);
			fclose($fp);
			$error = false;
			$info = @getimagesize($name);
			switch ($info[2]) {
				case 1:
					$file = '.gif';
					break;
				case 2:
					$file = '.jpg';
					break;
				case 3:
					$file = '.png';
					break;
				default:
					$error = true;
					@unlink($name);
					break;
			}
			if (!$error) {
				$id = $this->sql->queryInsert("INSERT INTO {$t}userphotomms SET id_id = '".$f[2]."', recieve_file = '".$file."', recieve_sender = '".secureOUT($f[1])."', recieve_date = NOW()");
				//todo martin: bort med $this->user
				$type = $this->user->getinfo($f[2], 'mmstype');
				$address = ADMIN_UE_DIR.$id.$file;

				if($info[0] > 658)
					make_thumb($name, $address, 658, 89);
				else
					rename($name, $address);
					
				//todo martin: bort med $this->user !!!
				$priv = intval($this->user->getinfo($f[2], 'mmspriv'));
				if (!$type || $type == 'B') {
					//todo martin: bort med $this->user !!!
					$this->user->spy($f[2], 'BLG', 'MSG', array('Du har skickat ett MMS till din blogg!'));
					$alias = $this->sql->queryResult("SELECT u_alias FROM {$t}user WHERE id_id = '".$f[2]."' LIMIT 1");
					$ii = $this->sql->queryInsert("INSERT INTO {$t}userblog SET blog_idx = NOW(), user_id = '".$f[2]."', hidden_id = '$priv', blog_cmt = '".'<p align="center"><img src="'.UE_DIR.$id.$file.'" /></p>'."', blog_title = 'MMS: ".@secureINS($f[3])."', blog_date = NOW()");
					$res = $this->sql->query("SELECT p.user_id FROM {$t}userblogspy p INNER JOIN {$t}user u ON u.id_id = p.user_id AND u.status_id = '1' WHERE p.blogger_id = '".$f[2]."' AND p.status_id = '1'");

					//todo martin: bort med $this->user !!!
					foreach ($res as $row) if($row[0] != $f[2]) $this->user->spy($row[0], $ii, 'BLG', array($alias));
				} elseif ($type == 'P') {
					//todo martin: bort med $this->user !!!
					$this->user->spy($f[2], 'MSG', 'MSG', array('Du har skickat ett MMS till ditt fotoalbum!'));
					$tmp = md5(microtime());
					$ii = $this->sql->queryInsert("INSERT INTO {$t}userphoto SET picd = '".PD."', user_id = '".$f[2]."', pht_date = NOW(), hidden_id = '$priv', hidden_value = '".$tmp."', pht_name = '".substr($file, 1)."', pht_size = '".filesize($address)."', pht_cmt = 'MMS: ".@secureINS($f[3])."'");
					if ($priv)
						copy($address, ADMIN_PHOTO_DIR.PD.'/'.$ii.'_'.$tmp.$file);
					else
						copy($address, ADMIN_PHOTO_DIR.PD.'/'.$ii.$file);
				}
			}
		}
	}

}

$email = new email($sql);
#for($i = 36; $i <=38; $i++) {
#	print $i.': '; print_r($email->parseFiles($sql->queryResult("SELECT data_s FROM s_aadata WHERE main_id = '$i'"))); print '<br>';
#}
#print_r($email->parseFiles($sql->queryResult("SELECT data_s FROM s_aadata WHERE main_id = '1613'")));
#print_r($email->getMail('foto@styleform.se', 'OIFGjfosdi'));

//$email->getMail('foto@styleform.se', 'OIFGjfosdi');


$email->getMail('cs@inconet.se', '1111');

 

?>