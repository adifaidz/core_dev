<?php
/**
 * $Id$
 */

die('UNTESTED');

require_once('find_config.php');
$h->session->requireAdmin();

if (!isset($_GET['id'])) {
    header('Location: admin_bug_reports.php');
    die;
}

$bugId = $_GET['id'];

if (isset($_POST['reason'])) {
    closeBugReport($_GET['id'], $_POST['reason']);
    header('Location: admin_bug_reports.php');
    die;
}

require('design_admin_head.php');

echo '<h1>Close bug report</h1>';

$item = getBugReport($bugId);

echo '<form method="post" action="'.$_SERVER['PHP_SELF'].'?id='.$bugId.'">';
echo getRelativeTimeLong($item['timestamp']).', by '.Users::link($item['bugCreator'], $item['userName']).'<br/>';
echo 'Details: <br/>';
echo nl2br($item['bugDesc']).'<br/>';
echo 'Close reason: <select name="reason">';
for ($i=0; $i<count($close_bug_reason); $i++) {
    echo '<option value="'.$i.'">'.$close_bug_reason[$i];
}
echo '</select><br/>';
echo '<input type="submit" class="button" value="Close bug report">';
echo '</form>';

require('design_admin_foot.php');
?>
