<?php
include("global.php");
start_my_session();	//start php session
header('Content-type: text/html; charset=utf-8');

global $mysql_db;
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


// uploading file example
//for testing only
/*?>
<form action="upload_file.php" method="post"
enctype="multipart/form-data">
<label for="file">Filename:</label>
<input type="file" name="file" id="file"><br>
<input type="submit" name="submit" value="Submit">
</form>
<?php
*/

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
		echo '<a href="' . rootPageURL() . '/locations.php">Browse locations</a>' . "<br >\n";
		echo '<a href="' . rootPageURL() . '/search.php">Search locations</a>' . "<br >\n";
	}
}

closeDatabase();

?>

</body>
</html>
