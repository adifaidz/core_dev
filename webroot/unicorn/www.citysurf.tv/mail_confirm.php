<?
	//"activate" anv�nds f�r att bekr�fta �ndrad mailaddress vid "bekr�fta uppgifter"
	$activate_code = '';
	if (!empty($_GET['activate']) && is_numeric($_GET['activate'])) $activate_code = $_GET['activate'];

	//"regcheck" anv�nds vid nyregisrering (och vid �ndring av email under inst�llningar-todo!)
	$regcheck = '';
	if (!empty($_GET['regcheck']) && is_numeric($_GET['regcheck'])) $regcheck = $_GET['regcheck'];

	if (!$activate_code && !$regcheck) die;

	include("_config/online.include.php");

	require(DESIGN.'head.php');

	if ($activate_code) {
		$q = 'SELECT id_id,u_email FROM s_userregfast WHERE activate_code="'.$activate_code.'"';
		$row = $sql->queryLine($q);
	
		if ($row) {
			$q = 'UPDATE tblVerifyUsers SET verified=1 WHERE user_id='.$row[0];
			$sql->queryUpdate($q);
	
			$q = 'UPDATE s_user SET u_email="'.$row[1].'",level_id="'.VIP_LEVEL2.'" WHERE id_id='.$row[0];
			$sql->queryUpdate($q);
			
			$q = 'DELETE FROM s_userregfast WHERE activate_code="'.$activate_code.'"';
			$sql->queryUpdate($q);
	
			addVIP($row[0], VIP_LEVEL2, 7);
	
			$msg = 'Dina uppgifter har bekr�ftats och du har nu f�tt en veckas VIP-Deluxe. Logga in p� nytt f�r att den ska tr�da i kraft!';
		} else {
			$msg = 'Felaktig aktiveringskod!';
		}
	}

?>
<div id="mainContent">

	<div class="bigHeader">Bekr�ftelse av uppgifter</div>
	<div class="bigBody"><?=$msg?>
	</div>

</div>

<?
	require(DESIGN.'foot.php');
?>
