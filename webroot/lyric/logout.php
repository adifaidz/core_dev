<?
	include("include_all.php");

	if ($_SESSION["loggedIn"] == true) {
		$_SESSION = array();
		session_destroy();
	}
	header("Location: index.php");
?>