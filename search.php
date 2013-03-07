<?php
session_start();	//start php session
header('Content-type: text/html; charset=utf-8');

include("global.php");

openDatabase();

?>
<!DOCTYPE HTML>
<html>
<head>
<title>Thermal Specialists Management System: Search</title>
<link rel="stylesheet" type="text/css" href="css/global.css" />
</head>
<body>

<?php

$stop = 0;
echo '<div>' . "\n";
if (login_code(0) == 1)
{
	$stop = 1;
}
echo "</div>\n";

global $mysql_db;
if ($stop == 0)
{
	echo '<a href="' . rootPageURL() . '">Return to main</a>' . "<br >\n";

	if ($_POST['action'] == "search")
	{
		$query = "SELECT * FROM equipment WHERE owner = " . $_SESSION['id'] . " AND name LIKE '%" . 
			$mysql_db->real_escape_string($_POST['search']) . "%' or description LIKE '%" .
					$mysql_db->real_escape_string($_POST['search']) . "%' or unit LIKE '%" .
					$mysql_db->real_escape_string($_POST['search']) . "%';";
		$results = $mysql_db->query($query);
		
		$results_found = 0;
		
		while($row = $results->fetch_array(MYSQLI_BOTH))
		{
			$results_found++;
			echo $row['quantity'] . " " . $row['unit'] . " of " . $row['name'];
			if ($row['description'] != "")
			{
				echo " (" . $row['description'] . ")";
			}
			echo " at ";
			echo '<a href="' . rootPageURL() . 'locations.php?id=' . 
				get_location($row['id'], $database) . "\">";
			print_location($row['location'], $database);
			echo '</a>';
			echo "<br >\n";
		}
		if ($results_found == 0)
		{
			echo "No results found\n";
		}
		$_POST['action'] = "";
	}

	if ($_POST["action"] == "")
	{
		echo "<form action=\"" . curPageURL() . "\" method=\"post\">\n" .
			"	<input name=\"search\" type=\"text\" />" .
			"	<input type=\"hidden\" name=\"action\" value=\"search\"><br>\n";
		echo "	<input type=\"submit\" value=\"Search\">\n";
		echo "</form>";
	}
}

closeDatabase();

?>

</body>
</html>
