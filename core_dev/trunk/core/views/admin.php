<?php

//STATUS: wip

//TODO: ability to set default prefix for all links shown here

require_once('UserGroupList.php');

switch ($this->owner)
{
case 'userlist':
    $userlist = new UserList();
    echo $userlist->render();
    break;

case 'useredit': //child=user id, XXX link to here is hardcoded in admin_UserList.php view
    $useredit = new UserEditor();
    $useredit->setId($this->child);
    echo $useredit->render();
    break;

case 'usergroups':
    $grouplist = new UserGroupList();
    echo $grouplist->render();
    break;

case 'phpinfo':
    phpinfo();
    break;

default:
    echo '<h1>core_dev admin</h1>';
    echo '<a href="/admin/core/userlist">Manage users</a><br/>';
    echo '<a href="/admin/core/usergroups">Manage user groups</a><br/>';
    echo '<br/>';
    echo '<a href="/admin/core/phpinfo">phpinfo()</a><br/>';
    break;
}

?>
