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

try
{
	$config = parse_ini_file("config.ini");
	test_config($config);
	
	global $mysql_db;
	openDatabase($config);

	require_once("include/jobs.php");
	require_once("include/finance.php");

	?>
	<title>Control Panel: <?php sitename($config)?></title>
	<?php do_css() ?>
	</head>
	<body>

	<script type="text/javascript" src="jquery-1.2.1.pack.js"></script>
	<script type="text/javascript" src="jscript.js"></script>

	<?php

	$stop = 0;
	if (login_code(0) == 1)
	{
		$stop = 1;
	}

	if ($stop == 0)
	{
		do_top_menu(6);
	
		echo "Time based filtering:\n";
		selectTimePeriod();
	
		echo "<b>Possible job status</b><br>\n";
		jobs::table_of_job_status();

		echo "<b>Possible transaction categories</b><br>\n";
		finance::table_of_transaction_categories();

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
				
