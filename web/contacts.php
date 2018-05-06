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
		echo "	<title>Contact Listing: ";
		\webAdmin\sitename($config);
		echo "</title>\n";
		\webAdmin\do_css($config);
		echo "</head>\n";
		echo "<body>\n";
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
		echo "<b>A certificate is required to access this page</b><br />\n";
		if (isset($_GET['debug']) || ($config['debug']==1))
		{
			echo "Details: " . (string)$e . "<br />\n";
		}
	}
}
?>

</body>
</html>
