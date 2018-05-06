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
require_once("webAdmin/table.php");
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
		echo "	<title>Control Panel: ";
		\webAdmin\sitename($config);
		echo "</title>\n";
		\webAdmin\do_css($config);
		echo "</head>\n";
		echo "<body>\n";
		echo "	<script type=\"text/javascript\" src=\"jquery-1.2.1.pack.js\"></script>\n";
		echo "	<script type=\"text/javascript\" src=\"jscript.js\"></script>\n";

		\webAdmin\do_top_menu(6, $config);

		echo "Time based filtering:\n";
		\webAdmin\selectTimePeriod();

		echo "<b>Possible job status</b><br>\n";
		\webAdmin\jobs::table_of_job_status();

		echo "<b>Possible transaction categories</b><br>\n";
		\webAdmin\finance::table_of_transaction_categories();
		
		$currentUser->revoke_own_certificates();
		
		if ($_POST["action"] == "register_cert")
		{
			try
			{
				$currentUser->register_certificate();
			}
			catch (\webAdmin\CertificateException $e)
			{
				echo "Failed to register certificate: " . (string)$e . "<br />\n";
			}
		}
		
		$stuff = $currentUser->registered_certs_data();
		\webAdmin\double_array_table($stuff);
		echo "<br />\n";
		$currentUser->show_register_certificate_button();
		echo "<br />\n";

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
	catch (\webAdmin\PermissionDeniedException $e)
	{
		echo "<head>\n";
		echo "	<title>Permission Denied</title>\n";
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
		echo "	<title>Permission Denied</title>\n";
		\webAdmin\do_css($config);
		echo "</head>\n";
		echo "<body>\n";
		echo "	<h3>Invalid username or password</h3>\n";
		$currentUser->login_form();
	}
	catch (\webAdmin\NotLoggedInException $e)
	{
		echo "<head>\n";
		echo "	<title>Permission Denied</title>\n";
		\webAdmin\do_css($config);
		echo "</head>\n";
		echo "<body>\n";
		$currentUser->login_form();
	}
	catch (\webAdmin\CertificateException $e)
	{
		echo "<head>\n";
		echo "	<title>Permission Denied</title>\n";
		\webAdmin\do_css($config);
		echo "</head>\n";
		echo "<body>\n";
		echo "	<b>A certificate is required to access this page</b><br />\n";
		if (isset($_GET['debug']) || ($config['debug']==1))
		{
			echo "Details: " . (string)$e . "<br />\n";
		}
	}
}

?>

</body>
</html>
				
