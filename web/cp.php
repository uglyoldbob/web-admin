<?php
/**
* Simple autoloader, so we don't need Composer just for this.
*/
class Autoloader
{
    public static function register()
    {
        spl_autoload_register(function ($class) 
		{
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
			$file = str_replace('_', DIRECTORY_SEPARATOR, $file);
            if (file_exists($file)) 
			{
                require $file;
                return true;
            }
            return false;
        });
    }
}
Autoloader::register();

require_once("webAdmin/exceptions.php");
require_once("global.php");

if (!headers_sent())
{
	header('Content-type: text/html; charset=utf-8');
}

?>
<!DOCTYPE HTML>
<html>
<head>
<?php

try
{
	$config = parse_ini_file("config.ini");
	test_config($config);

	global $mysql_db;
	$mysql_db = openDatabase($config);
	
	$cust_session = new \webAdmin\session($config, $mysql_db, "sessions");
	start_my_session();	//start php session

	?>
	<title>Control Panel: <?php sitename($config)?></title>
	<?php do_css($config) ?>
	</head>
	<body>

	<script type="text/javascript" src="jquery-1.2.1.pack.js"></script>
	<script type="text/javascript" src="jscript.js"></script>

	<?php
	$currentUser = new \webAdmin\user($config, $mysql_db, "users");
	$currentUser->certificate_tables("root_ca", "intermediate_ca");

	$currentUser->require_login(0);

	do_top_menu(6, $config);

	echo "Time based filtering:\n";
	selectTimePeriod();

	echo "<b>Possible job status</b><br>\n";
	\webAdmin\jobs::table_of_job_status();

	echo "<b>Possible transaction categories</b><br>\n";
	\webAdmin\finance::table_of_transaction_categories();

	echo "	<input class=\"buttons\" type=\"checkbox\" name=\"debug_session\" ";
	echo "onclick=\"cb_hide_show(this, $('#debug_session_data'));\" />Show session data<br >\n";
	echo "	<div id=\"debug_session_data\" style=\"display: none;\">\n";
	print_r($_SESSION);
	echo "<br>\n";
	print_r($_POST);
	echo "<br>\n";
	print_r($_GET);
	echo "<br>\n";
	echo "	</div>\n";
}
catch (\webAdmin\ConfigurationMissingException $e)
{
	?>
	<title>Site Configuration Error</title>
	<?php do_css($config) ?>
	</head>
	<body>
	<h1>Site configuration error</h1>
	<?php
	if (isset($_GET['debug']) || ($config['debug']==1))
	{
		echo "Details: " . (string)$e . "<br />\n";
	}
}
catch (\webAdmin\DatabaseConnectionFailedException $e)
{
	?>
	<title>Site Configuration Error</title>
	<?php do_css($config) ?>
	</head>
	<body>
	<h1>Site configuration error</h1>
	<?php
	if (isset($_GET['debug']) || ($config['debug']==1))
	{
		echo "Details: " . (string)$e . "<br />\n";
	}
}
catch (\webAdmin\PermissionDeniedException $e)
{
	?>
	<title>Permission Denied</title>
	<?php do_css($config) ?>
	</head>
	<body>
	<h1>Permission Denied</h1>
	<?php
	if (isset($_GET['debug']) || ($config['debug']==1))
	{
		echo "Details: " . (string)$e . "<br />\n";
	}
}
catch (\webAdmin\InvalidUsernameOrPasswordException $e)
{
	echo "<h3>Invalid username or password</h3>\n";
	echo "<b>Please login</b>\n" .
		"<form action=\"" . curPageURL($config) . "\" method=\"post\" autocomplete=\"on\" >\n" .
		"	<input type=\"hidden\" name=\"action\" value=\"login\">\n" .
		"	<label for=\"username\"> Username: <input type=\"text\" name=\"username\" autocomplete=\"on\" ><br>\n" .
		"	<label for=\"password\"> Password: <input type=\"password\" name=\"password\" autocomplete=\"on\" ><br>\n" .
		"	<input class=\"buttons\" type=\"submit\" name=\"do_login\" value=\"Login\">\n" .
		"</form>\n";
	if ($config['allow_user_create'] == 1)
	{
		echo "<form action=\"" . curPageURL($config) . "\" method=\"post\">\n" .
			 "	<input type=\"hidden\" name=\"action\" value=\"register\">\n" .
			 "	<input class=\"buttons\" type=\"submit\" value=\"Register\">\n" .
			 "</form>\n";
	}
}
catch (\webAdmin\NotLoggedInException $e)
{
	echo "<b>Please login</b>\n" .
		"<form action=\"" . curPageURL($config) . "\" method=\"post\" autocomplete=\"on\" >\n" .
		"	<input type=\"hidden\" name=\"action\" value=\"login\">\n" .
		"	<label for=\"username\"> Username: <input type=\"text\" name=\"username\" autocomplete=\"on\" ><br>\n" .
		"	<label for=\"password\"> Password: <input type=\"password\" name=\"password\" autocomplete=\"on\" ><br>\n" .
		"	<input class=\"buttons\" type=\"submit\" name=\"do_login\" value=\"Login\">\n" .
		"</form>\n";
	if ($config['allow_user_create'] == 1)
	{
		echo "<form action=\"" . curPageURL($config) . "\" method=\"post\">\n" .
			 "	<input type=\"hidden\" name=\"action\" value=\"register\">\n" .
			 "	<input class=\"buttons\" type=\"submit\" value=\"Register\">\n" .
			 "</form>\n";
	}
}
catch (\webAdmin\CertificateException $e)
{
	echo "<b>A certificate is required to access this page</b><br />\n";
	if (isset($_GET['debug']) || ($config['debug']==1))
	{
		echo "Details: " . (string)$e . "<br />\n";
	}
}
catch (Exception $e)
{
	?>
	<title>Error</title>
	<?php do_css($config) ?>
	</head>
	<body>
	<h1>Error</h1>
	<?php
	if (isset($_GET['debug']) || ($config['debug']==1))
	{
		echo "Details: " . (string)$e . "<br />\n";
	}
}


?>

</body>
</html>
				
