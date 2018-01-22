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
include_once("include/upload_file.php");

start_my_session();	//start php session

try
{
	$config = parse_ini_file("config.ini");
	test_config($config);

	global $mysql_db;
	openDatabase($config);
	$stop = 0;
	if (login_code(1, $config) == 1)
	{
		$stop = 1;
		throw new Exception('stuff');
	}

	$filename = $mysql_db->real_escape_string($_GET['id']);
	$thumb = @$_GET['thumb'];
	if (is_numeric($thumb) == FALSE)
		$location = 0;
	//verify permissions first
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
	closeDatabase();
}
catch (\webAdmin\ConfigurationMissingException $e)
{
	?>
	<h1>Site configuration error</h1>
	<?php
}
catch (\webAdmin\DatabaseConnectionFailedException $e)
{
	?>
	<h1>Site configuration error</h1>
	<?php
}
catch (\webAdmin\PermissionDeniedException $e)
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
	
