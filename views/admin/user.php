<?php
/**
 * This is the user manager
 */

//TODO: use XhtmlForm

//TODO: show user comments:
/*

        echo '<h2>'.t('Comments').'</h2>';
        echo showComments(COMMENT_USER, $user->getId());

*/

namespace cd;

$session->requireSuperAdmin();

$user = User::get($this->owner);
if (!$user || !$user->id) {
    echo '<h2>No such user exists</h2>';
    return;
}

echo '<h1>User admin for '.$user->name.'</h1>';

echo '&raquo; '.ahref('u/profile/'.$user->id, 'Show profile').'<br/>';

if ($user->type == SESSION_FACEBOOK)
{
    echo '<h2>Facebook account</h2>';
    echo 'Fb username: '.UserSetting::get($user->id, 'fb_name').'<br/>';
    echo 'Fb picture: <img src="'.UserSetting::get($user->id, 'fb_picture').'"/><br/>';
    $fburl = 'http://www.facebook.com/profile.php?id='.$user->name;
    echo 'Fb profile: <a href="'.$fburl.'" target="_blank">'.$fburl.'</a><br/>';
}

echo 'Last IP: '.$user->last_ip.'<br/>';
echo '<br/>';

if ($session->id != $this->owner && isset($_GET['remove'])) {
    if (confirmed('Are you sure you want to remove this user?')) {
        $user->remove();
        echo '<div class="item">User removed</div>';
    }
    return;
}

if (!empty($_POST['change_pwd'])) {
    if (Password::isForbidden($_POST['change_pwd'])) {
        echo '<div class="item">Weak password was chosen, password has not been changed!</div>';
        return;
    }

    UserHandler::setPassword($user->id, $_POST['change_pwd']);
    echo '<div class="item">Password changed!</div>';
    return;
}

if (!empty($_POST['setting_name']) && isset($_POST['setting_val'])) {
    UserSetting::set($user->id, $_POST['setting_name'], $_POST['setting_val']);
    echo '<div class="good">Setting added!</div>';
}

if (!empty($_GET['remove_setting'])) {
    UserSetting::delete($user->id, $_GET['remove_setting']);
    echo '<div class="good">Setting removed!</div>';
}

// save changes in edited settings
if (!empty($_POST)) {
    $settings = UserSetting::getAll($user->id);

    foreach ($settings as $set) {
        if (!empty($_POST['setting_name_'.$set['id']]))
            UserSetting::set($user->id, $_POST['setting_name_'.$set['id']], $_POST['setting_val_'.$set['id']]);
    }
}


if (!empty($_POST['grp_id'])) {
    UserGroupHandler::addToGroup($user->id, $_POST['grp_id']);
}

if (!empty($_GET['rm_grp'])) {
    UserGroupHandler::removeFromGroup($user->id, $_GET['rm_grp']);
}

echo '<h2>Group membership</h2>';
echo 'This user is member of the following groups:<br/>';

foreach (UserGroupHandler::getGroups($user->id) as $g) {
    echo '<a href="'.relurl_add( array('rm_grp' => $g->getId())).'">'.coreButton('Delete').'</a> ';
    echo ahref('a/usergroup/'.$g->getId(), $g->getName()).'<br/>';
}
echo '<br/>';


echo xhtmlForm('grp');

$x = new XhtmlComponentDropdown();
$x->name = 'grp_id';
$x->setOptions( UserGroup::getIndexedList() );

echo $x->render().' ';

echo xhtmlSubmit('Add');
echo xhtmlFormClose().'<br/><br/>';


echo '<h2>Password</h2>';

$form = new XhtmlForm('pwd');
$form->disableAutocomplete();
$form->addPassword('change_pwd', 'Change password');
$form->addSubmit('Change');
echo $form->render().'<br/><br/>';


echo '<h2>User settings</h2>';

$settings = UserSetting::getAll($user->id);
echo xhtmlForm('edit_setting');
echo '<table>';
echo '<tr><th>Name</th><th>Value</th><th>Delete</th></tr>';
//XXX use editable YuiDataTable
foreach ($settings as $set)
{
    echo '<tr>';
    echo '<td>'.xhtmlInput('setting_name_'.$set['id'], $set['name']).'</td>';
    echo '<td>'.xhtmlInput('setting_val_'.$set['id'], $set['value']).'</td>';
    echo '<td><a href="'.relurl_add( array('remove_setting'=>$set['name']) ).'">Remove</a></td>';
    echo '</tr>';
}
echo '</table>';
echo xhtmlSubmit('Save changes');
echo xhtmlFormClose().'<br/><br/>';


echo '<h3>Add new user setting</h3>';
echo xhtmlForm('new_setting');
echo 'Name: '.xhtmlInput('setting_name').' ';
echo 'Value: '.xhtmlInput('setting_val').' ';
echo xhtmlSubmit('Add');
echo xhtmlFormClose().'<br/><br/>';



echo '<h2>Login history</h2>';

$dt = new YuiDatatable();
$dt->addColumn('timeCreated',     'Timestamp');
$dt->addColumn('IP',              'IP');
$dt->addColumn('userAgent',       'User agent');
$dt->setSortOrder('timeCreated', 'desc');
$dt->setDataSource( LoginEntry::getHistory($user->id) );
$dt->setRowsPerPage( 10 );
echo $dt->render();




if ($session->id != $this->owner )
    echo '&raquo; <a href="'.relurl_add( array('remove'=>1) ).'">Remove user</a><br/><br/>';



/*
        echo '<h2>'.t('Userdata').'</h2>';
        editUserdataSettings($user->id);

        echo '<h2>'.t('Events').'</h2>';
        $events = getEvents(0, $user->id, ' LIMIT 0,40');

        echo '<table>';
        foreach ($events as $row) {
            echo '<tr>';
                echo '<td>'.$row['timeCreated'].'</td>';
                echo '<td>'.$event_name[$row['type']].'</td>';
            echo '</tr>';
        }
        echo '</table>';


        echo '<h2>All userdata</h2>';
        if (!empty($_POST['new_ud_key']) && isset($_POST['new_ud_val'])) {
            saveSetting(SETTING_USERDATA, 0, $userId, $_POST['new_ud_key'], $_POST['new_ud_val']);
        }
        $list = readAllSettings(SETTING_USERDATA, 0, $userId);

        echo '<table>';
        echo '<tr>';
        echo '<th>Key</th>';
        echo '<th>Value</th>';
        echo '<th>Time set</th>';
        echo '<th>Remove ('.xhtmlCheckbox('toggle', 'all', 1, false, "toggle_checkboxes(this, 'mod_userdata')").')</th>';
        echo '</tr>';
        echo xhtmlForm('mod_userdata');
        foreach ($list as $row) {
            if (!empty($_POST['del_ud_'.$row['settingId']])) {
                deleteSetting(SETTING_USERDATA, 0, $userId, $row['settingName']);
                continue;
            } else if (!empty($_POST['mod_ud_'.$row['settingId']]) && $row['settingValue'] != $_POST['mod_ud_'.$row['settingId']]) {
                saveSetting(SETTING_USERDATA, 0, $userId, $row['settingName'], $_POST['mod_ud_'.$row['settingId']]);
                $row['settingValue'] = $_POST['mod_ud_'.$row['settingId']];
            }

            echo '<tr>';
                echo '<td>'.$row['settingName'].'</td>';
                echo '<td>'.xhtmlInput('mod_ud_'.$row['settingId'], $row['settingValue']).'</td>';
                echo '<td>'.formatTime($row['timeSaved']).'</td>';
                echo '<td>'.xhtmlCheckbox('del_ud_'.$row['settingId']).'</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo 'New key: '.xhtmlInput('new_ud_key').', value: '.xhtmlInput('new_ud_val').'<br/>';

        echo xhtmlSubmit('Save changes');
        echo xhtmlFormClose();
*/
