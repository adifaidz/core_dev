<?
	require_once('config.php');
	if (!$l) die;	//user not logged in

	require('design_head.php');
?>

	S�K ANV�NDARE<br/><br/>

	<div class="mid_content">
	<form method="post" action="search_users_result.php">
		K�n:
		<input type="hidden" name="sex" value="0"/>
		<input type="radio" name="sex" id="sexM" value="M"/><label for="sexM">Killar</label>
		<input type="radio" name="sex" id="sexF" value="F"/><label for="sexF">Tjejer</label><br/>
		
<!--		<input type="checkbox" name="online" id="online" value="1"/><label for="online">Online nu</label> -->
		<input type="checkbox" name="pic" id="pic" value="1" checked="true"/><label for="pic">Har bild</label><br/>
		Alias: <input type="text" name="alias" size="15"/><br/>
		�lder:
		<select name="age">
			<option value="0">alla �ldrar</option>
			<option value="1">mellan 0-20 �r</option>
			<option value="2">mellan 21-25 �r</option>
			<option value="3">mellan 26-30 �r</option>
			<option value="4">mellan 31-35 �r</option>
			<option value="5">mellan 36-40 �r</option>
			<option value="6">mellan 41-45 �r</option>
			<option value="7">mellan 46-50 �r</option>
			<option value="8">mellan 51-55 �r</option>
			<option value="9">56 �r och �ldre</option>			
		</select>
		<br/>

		<select name="lan">
			<option value="0">Alla l�n</option>
<?		optionLan($result['lan']); ?>
		</select>
		
		<input type="submit" value="S�k"/>
	</form>
</div>

<?
	require('design_foot.php');
?>