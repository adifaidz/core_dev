<?
	require_once('find_config.php');
	$session->requireAdmin();

	require($project.'design_head.php');
	
	echo createMenu($admin_menu, 'blog_menu');

	echo '<h2>List users</h2>';
	echo 'As a super admin, you can upgrade users to other user levels, or remove them from the system from this page.<br/><br/>';

	if ($session->isSuperAdmin && !empty($_GET['del'])) {
		Users::delete($_GET['del']);
	}

	$mode = 0;
	if (!empty($_GET['mode'])) $mode = $_GET['mode'];
	$list = Users::getUsers($mode);

	if ($session->isSuperAdmin && !empty($_POST)) {
		foreach ($list as $row) {
			$newmode = $_POST['mode_'.$row['userId']];
			if ($newmode != $row['userMode']) {
				Users::setMode($row['userId'], $newmode);
			}
		}
		
		if (!empty($_POST['u_name']) && !empty($_POST['u_pwd']) && !empty($_POST['u_mode'])) {
			$newUserId = $session->registerUser($_POST['u_name'], $_POST['u_pwd'], $_POST['u_pwd'], $_POST['u_mode']);
			if (!is_numeric($newUserId)) {
				echo '<div class="critical">'.$newUserId.'</div>';
			} else {
				echo '<div class="okay">New user created. Go to user page: '.Users::link($newUserId, $_POST['u_name']).'</div>';
			}
		}
		$list = Users::getUsers($mode);
	}

	if ($session->isSuperAdmin) echo '<form method="post" action="">';
	echo '<table summary="" border="1">';
	echo '<tr>';
	echo '<th>Username</th>';
	echo '<th>Last active</th>';
	echo '<th>Created</th>';
	echo '<th>User mode</th>';
	echo '</tr>';
	foreach ($list as $user)
	{
		echo '<tr>';
		echo '<td>'.Users::link($user['userId'], $user['userName']).'</td>';
		echo '<td>'.$user['timeLastActive'].'</td>';
		echo '<td>'.$user['timeCreated'].'</td>';
		echo '<td>';
			if ($session->isSuperAdmin) {
				echo '<select name="mode_'.$user['userId'].'">';
				echo '<option value="0"'.($user['userMode']==0?' selected="selected"':'').'>Normal</option>';
				echo '<option value="1"'.($user['userMode']==1?' selected="selected"':'').'>Admin</option>';
				echo '<option value="2"'.($user['userMode']==2?' selected="selected"':'').'>Super admin</option>';
				echo '</select> ';

				if ($session->id != $user['userId']) echo '<a href="?del='.$user['userId'].getProjectPath().'">del</a>';

			} else {
				echo $user['userMode'];
			}
		echo '</td>';
		echo '</tr>';
	}
	echo '<tr>';
	echo '<td colspan="3">Add user: <input type="text" name="u_name"/> - pwd: <input type="text" name="u_pwd"/></td>';
	echo '<td>';
		if ($session->isSuperAdmin) {
			echo '<select name="u_mode">';
			echo '<option value="0">&nbsp;</option>';
			echo '<option value="0">Normal</option>';
			echo '<option value="1">Admin</option>';
			echo '<option value="2">Super admin</option>';
			echo '</select>';
		} else {
			echo 'normal user';
		}
	echo '</td>';
	echo '</tr>';
	echo '</table>';

	if ($session->isSuperAdmin) {
		echo '<input type="submit" class="button" value="Save changes"/>';
		echo '</form>';
	}

	require($project.'design_foot.php');
?>