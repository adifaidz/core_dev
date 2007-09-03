<?
	require_once('config.php');
	$user->requireLoggedIn();

	$result = performSearch('', 0, 0, 50);
	if (count($result['res']) == 1) {
		header('Location: user.php?id='.$result['res'][0]['id_id']);
		die;
	}

	require('design_head.php');


	echo 'SÖK ANVÄNDARE - RESULTAT<br/><br/>';

	echo count($result['res']).' träffar:<br/>';

	echo '<div class="mid_content">';
	foreach ($result['res'] as $row)
	{
		echo $user->getstringMobile($row['id_id']).'<br/>';
	}
	echo '</div>';

	require('design_foot.php');
?>
