<?php
session_start();	//start php session
header('Content-type: text/html; charset=utf-8');
include("global.php");

?>

<!DOCTYPE HTML SYSTEM>
<html>
<head>
<title>Equipment Management System</title>
</head>
<body>

<?php

$database = openDatabase();
login_code();
login_button($database);

if (isset($_SESSION['username']))
{
	echo '<a href="' . bottomPageURL() . 'locations.php">Browse locations</a>' . "<br >\n";
	echo '<a href="' . bottomPageURL() . 'search.php">Search</a>' . "<br >\n";
}


closeDatabase($database);

?>

</body>
</html>
