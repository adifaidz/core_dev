<?
	require_once('config.php');
	if (!$l) die;	//user not logged in

	require('design_head.php');
?>
	�NDRA PRESENTATIONSBILD<br/>
	<br/>
	Du kan ladda upp en ny presentationsbild genom att<br/>
	skicka ett MMS till mms@citysurf.tv med rubriken "PRES xxx",
	d�r xxx �r din unika MMS-kod.<br/><br/>
	
	<a href="user_change_mms_code.php">�NDRA MMS-KOD</a><br/>

<?
	require('design_foot.php');
?>