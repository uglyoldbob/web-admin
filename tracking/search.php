<?php
include("global.php");
start_my_session();
header('Content-type: text/html; charset=utf-8');

require("forms.php");

$database = openDatabase();

?>

<!DOCTYPE HTML SYSTEM>
<html>
<head>
<title>Equipment Management System: Search</title>
</head>


<body>

<?php

login_code();
login_button($database);

echo '<a href="' . bottomPageURL() . '">Return to main</a>' . "<br >\n";

if ($_POST['action'] == "search")
{
	$query = "SELECT * FROM equipment WHERE owner = " . $_SESSION['id'] . " AND name LIKE '%" . 
		mysql_real_escape_string($_POST['search']) . "%' or description LIKE '%" .
				mysql_real_escape_string($_POST['search']) . "%' or unit LIKE '%" .
				mysql_real_escape_string($_POST['search']) . "%';";
	$results = mysql_query($query, $database);
	
	$results_found = 0;
	
	while($row = mysql_fetch_array($results))
	{
		$results_found++;
		echo $row['quantity'] . " " . $row['unit'] . " of " . $row['name'];
		if ($row['description'] != "")
		{
			echo " (" . $row['description'] . ")";
		}
		echo " at ";
		echo '<a href="' . bottomPageURL() . 'locations.php?id=' . 
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

closeDatabase($database);

?>

</body>
</html>
