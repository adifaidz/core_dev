<?
/*
	todo:

		file upload:
			* visa upload progress med ajax callback (kr�ver n�n custom apache modul tror jag)
			
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

	//todo: fixa denna s�kv�g
	require_once('../layout/image_zoom_layer.html');

	echo 'file area<br>';
	
	$files->showFiles();

	require('design_foot.php');
?>