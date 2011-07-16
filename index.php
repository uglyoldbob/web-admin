<?php
session_start();	//start php session
header('Content-type: text/html; charset=utf-8');
include("global.php");

$database = openDatabase();

?>

<!DOCTYPE HTML SYSTEM>
<html>
<head>
<title>Thermal Specialists Management System</title>
</head>
<body>

<?php

login_code();
login_button($database);
selectTimePeriod();

if (isset($_SESSION['username']))
{
	echo '<a href="/payments.php">Look at all payments</a>' . "<br >\n";
	echo '<a href="/codb.php">Cost of doing business calculator</a>' . "<br >\n";
	echo '<a href="/contacts.php">Browse all contacts</a>' . "<br >\n";
	echo '<a href="/inspections.php">View all inspections</a>' . "<br >\n";
	echo '<a href="/properties.php">View where inspections have been done</a>' . "<br >\n";
}


closeDatabase($database);

?>

</body>
</html>
