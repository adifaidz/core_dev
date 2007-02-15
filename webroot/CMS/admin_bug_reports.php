<?
	//todo: merge admin_closed_bug_reports.php into this file

	include_once('include_all.php');

	if (!$_SESSION['isSuperAdmin']) {
		header('Location: '.$config['start_page']);
		die;
	}

	include('design_head.php');
	include('design_user_head.php');

	$content = '<b>Administration screen - Bug reports</b><br>';
	$content .= 'Here is a list of bug reports/feature requests made by users of the game.<br>';
	$content .= 'The sooner these reports are dealt with the better, because users will see<br>';
	$content .= 'that their engagement pays off.<br><br>';
	
	$list = getBugReports($db);
	for ($i=0; $i<count($list); $i++) {
		$content .= '<div class="objectCritical">';
		$content .= getRelativeTimeLong($list[$i]['timestamp']).', by '.nameLink($list[$i]['bugCreator'], $list[$i]['userName']);
		$content .= ' via the '.($list[$i]['reportMethod'] ? 'game':'site').'<br>';
		$content .= nl2br($list[$i]['bugDesc']).'<br>';
		$content .= '<a href="admin_move_bug_report.php?id='.$list[$i]['bugId'].'">&raquo; Move this report into the TODO system</a><br>';
		$content .= '<a href="admin_close_bug_report.php?id='.$list[$i]['bugId'].'">&raquo; Close this report</a>';
		$content .= '</div>';
		$content .= '<br>';
	}
	
	$closedbugs = getClosedBugReportsCount($db);
	$content .= count($list).' bugs in list (Excluding '.$closedbugs.' CLOSED bugs).<br><br>';
	if ($closedbugs) {
		$content .= '<a href="admin_closed_bug_reports.php">&raquo; List CLOSED bug reports</a><br>';
	}
	$content .= '<a href="admin_current_work.php">&raquo; Back to current work</a><br>';

		echo '<div id="user_admin_content">';
		echo MakeBox('<a href="admin.php">Administrationsgr&auml;nssnitt</a>|Bug reports', $content);
		echo '</div>';

	include('design_admin_foot.php');
	include('design_foot.php');
?>