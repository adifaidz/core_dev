<?
	require('config.php');
	require('design_head.php');

	echo 'DIN G�STBOK<br/><br/>';

	$list = gbList($l['id_id'], 0, 6);

	//print_r($list);
	foreach($list as $row)
	{
		echo ($row['user_read']?'l�st':'ol�st');
		echo ' fr�n '.$row['u_alias'].', '.$row['sent_date'];
		
		$text = substr($row['sent_cmt'], 0, 15);
		if (!$text) $text = '(ingen text)';
		echo '<a href="gb_view.php?id='.$row['main_id'].'">'.$text.'</a>';
		if (strlen($text) < strlen($row['sent_cmt'])) echo '...';
		echo '<br/>';
	}

	require('design_foot.php');
?>