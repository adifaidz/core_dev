<?
	require_once('relations.fnc.php');

	if($own) popupACT('Du kan inte blocka dig sj�lv.');
	if(!empty($_POST['do'])) {
		blockRelation($s['id_id']);
		popupACT('Nu har du blockerat personen.', '', l('user', 'block', $s['id_id']));
	}
	require(DESIGN.'head_popup.php');
?>
<body>
<form name="msg" action="<?=l('user', 'block', $s['id_id'])?>" method="post">
<input type="hidden" name="do" value="1" />
		<div class="popupWholeContent cnti mrg">
			<div class="smallHeader"><h4>blockera</h4></div>
			<div class="smallBody">
			<table cellspacing="0" style="height: 150px;"><tr><td style="height: 150px; vertical-align: middle;">
blockera:<br /><span><?=$user->getstring($s, '', array('nolink' => true))?></span>
<br /><br /><p class="lft">varken du eller personen kommer att kunna kontakta varandra h�r p� sidan. du kan n�r som helst ta bort din blockering under v�nner / ov�nner.</p><br /><b>forts�tt?</b><br /><br />
			</td></tr></table>
				<input type="submit" class="btn2_min r" value="blockera!" style="margin-top: 5px;" /><br class="clr" />
			</div>
		</div>
</form>
</body>
</html>