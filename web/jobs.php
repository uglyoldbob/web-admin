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
	<title>Jobs List: <?php sitename($config)?></title>
	<?php do_css($config) ?>
	</head>

	<body>

	<script type="text/javascript" src="jquery-1.2.1.pack.js"></script>
	<script type="text/javascript" src="jscript.js"></script>

	<?php
	$currentUser = new \webAdmin\user($config, $mysql_db, "users");
	$currentUser->certificate_tables("root_ca", "intermediate_ca", "user_certs");

	$currentUser->show_register_certificate_button();
	$currentUser->require_login_or_registered_certificate();
	
	#TODO : create a job_status_codes table
	#TODO : use the job_status_codes table for statuses of jobs


	
	
	$jobs = new \webAdmin\jobs($config);

	do_top_menu(3, $config);

	if ($_POST["action"] == "apply")
	{	//apply job data
		$jobs->create_job($_POST);
		$_POST["action"] = "";	//transition to listing the newly created job
	}
	else if ($_POST["action"] == "modjob")
	{
		$jobs->modify_job($_POST);
		$_POST["action"] = "";	//transition to listing the newly created job
	}
	else if ($_POST["action"] == "add_payment")
	{
		if (isset($_SESSION['payment_reference']))
		{
			$expense_query = "INSERT INTO job_expenses (job_id, payment_id) VALUES (" .
				$mysql_db->real_escape_string($_GET['job']) . ", " . $mysql_db->real_escape_string($_SESSION['payment_reference']) . ");";
			$mysql_db->query($expense_query);
		}
		$_POST["action"] = "";	//transition to listing the newly modified job
	}
	else if ($_POST["action"] == "remove_expense")
	{
		$expense_query = "DELETE from job_expenses WHERE job_id=" .
			$mysql_db->real_escape_string($_GET['job']) . " AND payment_id=" . $mysql_db->real_escape_string($_POST['id']) . " LIMIT 1;";
		$mysql_db->query($expense_query);
		$_POST["action"] = "";	//transition to listing the newly modified job
	}

	if ($_POST["action"] != "edit")
	{	//don't create the new job button if the create job form is going to be displayed
		//don't create the new job button if a specific job is going to be displayed
		echo "<form action=\"" . rootPageURL($config) . "/jobs.php\" method=\"post\">\n" .
			 "	<input type=\"hidden\" name=\"action\" value=\"edit\">\n" .
			 "	<input type=\"hidden\" name=\"id\" value=\"0\">\n" .
			 "	<input class=\"buttons\" type=\"submit\" value=\"Create new job\"/>\n" .
			 "</form>";
	}
	if (($_POST["action"] == "edit") || ($jobs->job != 0))
	{
		if ($jobs->job != 0)
		{	//do this when listing information for a specific job
			$jobs->list_job();			
		}
		else
		{	//do this when creating a new job
			echo "<h3>Creating new job:</h3>\n";
			$jobs->new_job_form();
		}
	}
	else	//if (($_POST["action"] == "")
	{	//do this when listing all jobs
		$jobs->table();
	}
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
