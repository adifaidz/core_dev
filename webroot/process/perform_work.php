<?
	/* perform_work.php - k�rs regelbundet
	
		detta script plockar fram de 10 �ldsta arbetsuppgifterna fr�n databasen och utf�r dessa en i taget
	*/

	require_once('config.php');
	
	require_once('design_head.php');

	performWorkOrders(10);

	require_once('design_foot.php');
?>