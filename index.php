<?php
session_start();	//start php session
header('Content-type: text/html; charset=utf-8');

include("global.php");

openDatabase();

?>
<!DOCTYPE HTML>
<html>
<head>
<title>Thermal Specialists Management System</title>
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
if ($stop == 0)
{
	selectTimePeriod();
	
	if (isset($_SESSION['username']))
	{
		if ($_SESSION['user']['permission_payments'] != 0)
		{
			echo '<a href="' . rootPageURL() . '/payments.php">Look at all payments</a>' . "<br >\n";
		}
		if ($_SESSION['user']['permission_contacts'] != 0)
		{
			echo '<a href="' . rootPageURL() . '/contacts.php">Browse all contacts</a>' . "<br >\n";
		}
		if ($_SESSION['user']['permission_jobs'] != 0)
		{
			echo '<a href="' . rootPageURL() . '/inspections.php">View all inspections</a>' . "<br >\n";
		}
		echo '<a href="' . rootPageURL() . '/properties.php">View where inspections have been done</a>' . "<br >\n";
		echo '<a href="' . rootPageURL() . '/jobs.php">View job list</a>' . "<br >\n";
	}
}

closeDatabase();

?>

</body>
</html>
