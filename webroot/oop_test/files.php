<?
/*
	todo:
	
		- rita upp en "files-gadget"
		- kunna ladda upp nya bilder direkt fr�n gadgeten,
			* visa upload progress med ajax---
			
		- kunna redigera bilder:
			* rotera
			* f�rminska
			* f�rstora
			* f�rhandsgranska
			* spara
			* med ajax
			
		- kunna spela .mp3or
			* med flash modul
*/

	require_once('config.php');

	require('design_head.php');

	echo 'file area<br>';
	
	$files->showFiles();

	require('design_foot.php');
?>