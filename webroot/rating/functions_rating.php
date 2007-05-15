<?
	/*
		atom_rating.php - set of functions to implement user rating of various objects, used by other modules

		Written by Martin Lindhe, 2006-2007

		todo: ajax-gadget d�r man kan r�sta p� ett objekt
	*/

	define('RATE_NEWS',		1);
	define('RATE_BLOG',		2);
	define('RATE_IMAGE',	3);

	/* L�gg ett omd�me + h�ll reda p� att anv�ndaren lagt sitt omd�me
		$_rating �r ett heltal mellan 1 till 100 (eller 0 till 99) ?
	*/
	function rateItem($_type, $_id, $_rating)
	{
		if (!is_numeric($_type) || !is_numeric($_id) || !is_numeric($_rating)) return false;

		//1. kolla om anv�ndaren redan r�stat
		$q = 'SELECT * FROM tblRatingData WHERE userId='.$session->id;
		if (1) return false;

		//2. spara r�stningen
		$q = 'INSERT INTO tblRatingData SET type='.$_type.',itemId='.$_id.',rating='.$_rating.',userId='.$session->id.',timeRated=NOW()';
		$db->query($q);
		
		//3. r�kna ut aktuella medelv�rdet av omd�met
		$q = 'SELECT * FROM tblRatingData WHERE type='.$_type.',itemId='.$_id;
		$avgrating = $db->getOneItem($q);
		
		switch ($_type) {
			case RATE_NEWS:
				//4. uppdatera medelv�rdet
				$q = 'UPDATE tblNews SET rating='.$avgrating.' WHERE newsId='.$_id;
				break;
				
			default: die('rateItem unknown type');
		}
	}

	/* Returnerar omd�met f�r detta objekt */
	function getRatring($_type, $_id)
	{
	}

?>