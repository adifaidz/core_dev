<?
	require('design_head.php');
?>

	S�K ANV�NDARE<br/>
	<br/>

	<form method="post" action="search_users_result.php">
		<input type="checkbox"/>Killar
		<input type="checkbox"/>Tjejer
		<input type="checkbox"/>Online nu
		<input type="checkbox" checked="true"/>Har bild<br/>
		Fritext: <input type="text"/><br/>
		<br/>
		�lder fr�n <input type="text" size="2"/> till <input type="text" size="2"/><br/>
		<br/>
		
		<select name="xx">
			<option>Alla l�n</option>
			<option>Stockholm</option>
		</select>


		<select name="xx">
			<option>Alla orter</option>
			<option>Stockholm</option>
		</select>
		
		<input type="submit" value="S�k"/>
	</form>

<?
	require('design_foot.php');
?>