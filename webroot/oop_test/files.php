<?
/*
	todo:

		file upload:
			* visa upload progress med ajax callback
			
		bildvisare:
			* centrera bilden i mitten av webbl�saren, �ver file-gadgeten (ska visas halvtransparent i bakgrunden)
			* rotera
			* f�rminska
			* f�rstora
			* f�rhandsgranska
			* spara
			* med ajax
			
		ljuduppspelare:
			* flash modul
*/

	require_once('config.php');

	require('design_head.php');

	echo 'file area<br/>';
	
	$files->showFiles();

	require('design_foot.php');
?>