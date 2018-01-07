<?php
require_once(dirname(__FILE__) . "/../global.php");
include(dirname(__FILE__) . "/../include/upload_file.php");
require_once(dirname(__FILE__) . "/../include/exceptions.php");

start_my_session();	//start php session

try
{
	$config = parse_ini_file(dirname(__FILE__) . "/../config.ini");
	test_config();

	global $mysql_db;
	openDatabase();
	$stop = 0;
	if (login_code(1) == 1)
	{
		$stop = 1;
		throw new Exception('stuff');
	}

	$filename = $mysql_db->real_escape_string($_GET['id']);
	$thumb = @$_GET['thumb'];
	if (is_numeric($thumb) == FALSE)
		$location = 0;
	//verify permissions first
	global $config;
	$query = "SELECT * FROM images WHERE id='" . $filename . "'";
	$results = $mysql_db->query($query);
	$permission = false;
	$row;
	if ($row = @$results->fetch_array(MYSQLI_BOTH))
	{
		$permission = check_for_read_permission($filename);
	}
	else
	{
		$error = "Invalid image.";
	}

	if ($permission == false)
	{
		header('Content-type: text/html; charset=utf-8');
		echo "<!DOCTYPE HTML SYSTEM>\n" . 
			 "<html>\n" . 
			 "<head>\n" .
			 "<title>" .sitename() . "</title>\n";
		do_css();
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
		header('Content-Type: image/jpeg');
		ob_end_flush();

		$img->output();
	}
	closeDatabase();
}
catch (ConfigurationMissingException $e)
{
	?>
	<h1>Site configuration error</h1>
	<?php
}
catch (DatabaseConnectionFailedException $e)
{
	?>
	<h1>Site configuration error</h1>
	<?php
}
catch (PermissionDeniedException $e)
{
	?>
	<h1>Permission Denied</h1>
	<?php
}
catch (Exception $e)
{
	?>
	<h1>Error</h1>
	<?php
}

?>
	
