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

	Om du ist�llet vill blockera anv�ndaren, <a href="javascript:makeBlock('<?=$s['id_id']?>');">klicka h�r</a>.<br/><br/>
	Motivera din anm�lan.<br/>
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
