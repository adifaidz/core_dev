<?
	if (empty($_GET['id']) || !is_numeric($_GET['id'])) die;
	$_id = $_GET['id'];

	require('config.php');
	require('design_head.php');
	
	if (!empty($_POST['ins_rel'])) {
		//Registrera relations-f�rfr�gan
		$check = sendRelationRequest($_id, $_POST['ins_rel']);
		if ($check === true) {
			echo 'Du har nu skickat en f�rfr�gan.<br/><br/>';
			echo '<a href="relations.php">MINA V�NNER</a>';
			require('design_foot.php');
			die;
		}
	}
	
	$user_data = $user->getuser($_id);

	echo 'SKAPA RELATION MED '.$user_data['u_alias'].'<br/>';
	echo '<br/>';

	echo '<form method="post" action="">';

	$rel = getset('', 'r', 'mo', 'text_cmt ASC');

	echo '<select name="ins_rel" class="txt">';
	foreach($rel as $row) {
		echo '<option value="'.$row[0].'">'.secureOUT($row[1]).'</option>';
	}
	echo '</select> ';

	echo '<input type="submit" value="Skapa"/>';
	echo '</form>';

	require('design_foot.php');
?>