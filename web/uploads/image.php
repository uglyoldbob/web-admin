<?php
chdir("../");
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

try
{
	$config = parse_ini_file("config.ini");
	test_config($config);

	global $mysql_db;
	$mysql_db = openDatabase($config);
	
	$cust_session = new \webAdmin\session($config, $mysql_db, "sessions");
	start_my_session();	//start php session
	
	$currentUser = new \webAdmin\user($config, $mysql_db, "users");
	$currentUser->certificate_tables("root_ca", "intermediate_ca");

	$currentUser->require_login(1);
	
	if (!(array_key_exists("id", $_GET)))
		throw new Exception("Id not specified");
	
	$filename = $mysql_db->real_escape_string($_GET['id']);
	$thumb = @$_GET['thumb'];
	if (is_numeric($thumb) == FALSE)
		$location = 0;
	//verify permissions first
	$query = "SELECT * FROM images WHERE id='" . $filename . "'";
	$results = $mysql_db->query($query);
	$permission = false;
	if ($results && ($results->num_rows > 0))
	{
		if ($row = $results->fetch_array(MYSQLI_BOTH))
		{
			$permission = check_for_read_permission($filename);
		}
		else
		{
			$error = "Invalid image.";
		}
	}
	else
	{
		$error = "Invalid image.";
	}

	if ($permission == false)
	{
		if (!headers_sent())
		{
			header('Content-type: text/html; charset=utf-8');
		}
		echo "<!DOCTYPE HTML SYSTEM>\n" . 
			 "<html>\n" . 
			 "<head>\n" .
			 "<title>" .sitename($config) . "</title>\n";
		do_css($config);
		echo	 "</head>\n" .
			 "<body>\n" .
			 "<h3>The image cannot be retrieved.</h3>\n<br >\n" .
			 $error .
			 "\n</body>\n" .
			 "\n</html>";
	}
	else
	{
		if ($thumb != 0)
		{
			$file_path=$_SERVER['DOCUMENT_ROOT'].$config['location'].'/'.$row['file_thumb'];
		}
		else
		{
			$file_path=$_SERVER['DOCUMENT_ROOT'].$config['location'].'/'.$row['file_vga'];
		}

		$img = new SimpleImage();
		$img->load($file_path);

		ob_start();
		if (!headers_sent())
		{
			header('Content-Type: image/jpeg');
		}
		ob_end_flush();

		$img->output();
	}
}
catch (\webAdmin\ConfigurationMissingException $e)
{
	?>
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
}
catch (Exception $e)
{
	?>
	<h1>Error</h1>
	<?php
	if (isset($_GET['debug']) || ($config['debug']==1))
	{
		echo "Details: " . (string)$e . "<br />\n";
	}
}

?>
	
