<?
	require(DESIGN.'head_user.php');
	require_once('abuse.fnc.php');
?>

<div class="subHead">ge VIP</div><br class="clr"/>
<div class="bigHeader">Ge bort VIP-status till denna anv�ndare</div>
<div class="bigBody">

	F�r att ge 2 veckors VIP till denna anv�ndare, skicka f�ljande SMS:<br/><br/>
	
	"<b>CITY VIP <?=$s['id_id']?></b>" till nummer <b>72777</b>.<br/><br/>

	SMS:et kostar 20 SEK.

</div>

<?
	require(DESIGN.'foot_user.php');
?>