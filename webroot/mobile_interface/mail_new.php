<?
	require('design_head.php');
	
	/*
		todo: sl� ihop som en "skriv nya brev" + "svara p� brev" (skicka med mail-id som vi ska svara p� is�fall
	
		todo: dropdown med folk fr�n kompislistan
	
	*/
?>

	SKIV NYTT MAIL<br/>
	<br/>

	<form method="post" action="">
		Till: <input type="text"/><br/>
		Rubrik: <input type="text"/><br/>
		Meddelande:<br/>
		<textarea></textarea><br/>
		<input type="submit" value="Skicka"/>
	</form>


<?
	require('design_foot.php');
?>