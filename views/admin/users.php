<?php
/**
 * This is the user manager
 */

//TODO: fix up row coloring with YuiDatatable

namespace cd;

$session->requireAdmin();

echo '<h1>Manage users</h1>';
echo 'All users: '.ahref('a/users/', UserList::getCount()).'<br/>';
echo 'Users online: '.ahref('a/users/?online', UserList::onlineCount()).'<br/>';

$filter = '';
if (!empty($_POST['usearch'])) $filter = $_POST['usearch'];

echo '<br/>';
echo xhtmlForm('usearch_frm');
echo 'Username filter: '.xhtmlInput('usearch');
echo xhtmlSubmit('Search');
echo xhtmlFormClose();
echo '<br/>';

if (isset($_GET['online']))
{
    $list = UserList::getUsersOnline($filter);
    echo '<h2>Showing all users online';
}
else
{
    $list = UserList::getUsers($filter);
    echo '<h2>Showing all users';
}

if ($filter)
    echo ', matching <u>'.$filter.'</u>';

echo ' ('.count($list).' hits)</h2>';

$dt = new YuiDatatable();
$dt->addColumn('id',    'Username', 'link', 'a/user/', 'name');
$dt->addColumn('email',             'E-mail');
$dt->addColumn('time_last_active',  'Last active');
$dt->addColumn('last_ip',           'Last IP');
$dt->addColumn('time_created',      'Created');
$dt->addColumn('is_online',         'Online?');
$dt->addColumn('type',              'Type', 'array', getSessionTypes() );
// $dt->addColumn('userlevel',         'User level', 'array', getUserLevels() );


/*  //XXX row coloring not fully working in YuiDatatable
$header->embedCss(
'.yui-skin-sam .yui-dt tr.green_mark,'.
'.yui-skin-sam .yui-dt tr.green_mark td.yui-dt-asc,'.
'.yui-skin-sam .yui-dt tr.green_mark td.yui-dt-desc,'.
'.yui-skin-sam .yui-dt tr.green_mark td.yui-dt-asc,'.
'.yui-skin-sam .yui-dt tr.green_mark td.yui-dt-desc {'.
    'background-color: #3a3;'.
    'color: #fff;'.
'}');

$dt->colorRow('is_online', '!=', false, 'green_mark');
*/

// $dt->setSortOrder('time_last_active', 'desc');
$dt->setDataSource( $list );
//$dt->setRowsPerPage(10);
echo $dt->render();
echo '<br/>';


if ($session->isSuperAdmin)
    echo '&raquo; '.ahref('a/create_user/', 'Create new user');

?>
