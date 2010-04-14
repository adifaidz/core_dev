<?php
/**
 * $Id$
 *
 * Default authentication class. Uses core_dev's own tblUsers
 *
 * @author Martin Lindhe, 2007-2009 <martin@startwars.org>
 */

//TODO: handleForgotPassword(): use client_smtp.php instead!

require_once('auth_base.php');
require_once('design_auth.php');        //default functions for auth xhtml forms
require_once('class.Users.php');

require_once('atom_events.php');        //for event logging
require_once('atom_blocks.php');        //for isBlocked()
require_once('atom_activation.php');    //for generateActivationCode()

class auth_default extends auth_base
{
    var $driver = 'default';

    var $error = '';    ///< contains last error message, if any

    var $sha1_key = 'rpxp8xFDSGsdfgds5tgddgsDh9tkeWljo';    ///< used to further encode sha1 passwords, to make rainbow table attacks harder

    var $allow_login = true;                ///< set to false to only let superadmins log in to the site
    var $allow_registration = true;        ///< set to false to disallow the possibility to register new users. will be disabled if login is disabled
    var $mail_activate = false;            ///< does account registration require email activation?
    var $mail_error = false;                ///< will be set to true if there was problems sending out email

    var $activation_sent = false;        ///< internal. true if mail activation has been sent
    var $resetpwd_sent = false;            ///< internal. true if mail for password reset has been sent

    var $check_ip = true;                ///< client will be logged out if client ip is changed during the session
    var $ip = 0;                        ///< IP of user

    function __construct($conf = array())
    {
        if (isset($conf['sha1_key'])) $this->sha1_key = $conf['sha1_key'];
        if (isset($conf['allow_login'])) $this->allow_login = $conf['allow_login'];
        if (isset($conf['allow_registration'])) $this->allow_registration = $conf['allow_registration'];
        if (isset($conf['mail_activate'])) $this->mail_activate = $conf['mail_activate'];

        if (isset($conf['check_ip'])) $this->check_ip = $conf['check_ip'];

        if (!isset($_SESSION['user_agent'])) $_SESSION['user_agent'] = '';

        $this->ip = &$_SESSION['ip'];
        $this->user_agent = &$_SESSION['user_agent'];

        if (!$this->ip && !empty($_SERVER['REMOTE_ADDR'])) $this->ip = IPv4_to_GeoIP(client_ip());
    }

    /**
     * Handles logins
     *
     * @param $username
     * @param $password
     * @return true on success
     */
    function login($username, $password)
    {
        $data = $this->validLogin($username, $password);

        if (!$data) {
            $this->error = t('Login failed');
            dp('Failed login attempt: username '.$username, LOGLEVEL_WARNING);
            return false;
        }

        if ($data['userMode'] != USERLEVEL_SUPERADMIN) {
            if ($this->mail_activate && !Users::isActivated($data['userId'])) {
                $this->error = t('This account has not yet been activated.');
                return false;
            }

            if (!$this->allow_login) {
                $this->error = t('Logins currently not allowed.');
                return false;
            }

            $blocked = isBlocked(BLOCK_USERID, $data['userId']);
            if ($blocked) {
                $this->error = t('Account blocked');
                dp('Login attempt from blocked user: username '.$username, LOGLEVEL_WARNING);
                return false;
            }
        }
        return $data;
    }

    /**
     * Logs out the user
     */
    function logout($userId)
    {
        $users = new Users();
        $users->logoutTime($userId);

        addEvent(EVENT_USER_LOGOUT, 0, $userId);
    }

    /**
     * Checks if this is a valid login
     *
     * @return if valid login, return user data, else false
     */
    function validLogin($username, $password)
    {
        $users = new Users();
        $id = $users->getId($username);
        $enc_password = sha1( $id.sha1($this->sha1_key).sha1($password) );

        return $users->validLogin($username, $enc_password);
    }

}
?>
