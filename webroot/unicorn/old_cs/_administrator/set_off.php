<?
require("./set_c.php");
require("./set_fnc.php");

$connection = @mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS);
if(!$connection) { 
	echo 'Det g�r inte att kontakta databasen.';
	#doMail('frans@freshfly.se', 'Connect', 'En error har uppst�tt! Kolla sidan!');
	exit;
}
if(!@mysql_select_db(MYSQL_DB)) { 
	echo 'Det g�r inte att v�lja databas.';
#	doMail('frans@freshfly.se', 'Select', 'En error har uppst�tt! Kolla sidan!');
	exit;
}
?>