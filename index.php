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

/*
$to = "thomas.epperson@gmail.com";
$subject = "test message";
$body = "this is a test message";
if (mail($to, $subject, $body))
{
	echo("<p>Message successfully sent!</p>");
}
else
{
	echo("<p>Message delivery failed...</p>");
}*/

if ($stop == 0)
{
	selectTimePeriod();
	
	if (isset($_SESSION['username']))
	{
		echo '<a href="' . rootPageURL() . '/payments.php">Look at all payments</a>' . "<br >\n";
		echo '<a href="' . rootPageURL() . '/contacts.php">Browse all contacts</a>' . "<br >\n";
		echo '<a href="' . rootPageURL() . '/inspections.php">View all inspections</a>' . "<br >\n";
		echo '<a href="' . rootPageURL() . '/properties.php">View where inspections have been done</a>' . "<br >\n";
		echo '<a href="' . rootPageURL() . '/jobs.php">View job list</a>' . "<br >\n";
	}
}

closeDatabase();

?>

</body>
</html>
