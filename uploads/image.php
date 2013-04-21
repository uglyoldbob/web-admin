<?php
include("../global.php");
include("../include/upload_file.php");

start_my_session();
openDatabase();

$stop = 0;
if (login_code(1) == 1)
{
        $stop = 1;
	die('stuff');
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
		 "<title>Thermal Specialists</title>\n" .
		 "<link rel=\"stylesheet\" type=\"text/css\" href=\"../css/global.css\" />\n" .
		 "</head>\n" .
		 "<body>\n" .
		 "<h3>The image cannot be retrieved.</h3>\n<br >\n" .
		 $error .
		 "\n</body>\n" .
		 "\n</html>";
	exit();
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

	exit;
	exit();

	header('Content-type: text/html; charset=utf-8');
	echo "<!DOCTYPE HTML SYSTEM>" . 
		 "<html>" . 
		 "<head>" .
		 "<title>Thermal Specialists</title>" .
		 "<link rel=\"stylesheet\" type=\"text/css\" href=\"../css/global.css\" />\n" .
		 "</head>" .
		 "<body>" .
		 "<h3>The image cannot be retrieved.</h3>" .
		 "</body>" .
		 "</html>";
	exit();
}

closeDatabase();
?>
