<?php
require("global.php");
require_once("include/exceptions.php");

start_my_session();	//start php session
header('Content-type: text/html; charset=utf-8');

?>
<!DOCTYPE HTML>
<html>
<head>
<?php

try
{
	$config = parse_ini_file("config.ini");
	test_config();

	global $mysql_db;
	openDatabase();
	?>
	<title><?php sitename()?></title>
	<?php do_css() ?>
</head>
<body>
	<?php

	$stop = 0;
	if (login_code(0) == 1)
	{
		$stop = 1;
	}

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
		do_top_menu(0);
		echo "Something goes here?<br>\n";
	}
	closeDatabase();
}
catch (ConfigurationMissingException $e)
{
	?>
	<title>Site Configuration Error</title>
	<?php do_css() ?>
	</head>
	<body>
	<h1>Site configuration error</h1>
	<?php
}
catch (DatabaseConnectionFailedException $e)
{
	?>
	<title>Site Configuration Error</title>
	<?php do_css() ?>
	</head>
	<body>
	<h1>Site configuration error</h1>
	<?php
}
catch (PermissionDeniedException $e)
{
	?>
	<title>Permission Denied</title>
	<?php do_css() ?>
	</head>
	<body>
	<h1>Permission Denied</h1>
	<?php
}
catch (Exception $e)
{
	?>
	<title>Error</title>
	<?php do_css() ?>
	</head>
	<body>
	<h1>Error</h1>
	<?php
}

?>

</body>
</html>
