<?
	require('design_head.php');
?>

	S�K ANV�NDARE<br/>
	<br/>

	<form method="post" action="search_users_result.php">
		<input type="checkbox"/>Killar
		<input type="checkbox"/>Tjejer
		<input type="checkbox"/>Online nu<br/>
		Fritext: <input type="text"/><br/>
		
		Stad:
		<select name="xx">
			<option>Alla st�der
			<option>Stockholm
		</select>
		
		<input type="submit" value="S�k"/>
	</form>

<?
	require('design_foot.php');
?>