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

require_once("global.php");

start_my_session();	//start php session
if (!headers_sent())
{
	header('Content-type: text/html; charset=utf-8');
}

?>
<!DOCTYPE HTML>
<html>
<head>
<?php

require_once("include/forms.php");

try
{
	$config = parse_ini_file("config.ini");
	test_config($config);

	$jobs = new jobs();

	openDatabase($config);

	?>
	<title>Jobs List: <?php sitename($config)?></title>
	<?php do_css() ?>
	</head>

	<body>

	<script type="text/javascript" src="jquery-1.2.1.pack.js"></script>
	<script type="text/javascript" src="jscript.js"></script>

	<?php

	#TODO : create a job_status_codes table
	#TODO : use the job_status_codes table for statuses of jobs


	//make sure the user is logged in properly
	$stop = 0;
	if (login_code(0) == 1)
	{
		$stop = 1;
	}
	if ($stop == 0)
	{
		do_top_menu(3);
	
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
			echo "<form action=\"" . rootPageURL() . "/jobs.php\" method=\"post\">\n" .
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
	
		//bcmul, bcadd,
		//
	}

	/*
	$query = "SHOW SESSION STATUS";
	$result = $mysql_db->query($query);
	while ($row = $result->fetch_array(MYSQLI_BOTH))
	{
		echo $row[0] . " " . $row[1];
		echo "<br >\n";
	}*/

	//this function is undefined
	//print_r(mysqli_get_client_stats());

	closeDatabase();
}
catch (\webAdmin\ConfigurationMissingException $e)
{
	?>
	<title>Site Configuration Error</title>
	<?php do_css() ?>
	</head>
	<body>
	<h1>Site configuration error</h1>
	<?php
}
catch (\webAdmin\DatabaseConnectionFailedException $e)
{
	?>
	<title>Site Configuration Error</title>
	<?php do_css() ?>
	</head>
	<body>
	<h1>Site configuration error</h1>
	<?php
}
catch (\webAdmin\PermissionDeniedException $e)
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
