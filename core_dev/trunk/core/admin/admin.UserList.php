<?php
/**
 * $Id$
 *
 * @author Martin Lindhe, 2007-2010 <martin@startwars.org>
 */

//STATUS: cleanup & rewrite
//TODO: factor our sql from here

//TODO: use editable yui_datatable

require_once('AdminComponent.php');

class UserList extends AdminComponent
{
    /**
     * @return total number of users (excluding deleted ones)
     */
    function getCount($mode = 0)
    {
        global $db;
        if (!is_numeric($mode)) return false;

        $q = 'SELECT COUNT(*) FROM tblUsers';
        $q .= ' WHERE timeDeleted IS NULL';
        if ($mode) $q .= ' AND userMode='.$mode;
        return $db->getOneItem($q);
    }

    /**
     * @return number of users online
     */
    function onlineCount()
    {
        global $db, $h;

        if (empty($h->session))
            $timeout = 60 * 30; //30min
        else
            $timeout = $h->session->online_timeout;

        $q  = 'SELECT COUNT(*) FROM tblUsers WHERE timeDeleted IS NULL';
        $q .= ' AND timeLastActive >= DATE_SUB(NOW(),INTERVAL '.$timeout.' SECOND)';
        return $db->getOneItem($q);
    }

    /**
     * @returns array of all users online
     */
    function allOnline()
    {
        global $db, $h;

        if (empty($h->session))
            $timeout = 60 * 30; //30min
        else
            $timeout = $h->session->online_timeout;

        $q  = 'SELECT * FROM tblUsers WHERE timeDeleted IS NULL';
        $q .= ' AND timeLastActive >= DATE_SUB(NOW(),INTERVAL '.$timeout.' SECOND)';
        $q .= ' ORDER BY timeLastActive DESC';
        return $db->getArray($q);
    }

    /**
     * @param $mode usermode
     * @param $filter partial username matching
     */
    function getUsers($mode = 0, $filter = '')
    {
        global $db;
        if (!is_numeric($mode)) return false;

        $q = 'SELECT * FROM tblUsers';
        $q .= ' WHERE timeDeleted IS NULL';
        if ($mode) $q .= ' AND userMode='.$mode;
        if ($filter) $q .= ' AND userName LIKE "%'.$db->escape($filter).'%"';
        return $db->getArray($q);
    }

    function render()
    {
        global $h;

        if ($h->session->isSuperAdmin && !empty($_GET['del']))
            Users::removeUser($_GET['del']);

        echo 'Registered users: <a href="'.$_SERVER['PHP_SELF'].'">'.$this->getCount().'</a><br/>';
        echo 'Webmasters: <a href="'.$_SERVER['PHP_SELF'].'?user_mode='.USERLEVEL_WEBMASTER.'">'.$this->getCount(USERLEVEL_WEBMASTER).'</a><br/>';
        echo 'Admins: <a href="'.$_SERVER['PHP_SELF'].'?user_mode='.USERLEVEL_ADMIN.'">'.$this->getCount(USERLEVEL_ADMIN).'</a><br/>';
        echo 'SuperAdmins: <a href="'.$_SERVER['PHP_SELF'].'?user_mode='.USERLEVEL_SUPERADMIN.'">'.$this->getCount(USERLEVEL_SUPERADMIN).'</a><br/>';
//XXX TODO: lista användare online
        echo 'Users online: <a href="'.$_SERVER['PHP_SELF'].'?show_online">'.$this->onlineCount().'</a><br/>';

        $filter = '';
        if (!empty($_POST['usearch'])) $filter = $_POST['usearch'];

        $mode = 0;
        if (!empty($_GET['user_mode'])) $mode = $_GET['user_mode'];

        //process updates
        if ($h->session->isSuperAdmin && !empty($_POST)) {
            $list = $this->getUsers($mode);
            foreach ($list as $row) {
                if (empty($_POST['mode_'.$row['userId']])) continue;
                $newmode = $_POST['mode_'.$row['userId']];
                if ($newmode != $row['userMode'])
                    Users::setMode($row['userId'], $newmode);
            }

            if (!empty($_POST['u_name']) && !empty($_POST['u_pwd']) && isset($_POST['u_mode'])) {
                $newUserId = $h->user->register($_POST['u_name'], $_POST['u_pwd'], $_POST['u_pwd'], $_POST['u_mode']);
                if (!is_numeric($newUserId)) {
                    echo '<div class="critical">'.$newUserId.'</div>';
                } else {
                    echo '<div class="okay">New user created. <a href="admin_user.php?id='.$newUserId.'">'.$_POST['u_name'].'</a></div>';
                }
            }
        }

        echo '<br/>';
        echo xhtmlForm('usearch_frm');
        echo 'Username filter: '.xhtmlInput('usearch');
        echo xhtmlSubmit('Search');
        echo xhtmlFormClose();
        echo '<br/>';

        $list = $this->getUsers($mode, $filter);

        if ($h->session->isSuperAdmin) echo '<form method="post" action="">';
        echo '<table summary="" border="1">';
        echo '<tr>';
        echo '<th>Username</th>';
        echo '<th>Last active</th>';
        echo '<th>Created</th>';
        echo '<th>User mode</th>';
        echo '</tr>';
        foreach ($list as $user)
        {
            echo '<tr'.($user['timeDeleted']?' class="critical"':'').'>';
            echo '<td><a href="admin_user.php?id='.$user['userId'].'">'.$user['userName'].'</a></td>';
            echo '<td>'.$user['timeLastActive'].'</td>';
            echo '<td>'.$user['timeCreated'].'</td>';
            echo '<td>';
            if ($h->session->isSuperAdmin) {
                echo '<select name="mode_'.$user['userId'].'">';
                echo '<option value="'.USERLEVEL_NORMAL.'"'.($user['userMode']==USERLEVEL_NORMAL?' selected="selected"':'').'>Normal</option>';
                echo '<option value="'.USERLEVEL_WEBMASTER.'"'.($user['userMode']==USERLEVEL_WEBMASTER?' selected="selected"':'').'>Webmaster</option>';
                echo '<option value="'.USERLEVEL_ADMIN.'"'.($user['userMode']==USERLEVEL_ADMIN?' selected="selected"':'').'>Admin</option>';
                echo '<option value="'.USERLEVEL_SUPERADMIN.'"'.($user['userMode']==USERLEVEL_SUPERADMIN?' selected="selected"':'').'>Super admin</option>';
                echo '</select> ';
                if ($h->session->id != $user['userId'] && !$user['timeDeleted']) {
                    echo coreButton('Delete', '?del='.$user['userId']);
                }
            } else {
                echo $user['userMode'];
            }
            echo '</td>';
            echo '</tr>';
        }
        echo '<tr>';
        echo '<td colspan="3">Add user: '.xhtmlInput('u_name').' - pwd: '.xhtmlInput('u_pwd').'</td>';
        echo '<td>';
        if ($h->session->isSuperAdmin) {
            echo '<select name="u_mode">';
            echo '<option value="'.USERLEVEL_NORMAL.'">Normal</option>';
            echo '<option value="'.USERLEVEL_WEBMASTER.'">Webmaster</option>';
            echo '<option value="'.USERLEVEL_ADMIN.'">Admin</option>';
            echo '<option value="'.USERLEVEL_SUPERADMIN.'">Super admin</option>';
            echo '</select>';
        } else {
            echo 'normal user';
        }
        echo '</td>';
        echo '</tr>';
        echo '</table>';

        if ($h->session->isSuperAdmin) {
            echo xhtmlSubmit('Save changes');
            echo '</form>';
        }

    }
}


?>
