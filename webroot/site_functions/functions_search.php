<?
	/*
		functions_search.php - Funktioner f�r s�kning i forum och av anv�ndare
	*/

	/* $list �r en array med ord att s�ka p� */
	function getForumSearchQuery($list)
	{
		$sql = '';
		for ($i=0; $i<count($list); $i++) {

			$curr = $list[$i];
			if (substr($curr,0,1) == '+') {
				//kr�v detta

				$curr = substr($curr,1);
				if ($i>0) {
					$sql .= 'AND ';
				}
				$sql .= '(t1.itemSubject LIKE "%'.$curr.'%" OR t1.itemBody LIKE "%'.$curr.'%") ';

			} else if (substr($curr,0,1) == '-') {
				//INTE detta

				if (count($list)==1) { //till�t inte s�kning p� allt UTAN ett ord..
					return;
				}

				$curr = substr($curr,1);
				if ($i>0) {
					$sql .= 'AND ';
				}
				$sql .= 'NOT (t1.itemSubject LIKE "%'.$curr.'%" OR t1.itemBody LIKE "%'.$curr.'%") ';

			} else {
				//frivilligt (typ detta ELLER n�t annat)

				if ($i>0) {
					$sql .= 'OR ';
				}
				$sql .= '(t1.itemSubject LIKE "%'.$curr.'%" OR t1.itemBody LIKE "%'.$curr.'%") ';
			}
		}

		$sql .= 'AND t1.itemDeleted=0 ';
		return $sql;
	}

	/* Returns a list of search results with forum items */
	function getForumSearchResults(&$db, $criteria, $method, $page, $limit)
	{
		$criteria = dbAddSlashes($db, $criteria);
		if (!is_numeric($page) || !is_numeric($limit)) return false;

		if (!$criteria || !$method || !$page || !$limit) {
			return false;
		}

		$list = explode(' ', $criteria);

		$sql  = 'SELECT t1.*,t2.userName AS authorName FROM tblForums AS t1 ';
		$sql .= 'INNER JOIN tblUsers AS t2 ON (t1.authorId=t2.userId) ';
		$sql .= 'WHERE ';

		$sql .= getForumSearchQuery($list);

		switch ($method) {
			case 'mostread': //mest l�st
				$sql .= 'ORDER BY t1.itemRead DESC '; break;

			case 'oldfirst': //�lst f�rst
				$sql .= 'ORDER BY t1.timestamp ASC '; break;

			case 'newfirst': default: //nyast f�rst, default
				$sql .= 'ORDER BY t1.timestamp DESC '; break;
		}


		$sql .= 'LIMIT '.(($page-1) * $limit).','.$limit;

		return dbArray($db, $sql);
	}

	function getForumSearchResultsCount(&$db, $criteria)
	{
		$criteria = dbAddSlashes($db, $criteria);

		if (!$criteria) {
			return false;
		}

		$list = explode(' ', $criteria);

		$sql  = 'SELECT COUNT(t1.itemId) FROM tblForums AS t1 ';
		$sql .= 'WHERE ';

		$sql .= getForumSearchQuery($list);

		return dbOneResultItem($db, $sql);
	}

	/* data �r $_POST � kan inneh�lla irrelevant info! */
	function getUserSearchResult(&$db, $data)
	{
		$data['c'] = trim($data['c']);
		$criteria = substr($data['c'], 0, 30); //only allow up to 30 characters in search free-text
		$criteria = dbAddSlashes($db, $data['c']);

		/* $criteria matchar vad som finns i alla textarea & textf�lt */
		$sql  = 'SELECT t1.userId,t1.userName FROM tblUsers AS t1 ';
		$sql .= 'LEFT OUTER JOIN tblUserdata AS n1 ON (t1.userId=n1.userId) ';
		$sql .= 'LEFT OUTER JOIN tblUserdataFields AS t2 ON (n1.fieldId=t2.fieldId) ';

		$list = getUserdataFields($db);
		
		/* Kollar ifall n�n data �r satt i $data[] ... */
		for ($i=0; $i<count($list); $i++) {
			if (isset($data[ $list[$i]['fieldId'] ]) && $data[ $list[$i]['fieldId'] ]) {
				break;
			}
		}

		/* ... eller s�tter $x om vi bara ska returnera alla anv�ndarna */
		/* om man �r admin, s� returneras alla anv�ndare om $data �r tom */
		if ( !$criteria && $_SESSION['isAdmin'] && $i == count($list)) {

			$x = 1;
			
		} else {

			$start = 2; //autogenererade INNER JOIN tables kommer heta a1, a2 osv.
			
			/* Add one INNER JOIN for each parameter we want to search on */
			for ($i=0; $i<count($list); $i++) {
				if (isset($data[ $list[$i]['fieldId'] ])) {

					if ($data[ $list[$i]['fieldId'] ]) {
						$sql .= 'INNER JOIN tblUserdata AS n'.$start.' ON (t1.userId=n'.$start.'.userId) ';
						$start++; //�ka
					}
				}
			}

			$sql .= 'WHERE ';
			if ($criteria) { //f�r fritext
				$sql .= '((t2.fieldType='.USERDATA_TYPE_TEXT.' OR t2.fieldType='.USERDATA_TYPE_TEXTAREA.') ';
				$sql .= 'AND LOWER(n1.value) LIKE LOWER("%'.$criteria.'%") AND t2.fieldAccess=2) ';
				$sql .= 'OR LOWER(t1.userName) LIKE LOWER("%'.$criteria.'%") ';
				$x = 1;
			}

			$start = 2; //autogenererade INNER JOIN tables kommer heta a1, a2 osv.

			/* Plocka fram dom userf�lt anv�ndaren har s�kt p� */
			for ($i=0; $i<count($list); $i++) {
				if (isset($data[ $list[$i]['fieldId'] ])) {
					$val = $data[ $list[$i]['fieldId'] ];
					if ($val) {
						if (isset($x)) {
							$sql .= 'AND ';
						}
						if ($start > 1) { // n1 f�rsta skapas alltid!
							$sql .= '(n'.$start.'.fieldId='.$list[$i]['fieldId'].' AND n'.$start.'.value="'.$val.'") ';
						}
						$start++;
						$x = 1;
					}
				}
			}

		}

		$sql .= 'GROUP BY t1.userId ';
		$sql .= 'ORDER BY t1.userName';

		if (!isset($x)) return;

		return dbArray($db, $sql);
	}

	/* Returnerar alla anv�ndarnamn som b�rjar p� $phrase */
	function searchUsernameBeginsWith(&$db, $phrase)
	{
		$phrase = dbAddSlashes($db, trim($phrase));

		$sql  = 'SELECT userId,userName FROM tblUsers ';
		$sql .= 'WHERE LOWER(userName) LIKE LOWER("'.$phrase.'%")';

		return dbArray($db, $sql);
	}

	/* Returnerar alla med nicknames som b�rjar p� $phrase */
	function searchNicknameBeginsWith(&$db, $phrase)
	{
		$phrase = dbAddSlashes($db, trim($phrase));
		$fieldId = getUserdataFieldId($db, 'Nickname');

		$sql  = 'SELECT t1.userId,t1.userName FROM tblUsers AS t1 ';
		$sql .= 'INNER JOIN tblUserdata AS t2 ON (t1.userId=t2.userId) ';
		$sql .= 'WHERE t2.fieldId='.$fieldId.' AND LOWER(t2.value) LIKE LOWER("'.$phrase.'%")';

		return dbArray($db, $sql);
	}
	
	function getUserSearchResultOnNickname(&$db, $phrase)
	{
		$phrase = dbAddSlashes($db, trim($phrase));
		$fieldId = getUserdataFieldId($db, 'Nickname');

		$sql  = 'SELECT t1.userId,t1.userName FROM tblUsers AS t1 ';
		$sql .= 'INNER JOIN tblUserdata AS t2 ON (t1.userId=t2.userId) ';
		$sql .= 'WHERE t2.fieldId='.$fieldId.' AND LOWER(t2.value) LIKE LOWER("%'.$phrase.'%")';

		return dbArray($db, $sql);
	}
?>