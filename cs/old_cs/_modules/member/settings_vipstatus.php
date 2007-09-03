<?
	require(CONFIG.'cut.fnc.php');
	require(CONFIG.'secure.fnc.php');

	require_once('settings.fnc.php');

	require(DESIGN.'head.php');
	
	$vip_levels = array(
		1 => 'Normal anv�ndare',
		2 => 'VIP',
		3 => 'VIP Deluxe'
	);
?>
<div id="mainContent">

	<div class="subHead">inst�llningar - vip status</div><br class="clr"/>

	<? makeButton(false, 'goLoc(\''.l('member', 'settings').'\')', 'icon_settings.png', 'publika'); ?>
	<? makeButton(false, 'goLoc(\''.l('member', 'settings', 'fact').'\')', 'icon_settings.png', 'fakta'); ?>
	<? makeButton(false, 'goLoc(\''.l('member', 'settings', 'theme').'\')', 'icon_settings.png', 'tema'); ?>
	<? makeButton(false, 'goLoc(\''.l('member', 'settings', 'img').'\')', 'icon_settings.png', 'bild'); ?>
	<? makeButton(false, 'goLoc(\''.l('member', 'settings', 'personal').'\')', 'icon_settings.png', 'personliga'); ?>
	<? makeButton(false, 'goLoc(\''.l('member', 'settings', 'subscription').'\')', 'icon_settings.png', 'span'); ?>
	<? makeButton(false, 'goLoc(\''.l('member', 'settings', 'delete').'\')', 'icon_settings.png', 'radera konto'); ?>
	<? makeButton(true, 'goLoc(\''.l('member', 'settings', 'vipstatus').'\')', 'icon_settings.png', 'VIP'); ?>
	<br class="clr"/><br/>
	
	<div class="bigHeader">Fyll p� VIP</div>
	<div class="bigBody">
	
		F�r att k�pa VIP, skicka f�ljande SMS:<br/><br/>
		
		"<b>CITY VIPD <?=$l['id_id']?></b>" till nummer <b>72777</b> f�r 10 dagars VIP Delux (SMS:et kostar 20 SEK).<br/><br/>
		
		"<b>CITY VIP <?=$l['id_id']?></b>" till nummer <b>72777</b> f�r 14 dagars VIP (SMS:et kostar 20 SEK).<br/><br/>
	
		<b><a href="/main/upgrade/">Klicka h�r</a></b> f�r att l�sa mer om VIP-niv�er.<br/><br/>
	</div>
	<br/>

	<div class="bigHeader">Din aktuella VIP-niv�</div>
	<div class="bigBody">
<?
		$current_vip = getCurrentVIPLevel($l['id_id']);
		echo $vip_levels[ $current_vip ].'<br/><br/>';
?>
	</div>
	<br/>

	<div class="bigHeader">Tillg�ngliga VIP-niv�er</div>
	<div class="bigBody">
<?
		$list = getVIPLevels($l['id_id']);

		if (!$list) echo 'Inga VIP-niv�er tillg�ngliga f�r dig!<br/>';
		
		foreach ($list as $row) {
			echo '<div'.($current_vip==$row['level']?' style="font-weight: bold;"':'').'>';
			echo $vip_levels[ $row['level'] ];
			echo ' - '.$row['days'].' dagar �terst�r. Betalades senast '.$row['timeSet'];
			echo '</div>';
		}
?>
	</div>

</div>

<?
	include(DESIGN.'foot.php');
?>