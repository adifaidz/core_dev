<?
	include("include_all.php");
	include("body_header.php");

	if (isset($_POST["recordname"]) && isset($_POST["tracks"]) && isset($_POST["info"]) && $_POST["recordname"] && $_POST["tracks"])
	{
		/* Add comp/split entry */

		$record_name = $_POST["recordname"];
		$record_info = trim($_POST["info"]);
		$tracks = $_POST["tracks"];

		$record_id = addRecord($db, $_SESSION["userId"], 0, $record_name, $record_info); //bandid=0 for records with multiple bands
		if (!$record_id)
		{
			echo "Problems adding record.<br>";
		}
		else
		{
			createTracks($db, $record_id, $tracks);
			echo "Record '".$record_name."' added.<br>";
			echo "<a href=\"show_record.php?id=".$record_id."\">Click here</a> to go to this record.<br>";
			echo "<br>";
			
		}
		
	}


	echo "For comp and split records, you assign a band to each track in the next step,<br>";
	echo "first we just create the record entry, with the record name and track count.<br>";

	echo "<table cellpadding=0 cellspacing=0 border=0>";
	echo "<form name=\"addcomprecord\" method=\"post\" action=\"".$_SERVER["PHP_SELF"]."\">";
	echo "<tr><td>Record name:</td><td><input type=\"text\" name=\"recordname\" size=50></td></tr>";
	echo "<tr><td>Number of tracks:&nbsp;</td><td><input type=\"text\" name=\"tracks\" value=\"1\"></td></tr>";
	echo "<tr><td valign=\"top\">Record info:<br>(optional)</td><td><textarea name=\"info\" cols=40 rows=8></textarea></td></tr>";

	echo "<tr><td colspan=2><input type=\"submit\" value=\"Add\" class=\"buttonstyle\"></td></tr>";
	echo "</form>";
	echo "</table>";
	
	include("body_footer.php");
?>