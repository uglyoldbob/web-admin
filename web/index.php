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
require_once("webAdmin/global.php");

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
	\webAdmin\test_config($config);

	global $mysql_db;
	$mysql_db = \webAdmin\openDatabase($config);
	
	$cust_session = new \webAdmin\session($config, $mysql_db, "sessions");
	\webAdmin\start_my_session();	//start php session

	
	?>
	<title><?php \webAdmin\sitename($config)?></title>
	<?php \webAdmin\do_css($config) ?>
</head>
<body>
	<?php

	$currentUser = new \webAdmin\user($config, $mysql_db, "users");
	$currentUser->certificate_tables("root_ca", "intermediate_ca", "user_certs");
	
	$currentUser->require_login_or_registered_certificate();
	
	\webAdmin\do_top_menu(0, $config);
	echo "Something goes here?<br>\n";
}
catch (\webAdmin\ConfigurationMissingException $e)
{
	?>
	<title>Site Configuration Error</title>
	</head>
	<body>
	<h1>Site configuration error</h1>
	<?php
	if (isset($_GET['debug']) || ($config['debug']==1))
	{
		echo "Details: " . (string)$e . "<br />\n";
	}
}
catch (\webAdmin\SiteConfigurationException $e)
{
	?>
	<title>Site Configuration Error</title>
	<?php \webAdmin\do_css($config) ?>
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
	<?php \webAdmin\do_css($config) ?>
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
	<?php \webAdmin\do_css($config) ?>
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
	$currentUser->login_form();
	if (isset($_GET['debug']) || ($config['debug']==1))
	{
		echo "Details: " . (string)$e . "<br />\n";
	}
}
catch (\webAdmin\NotLoggedInException $e)
{
	$currentUser->login_form();
	if (isset($_GET['debug']) || ($config['debug']==1))
	{
		echo "Details: " . (string)$e . "<br />\n";
	}
}
catch (\webAdmin\CertificateException $e)
{
	echo "<b>A certificate is required to access this page</b><br />\n";
}
catch (Exception $e)
{
	?>
	<title>Error</title>
	<?php \webAdmin\do_css($config) ?>
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
