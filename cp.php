<?php
include("global.php");
start_my_session();	//start php session
header('Content-type: text/html; charset=utf-8');

global $mysql_db;
openDatabase();

require("include/jobs.php");

?>
<!DOCTYPE HTML>
<html>
<head>
<title>Thermal Specialists Management System</title>
<?php do_css() ?>
</head>
<body>

<script type="text/javascript" src="jquery-1.2.1.pack.js"></script>
<script type="text/javascript" src="jscript.js"></script>

<?php

$stop = 0;

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
$stop = 0;

if ($stop == 0)
{
	do_top_menu(5);
	if (login_code(0) == 1)
	{
		$stop = 1;
	}
	echo "Time based filtering:\n";
	selectTimePeriod();
	
	echo "<b>Possible job status</b><br>\n";
	jobs::table_of_job_status();
}

closeDatabase();

?>

</body>
</html>
