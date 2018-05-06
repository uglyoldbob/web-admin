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

\webAdmin\runSite();

function website($mysql_db, $config, $cust_session)
{
	if (!headers_sent())
	{
		header('Content-type: text/html; charset=utf-8');
	}
	echo "<!DOCTYPE HTML>\n";
	echo "<html>\n";
	try
	{
		$currentUser = new \webAdmin\user($config, $mysql_db, "users");
		$currentUser->certificate_tables("root_ca", "intermediate_ca", "user_certs");
	
		$currentUser->require_login_or_registered_certificate();
	
		echo "<head>\n";
		echo "	<title>";
		\webAdmin\sitename($config);
		echo "</title>\n";
		\webAdmin\do_css($config);
		echo "</head>\n";
		echo "<body>\n";
		\webAdmin\do_top_menu(0, $config);
		echo "Something goes here?<br>\n";	
	}
	catch (\webAdmin\PermissionDeniedException $e)
	{
		echo "<head>\n";
		echo "	<title>Permission Denied</title>\m";
		\webAdmin\do_css($config);
		echo "</head>\n";
		echo "<body>\n";
		echo "	<h1>Permission Denied</h1>\n";
		if (isset($_GET['debug']) || ($config['debug']==1))
		{
			echo "Details: " . (string)$e . "<br />\n";
		}
	}
	catch (\webAdmin\InvalidUsernameOrPasswordException $e)
	{
		echo "<head>\n";
		echo "	<title>Permission Denied</title>\m";
		\webAdmin\do_css($config);
		echo "</head>\n";
		echo "<body>\n";
		echo "<h3>Invalid username or password</h3>\n";
		$currentUser->login_form();
		if (isset($_GET['debug']) || ($config['debug']==1))
		{
			echo "Details: " . (string)$e . "<br />\n";
		}
	}
	catch (\webAdmin\NotLoggedInException $e)
	{
		echo "<head>\n";
		echo "	<title>Permission Denied</title>\m";
		\webAdmin\do_css($config);
		echo "</head>\n";
		echo "<body>\n";
		$currentUser->login_form();
		if (isset($_GET['debug']) || ($config['debug']==1))
		{
			echo "Details: " . (string)$e . "<br />\n";
		}
	}
	catch (\webAdmin\CertificateException $e)
	{
		echo "<head>\n";
		echo "	<title>Permission Denied</title>\m";
		\webAdmin\do_css($config);
		echo "</head>\n";
		echo "<body>\n";
		echo "<b>A certificate is required to access this page</b><br />\n";
	}
	echo "</body>\n";
}

?>
</html>
