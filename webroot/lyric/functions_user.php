<?
	function loginUser($db, $username, $password) {

		$username = dbAddSlashes($db, $username);
		$password = dbAddSlashes($db, $password);

		if (!$username || !$password) return "Fyll i b�de anv�ndarnamn och l�senord!";

		$sql = "SELECT userId FROM tblUsers WHERE username='".$username."' AND userpassword=PASSWORD('".$password."')";
		$check = dbQuery($db, $sql);
		if (dbNumRows($check)) {

			$row = dbFetchArray($check);
			return true;
		}
		return "Felaktigt anv�ndarnamn/l�senord";
	}
	
	function getUserId($db, $userName) {
		/* Kollar upp userId f�r $username, returnerar false om $username inte finns */

		$userName = dbAddSlashes($db, $userName);

		$check = dbQuery($db, "SELECT userId FROM tblUsers WHERE username='".$userName."'");
		if (dbNumRows($check)) {
			$userinfo = dbFetchArray($check);
			return $userinfo["userId"];
		} else {
			return false;
		}
	}
	
	/* Returnerar usermode, 0 = normal, 1 = admin */
	function getUserMode($db, $userId) {
		
		if (!is_numeric($userId)) return false;
		
		return dbOneResultItem($db, 'SELECT mode from tblUsers where userId='.$userId);
	}
?>