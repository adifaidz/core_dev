<?
	require(DESIGN.'head_user.php');
	require_once('abuse.fnc.php');
?>

<div class="subHead">anm�l anv�ndare</div><br class="clr"/>
<div class="bigHeader">anm�lan</div>
<div class="bigBody">

<?
	if (!empty($_POST['abuse']) ) {
		abuseReport($s['id_id'], $_POST['abuse']);
?>
		Din anm�lan har mottagits!
<?
	} else {
?>
	Vill du bara blockera denna anv�ndare fr�n din sida, <a href="javascript:makeBlock('<?=$s['id_id']?>');">klicka h�r</a>.<br/><br/>
	
	Vill du anm�la anv�ndaren, ange orsak i rutan h�r under.andlar �rendet s� snabbt vi kan.<br/>
	Missbruk kan lea till avst�ngning av dig sj�lv!

	<form method="post" action="">
	<textarea name="abuse" rows="7" cols="40"></textarea><br/>
	<input type="submit" class="btn2_sml" value="anm�l!"/>
	</form>
<?
	}
?>

</div>

<?
	require(DESIGN.'foot_user.php');
?>
