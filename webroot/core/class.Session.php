<?
/*
	Session class

	Written by Martin Lindhe, 2007
	
	User setting examples:
		$session->save('variablename', 'some random setting to save');
		$kex = $session->read('variablename');
*/

require_once('functions_ip.php');
require_once('functions_settings.php');
require_once('functions_textformat.php');

class Session
{
	private $session_name = 'sid';			//default session name
	private $timeout = 1800;						//max allowed idle time (in seconds) before auto-logout
	private $check_ip = true;						//client will be logged out if client ip is changed during the session, this can be overridden with _POST['login_lock_ip']
	private $sha1_key = 'rpxp8xkeWljo';	//used to further encode sha1 passwords, to make rainbow table attacks harder
	private $allow_registration = true;	//set to false to disallow the possibility to register new users
	private $home_page = '';						//if set, redirects user to this page after successful login

	//Aliases of $_SESSION[] variables
	public $error;
	public $ip;
	public $id;													//current user's user ID
	public $username;
	public $mode;
	public $lastActive;
	public $isAdmin;
	public $isSuperAdmin;
	public $started;		//timestamp of when the session started

	function __construct(array $session_config)
	{
		if (isset($session_config['name'])) $this->session_name = $session_config['name'];
		if (isset($session_config['timeout'])) $this->timeout = $session_config['timeout'];
		if (isset($session_config['check_ip'])) $this->check_ip = $session_config['check_ip'];
		if (isset($session_config['sha1_key'])) $this->sha1_key = $session_config['sha1_key'];
		if (isset($session_config['allow_registration'])) $this->allow_registration = $session_config['allow_registration'];
		if (isset($session_config['home_page'])) $this->home_page = $session_config['home_page'];

		global $db;

		session_name($this->session_name);
		session_start();

		if (!isset($_SESSION['started']) || !$_SESSION['started']) $_SESSION['started'] = time();
		if (!isset($_SESSION['error'])) $_SESSION['error'] = '';
		if (!isset($_SESSION['ip'])) $_SESSION['ip'] = 0;
		if (!isset($_SESSION['id'])) $_SESSION['id'] = 0;
		if (!isset($_SESSION['username'])) $_SESSION['username'] = '';
		if (!isset($_SESSION['mode'])) $_SESSION['mode'] = 0;
		if (!isset($_SESSION['lastActive'])) $_SESSION['lastActive'] = 0;
		if (!isset($_SESSION['isAdmin'])) $_SESSION['isAdmin'] = 0;
		if (!isset($_SESSION['isSuperAdmin'])) $_SESSION['isSuperAdmin'] = 0;

		$this->started = &$_SESSION['started'];
		$this->error = &$_SESSION['error'];
		$this->ip = &$_SESSION['ip'];	//store IP as an unsigned 32bit int
		$this->id = &$_SESSION['id'];	//if id is set, also means that the user is logged in
		$this->username = &$_SESSION['username'];
		$this->mode = &$_SESSION['mode'];
		$this->lastActive = &$_SESSION['lastActive'];
		$this->isAdmin = &$_SESSION['isAdmin'];
		$this->isSuperAdmin = &$_SESSION['isSuperAdmin'];

		if (!$this->ip) $this->ip = IPv4_to_GeoIP($_SERVER['REMOTE_ADDR']);

		//Check for login/logout requests
		if (!$this->id && !empty($_POST['login_usr']) && !empty($_POST['login_pwd']))
		{
			//POST to any page with 'usr' & 'pwd' variables set to log in
			$this->logIn($_POST['login_usr'], $_POST['login_pwd']);

			//See what IP checking policy that will be in use for the session
			if (!empty($_POST['login_lock_ip'])) $this->check_ip = true;
			else $this->check_ip = false; 

			if ($this->home_page) {
				header('Location: '.basename($this->home_page));
				die;
			}
		}

		if (!$this->id && !empty($_POST['register_usr']) && !empty($_POST['register_pwd']) && !empty($_POST['register_pwd2']))
		{
			//todo: l�s och spara register_email
			$check = $this->registerUser($_POST['register_usr'], $_POST['register_pwd'], $_POST['register_pwd2']);
			if (!is_numeric($check)) {
				echo 'Registration failed: '.$check;
				die;
			}
			$this->logIn($_POST['register_usr'], $_POST['register_pwd']);
		}

		if (!$this->id) return;

		//Logged in: Check for a logout request
		if (isset($_GET['logout']))
		{
			//GET to any page with 'logout' set to log out
			$this->logOut();
			header('Location: '.basename($_SERVER['SCRIPT_NAME']));
			die;
		}

		//Logged in: Check if client ip has changed since last request, if so - log user out to avoid session hijacking
		if ($this->check_ip && $this->ip && ($this->ip != IPv4_to_GeoIP($_SERVER['REMOTE_ADDR']))) {
				$this->error = 'Client IP changed';
				$db->log('Client IP changed! Old IP: '.GeoIP_to_IPv4($this->ip).', current: '.GeoIP_to_IPv4($_SERVER['REMOTE_ADDR']));
				$this->logOut();
		}

		//Logged in: Check user activity - log out inactive user
		if ($this->lastActive < (time()-$this->timeout)) {
			$db->log('Session timed out after '.(time()-$this->lastActive).' (timeout is '.($this->timeout).')');
			$this->error = 'Inactivity timeout';
			$this->logOut();
		} else {
			//Update last active timestamp
			$db->query('UPDATE tblUsers SET timeLastActive=NOW() WHERE userId='.$this->id);
			$this->lastActive = time();
		}

	}

	//returns the user ID of the newly created user
	function registerUser($username, $password1, $password2, $userMode = 0)
	{
		global $db, $config;

		if (!is_numeric($userMode)) return false;

		$username = trim($username);
		$password1 = trim($password1);
		$password2 = trim($password2);
		
		if (($db->escape($username) != $username) || ($db->escape($password1) != $password1)) {
			//if someone tries to enter ' or " etc as username/password letters
			//with this check, we dont need to encode the strings for use in sql query
			return 'Username or password contains invalid characters';
		}
		
		if ($password1 && $password2 && ($password1 != $password2)) {
			return 'The passwords doesnt match';
		}
		
		if (strlen($username) < 3) return 'Username must be at least 3 characters long';
		if (strlen($password1) < 4) return 'Password must be at least 4 characters long';

		$q = 'SELECT userId FROM tblUsers WHERE userName="'.$username.'"';
		$checkId = $db->getOneItem($q);
		//echo $checkId;
		if ($checkId) {
			return 'Username already exists';
		}
		
		$q = 'INSERT INTO tblUsers SET userName="'.$username.'",userPass="'.sha1( sha1($this->sha1_key).sha1($password1) ).'",userMode='.$userMode.',timeCreated=NOW()';
		$db->query($q);
		$newUserId = $db->insert_id;
		
		$db->log('User <b>'.$username.'</b> created');

		/* Creates a Inbox and Outbox */
		/*
		if ($config['messages']['enabled']) {
			addUserMessageFolder($db, $newUserId, $config['messages']['folder_inbox'],  MESSAGE_FOLDER_STATIC);
			addUserMessageFolder($db, $newUserId, $config['messages']['folder_outbox'], MESSAGE_FOLDER_STATIC);
		}*/

		return $newUserId;
	}

	function logIn($username, $password)
	{
		global $db;
		
		$enc_username = $db->escape($username);
		$enc_password = sha1( sha1($this->sha1_key).sha1($password) );

		$q = 'SELECT * FROM tblUsers WHERE userName="'.$enc_username.'" AND userPass="'.$enc_password.'"';
		$data = $db->getOneRow($q);
		if (!$data) {
			$this->error = 'Login failed';
			$db->log('Failed login attempt: username '.$enc_username);
			return false;
		}

		$this->error = '';
		$this->username = $enc_username;
		$this->id = $data['userId'];
		$this->mode = $data['userMode'];		//0=normal user. 1=admin, 2=super admin

		if ($this->mode >= 1) $this->isAdmin = 1;
		if ($this->mode >= 2) $this->isSuperAdmin = 1;

		//Update last login time
		$db->query('UPDATE tblUsers SET timeLastLogin=NOW(), timeLastActive=NOW() WHERE userId='.$this->id);
		$this->lastActive = time();

		$db->log('User logged in');

		return true;
	}

	function logOut()
	{
		global $db;

		$db->log('User logged out');
		$db->query('UPDATE tblUsers SET timeLastLogout=NOW()');

		$this->started = 0;
		$this->id = 0;
		$this->ip = 0;
		$this->mode = 0;
		$this->isAdmin = 0;
		$this->isSuperAdmin = 0;
	}
	
	/*Shows a login form with tabs for Register & Forgot password functions */
	//the handling of the result variables are in the __construct function above
	function showLoginForm()
	{
		echo '<div class="login_box">';

		echo '<div id="login_form_layer">';
		echo '<form name="login_form" method="post" action="">';
		if ($this->error) {
			echo '<b>Error: '.$this->error.'</b><br/>';
			$this->error = ''; //remove error message once it has been displayed
		}
		
		//todo: g�r om tabellen till relativt positionerade element utifr�n "login_form_layer"
		echo '<table cellpadding="2">';
		echo '<tr><td>Username:</td><td><input name="login_usr" type="text"/> <img src="/gfx/icon_user.png" alt="Username"/></td></tr>';
		echo '<tr><td>Password:</td><td><input name="login_pwd" type="password"/> <img src="/gfx/icon_keys.png" alt="Password"/></td></tr>';
		echo '</table>';
		echo '<input id="login_lock_ip" name="login_lock_ip" value="1" type="checkbox" checked="checked"/> ';
		echo '<label for="login_lock_ip">Restrict session to current IP</label><br/>';
		echo '<br/>';
		echo '<input type="submit" class="button" value="Log in"/>';
		if ($this->allow_registration) {
			echo '<input type="button" class="button" value="Register" onclick="hide_element_by_name(\'login_form_layer\'); show_element_by_name(\'login_register_layer\');"/>';
			echo '<input type="button" class="button" value="Forgot password" onclick="hide_element_by_name(\'login_form_layer\'); show_element_by_name(\'login_forgot_pwd_layer\');"/>';
		}
		echo '</form>';
		echo '</div>';
		
		if ($this->allow_registration) {
			echo '<div id="login_register_layer" style="display: none;">';
				echo '<b>Register new account</b><br/><br/>';

				echo '<form method="post" action="">';
				echo '<table cellpadding="2">';
				echo '<tr><td>Username:</td><td><input name="register_usr" type="text"/> <img src="/gfx/icon_user.png" alt="Username"/></td></tr>';
				echo '<tr><td>Password:</td><td><input name="register_pwd" type="password"/> <img src="/gfx/icon_keys.png" alt="Password"/></td></tr>';
				echo '<tr><td>Again:</td><td><input name="register_pwd2" type="password"/> <img src="/gfx/icon_keys.png" alt="Repeat password"/></td></tr>';
				echo '<tr><td>E-mail:</td><td><input name="register_email" type="password"/> <img src="/gfx/icon_mail.png" alt="E-Mail"/></td></tr>';
				echo '</table><br/>';

				echo '<input type="button" class="button" value="Log in" onclick="hide_element_by_name(\'login_register_layer\'); show_element_by_name(\'login_form_layer\');"/>';
				echo '<input type="submit" class="button" value="Register" style="font-weight: bold;"/>';
				echo '<input type="button" class="button" value="Forgot password" onclick="hide_element_by_name(\'login_register_layer\'); show_element_by_name(\'login_forgot_pwd_layer\');"/>';
				echo '</form>';
			echo '</div>';

			//todo: javascript som validerar input email, visa en "retrieve new password" knapp om emailen �r korrekt
			echo '<div id="login_forgot_pwd_layer" style="display: none;">';
				echo '<form method="post" action="">';
				echo 'Enter the e-mail address used when registering your account.<br/><br/>';
				echo 'You will recieve an e-mail with a link to follow, where you can set a new password.<br/><br/>';
				echo '<table cellpadding="2">';
				echo '<tr><td>E-mail:</td><td><input type="text" size="26"/> <img src="/gfx/icon_mail.png" alt="E-Mail"/></td></tr>';
				echo '</table><br/>';

				echo '<input type="button" class="button" value="Log in" onclick="hide_element_by_name(\'login_forgot_pwd_layer\'); show_element_by_name(\'login_form_layer\');"/>';
				echo '<input type="button" class="button" value="Register" onclick="hide_element_by_name(\'login_forgot_pwd_layer\'); show_element_by_name(\'login_register_layer\');"/>';
				echo '<input type="submit" class="button" value="Forgot password" style="font-weight: bold;"/>';
				echo '</form>';
			echo '</div>';
		}

		echo '</div>';
	}

	function showInfo()
	{
		echo '<b>Current session information</b><br/>';
		echo 'Logged in: '. ($this->id?'YES':'NO').'<br/>';
		if ($this->id) {
			echo 'User name: '.$this->username.'<br/>';
			echo 'User ID: '.$this->id.'<br/>';
		}

		echo 'User mode: ';
		if ($this->isSuperAdmin) echo 'Super admin<br/>';
		else if ($this->isAdmin) echo 'Admin<br/>';
		else if ($this->id) echo 'Normal user<br/>';
		else echo 'Visitor<br/>';

		echo 'Session name: '.$this->session_name.'<br/>';
		echo 'Current IP: '.GeoIP_to_IPv4($this->ip).'<br/>';
		echo 'Session timeout: '.shortTimePeriod($this->timeout).'<br/>';
		echo 'Check for IP changes: '. ($this->check_ip?'YES':'NO').'<br/>';
		echo 'Home page: '.$this->home_page.'<br/>';
		if ($this->isSuperAdmin) {
			echo 'SHA1 key: '.$this->sha1_key.'<br/>';
		}
	}

	/* Saves a setting associated with current user */
	function save($name, $value)
	{
		if (!$this->id) return;

		saveSetting(SETTING_USER, $this->id, $name, $value);
	}

	/* Reads a setting associated with current user */
	function load($name, $default = '')
	{
		if (!$this->id) return;

		return loadSetting(SETTING_USER, $this->id, $name, $default);
	}

	/* Renders html for editing all tblSettings field for current user */
	//todo: use ajax to save changes
	function editSettings()
	{
		global $config;

		$list = readAllSettings(SETTING_USER, $this->id);
		if (!$list) return;

		require_once($config['core_root'].'layout/ajax_loading_layer.html');

		echo '<div class="edit_settings">';
		echo '<form name="edit_settings_frm" method="post" action="">';
		foreach($list as $row) {
			if (!empty($_POST['edit_setting_'.$row['settingId']])) {
				//Stores the setting
				saveSetting(SETTING_USER, $this->id, $row['settingName'], $_POST['edit_setting_'.$row['settingId']]);
				$row['settingValue'] = $_POST['edit_setting_'.$row['settingId']];
			}
			echo '<div id="edit_setting_div_'.$row['settingId'].'">';
			echo $row['settingName'].': <input type="text" name="edit_setting_'.$row['settingId'].'" value="'.$row['settingValue'].'"/>';
			echo '<img src="/gfx/icon_error.png" alt="Delete" title="Delete" onclick="perform_ajax_delete_uservar('.$row['settingId'].');"/>';
			echo '</div>';
		}
		echo '<input type="submit" class="button" value="Save"/>';
		echo '</form>';
		echo '</div>';
	}
	
	/* Locks unregistered users out from certain pages */
	function requireLoggedIn()
	{
		global $config;

		if (!$this->id) {
			header('Location: '.$config['session']['home_page']);
			die;
		}
	}

	/* Locks unregistered users out from certain pages */
	function requireAdmin()
	{
		global $config;

		if (!$this->isAdmin) {
			header('Location: '.$config['session']['home_page']);
			die;
		}
	}

}
?>