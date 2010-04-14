<?php

//xdebug_enable();
error_reporting(E_ALL);
$time_start = microtime(true);
$config['debug'] = true;

$config['core']['fs_root'] = 'E:/devel/webroot/core_dev/';
$config['core']['web_root']= '/core_dev/';

$config['app']['web_root'] = '/core_dev/sample/';
$config['default_title'] = 'sample project';                    //default title for pages if no title is specified for that page

set_include_path($config['core']['fs_root'].'core/');
require_once('class.DB_MySQLi.php');
require_once('class.Auth_Standard.php');
require_once('class.Users.php');
require_once('class.Session.php');
require_once('class.Files.php');
require_once('functions_core.php');
require_once('functions_general.php');
require_once('functions_wiki.php');
require_once('functions_news.php');
require_once('functions_blogs.php');
require_once('functions_guestbook.php');
require_once('functions_contacts.php');
require_once('functions_messages.php');
require_once('functions_forum.php');
require_once('functions_todo.php');
require_once('functions_fileareas.php');
require_once('atom_feedback.php');
restore_include_path();

//$config['plugins'] = array('wurfl');
//loadPlugins();

$config['database']['username']    = 'root';
$config['database']['password']    = '';
$config['database']['database']    = 'dbSample';
$db = new DB_MySQLi($config['database']);
/*
$config['database']['username']    = 'postgres';
$config['database']['password']    = 'test';
$config['database']['database']    = 'dbSample';
$db = new DB_PostgreSQL($config['database']);
*/

$config['session']['timeout'] = (60*60)*24*7;        //keep logged in for 7 days
$config['session']['name'] = 'coreID';
$config['session']['allow_themes'] = true;
$session = new Session($config['session']);

$config['auth']['sha1_key'] = 'sdcu7cw897cwhwihwiuh#zaixx7wsxh3hdzsddFDF4ex1g';
$config['auth']['allow_login'] = true;
$config['auth']['allow_registration'] = true;
$auth = new Auth_Standard($config['auth']);

$config['files']['apc_uploads'] = false;
$config['files']['upload_dir'] = 'E:/devel/webupload/sample/';
$files = new Files($config['files']);

$config['wiki']['allow_html'] = true;
$config['wiki']['allow_files'] = true;

/* Visas på alla olika sidor som hör till någons användarprofil */
$param = '';
$username = $session->username;
if (!empty($_GET['id']) && is_numeric($_GET['id'])) {
    //Someones profile
    $param = '?id='.$_GET['id'];
    $username = Users::getName($_GET['id']);
    $profile_menu = array(
        'user.php'.$param => 'Overview:'.$username,
        'files.php'.$param => 'Files',
        'user_blogs.php'.$param => 'Blogs',
        'guestbook.php'.$param => 'Guestbook',
        'messages.php'.$param => 'Message',
        'friends.php'.$param => 'Friends',
        'abuse.php'.$param => 'Abuse'
    );
} else {
    //My profile
    $profile_menu = array(
        'user.php' => 'Overview:'.$username,
        'files.php' => 'Files',
        'messages.php' => 'Messages',
        'user_blogs.php' => 'Blogs',
        'guestbook.php' => 'Guestbook ('.getGuestbookUnreadCount($session->id).')',    //shows number of unread guestbook messages
        'friends.php' => 'Friends',
        'settings.php' => 'Settings',
        'subscriptions.php' => 'Subscriptions',
        'user_visits.php' => 'Visitors'
    );
}

$user_menu = array(
    'users.php' => 'Users:Overview',
    'search_users.php' => 'Search users',
    'last_logged_in.php' => 'Last logged in',
    'users_online.php' => 'Users online'
);

$forum_menu = array(
    'forum.php' => 'Forum:Overview',
    'forum_search.php' => 'Search',
    'forum_latest.php' => 'Latest'
);
?>
