<?
	require_once('find_config.php');
	$session->requireAdmin();

	require($project.'design_head.php');
?>
<h1>Admin menu</h1><br/><br/>

<hr/>
super admin stuff:<br/>
<a href="admin_stopwords.php<?=getProjectPath(false)?>">Manage stopwords</a><br/>
<a href="admin_moderationqueue.php<?=getProjectPath(false)?>">Moderation queue</a><br/>
<hr/>

maintainance:<br/>
<a href="admin_events.php<?=getProjectPath(false)?>">Event log</a><br/>
<a href="admin_compat_check.php<?=getProjectPath(false)?>">Compatiblity check</a><br/>

<a href="admin_db_info.php<?=getProjectPath(false)?>">DB driver config</a><br/>
<a href="admin_session_info.php<?=getProjectPath(false)?>">Session info</a><br/>
<a href="admin_phpinfo.php<?=getProjectPath(false)?>">PHP info</a><br/>


<?
	require($project.'design_foot.php');

?>