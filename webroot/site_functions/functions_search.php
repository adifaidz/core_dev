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


?>