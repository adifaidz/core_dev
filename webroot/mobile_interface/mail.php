<?
	require('config.php');

	require('design_head.php');
	
	/*
	todo: $user->getuser() b�r returnera "raderad anv�ndare" om anv�ndaren ej finns?
	
	*/
?>

	DIN MAIL:<br/>
	<br/>
	
	<a href="mail_new.php">SKRIV NYTT MAIL</a><br/>
	<br/>

<?

	$list = mailInboxContent(0, 5);
	//print_r($list);
	
	for ($i=0; $i<count($list); $i++) {
		if ($list[$i]['user_read']) echo '(l�st) '; else echo '(ol�st )';
		$rubrik = $list[$i]['sent_ttl'];
		if (!$rubrik) $rubrik = '(ingen rubrik)';
		echo '<a href="mail_read.php?id='.$list[$i]['main_id'].'">'.$rubrik.'</a>';
		
		$from_alias = $user->getuser($list[$i]['sender_id']);
		$from_alias = $from_alias['u_alias'];
		echo ' fr�n <a href="user.php?id='.$list[$i]['sender_id'].'">'.$from_alias.'</a> ';
		
		echo nicedate($list[$i]['sent_date']).'<br/>';
		
	}
	
	require('design_foot.php');
?>