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
	<title>Contact Listing: <?php \webAdmin\sitename($config)?></title>
	<?php \webAdmin\do_css($config) ?>
	</head>
	<body>

	<?php
	$currentUser = new \webAdmin\user($config, $mysql_db, "users");
	$currentUser->certificate_tables("root_ca", "intermediate_ca", "user_certs");

	$currentUser->require_login_or_registered_certificate();

	\webAdmin\do_top_menu(2, $config);

	//update contact information
	if ($_POST["action"] == "update")
	{
		$currentUser->update($_POST);
		$_POST["action"] = "";	//go back to contact viewing
	}
	else if ($_POST["action"] == "cpass")
	{
		$val = $_POST["id"];
		if (is_numeric($val) == FALSE)
		{
			$val = 0;
		}
		$contacts->create_password($val);
	}
	else if ($_POST["action"] == "epass")
	{
		$val = $_POST["id"];
		if (!is_numeric($val))
		{
			$val = 0;
		}
		$contacts->edit_password($val);
	}
	else if ($_POST["action"] == "apass")
	{
		$val = $_POST["id"];
		if (!is_numeric($val))
		{
			$val = 0;
		}
		$userid = $_SESSION['user']['emp_id'];
		$allow = \webAdmin\check_permission("contact_permission", $userid, $val, "%p%");
		if (\webAdmin\check_specific_permission($allow, "global") == "yes")
		{
			$newpass = $mysql_db->real_escape_string($_POST['pass2']);
			$passmatch = $mysql_db->real_escape_string($_POST['pass3']);
			if ($newpass == $passmatch)
			{
				//contacts::mod_user_pword($val, $newpass);
			}
			else
			{
				echo "<h3>Passwords do not match</h3><br >\n";
			}
		}
		else
		{
			echo "<b>You can't do that</b><br >\n";
		}
	
	}


	//edit or view contact information
	if (($_POST["action"] == "edit") || array_key_exists("contact", $_GET))
	{
		$currentUser->single();
	}
	else if ($_POST["action"] == "create")
	{
		echo "<h3>Creating new contact:</h3>\n<br >\n";
		$currentUser->make_form(0, '', '', '',
			'', '', '', '', '',
			'', '', '', '', '', '', '');
	}
	else if ($_POST["action"] == "")
	{	//display all contacts
		$currentUser->table();
	}
}
catch (\webAdmin\ConfigurationMissingException $e)
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
	echo "<b>Please login</b>\n" .
		"<form action=\"" . \webAdmin\curPageURL($config) . "\" method=\"post\" autocomplete=\"on\" >\n" .
		"	<input type=\"hidden\" name=\"action\" value=\"login\">\n" .
		"	<label for=\"username\"> Username: <input type=\"text\" name=\"username\" autocomplete=\"on\" ><br>\n" .
		"	<label for=\"password\"> Password: <input type=\"password\" name=\"password\" autocomplete=\"on\" ><br>\n" .
		"	<input class=\"buttons\" type=\"submit\" name=\"do_login\" value=\"Login\">\n" .
		"</form>\n";
	if ($config['allow_user_create'] == 1)
	{
		echo "<form action=\"" . \webAdmin\curPageURL($config) . "\" method=\"post\">\n" .
			 "	<input type=\"hidden\" name=\"action\" value=\"register\">\n" .
			 "	<input class=\"buttons\" type=\"submit\" value=\"Register\">\n" .
			 "</form>\n";
	}
}
catch (\webAdmin\NotLoggedInException $e)
{
	echo "<b>Please login</b>\n" .
		"<form action=\"" . \webAdmin\curPageURL($config) . "\" method=\"post\" autocomplete=\"on\" >\n" .
		"	<input type=\"hidden\" name=\"action\" value=\"login\">\n" .
		"	<label for=\"username\"> Username: <input type=\"text\" name=\"username\" autocomplete=\"on\" ><br>\n" .
		"	<label for=\"password\"> Password: <input type=\"password\" name=\"password\" autocomplete=\"on\" ><br>\n" .
		"	<input class=\"buttons\" type=\"submit\" name=\"do_login\" value=\"Login\">\n" .
		"</form>\n";
	if ($config['allow_user_create'] == 1)
	{
		echo "<form action=\"" . \webAdmin\curPageURL($config) . "\" method=\"post\">\n" .
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
