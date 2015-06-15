<?php
include("../global.php");

start_my_session();
login_code();
$database = openDatabase();
quiet_login($database);

$filename = mysql_real_escape_string($_GET['id']);
$filesize = 5;

//verify permissions first

$query = "SELECT * FROM inspections WHERE report='" . $filename . ".pdf'";
$results = mysql_query($query, $database);
$permission = false;
if ($row = @mysql_fetch_array($results))
{
	if (checkPermission($database, 2))
	{	//no need to check for the inspector (we are good)
		$permission = true;
	}
	else
	{
		if ($row['inspector'] == $_SESSION['id'])
		{	//good
			$permission = true;
		}
		else
		{
			$error = "You did not inspect this property.";
		}
	}
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
		 "<title>Thermal Specialists Payment Details</title>\n" .
	do_css();
	echo	 "</head>\n" .
		 "<body>\n" .
		 "<h3>The report cannot be retrieved.</h3>\n<br >\n" .
		 $error .
		 "\n</body>\n" .
		 "\n</html>";
	exit();
}
else
{
	$file_path=$_SERVER['DOCUMENT_ROOT'].'/reports/'.strip_tags(htmlentities($filename . ".pdf"));

	if ($fp = fopen ($file_path, "r"))
	{
		$file_size = filesize($file_path);
		$file_info = pathinfo($file_path);
		$file_extension = strtolower($file_info["extension"]);
 
		if($file_extension!='jpg')
		{
			header('Content-type: text/html; charset=utf-8');
			die('LOGGED! bad extension');
		}

		ob_start();
		header('Content-type: image/jpeg');
		ob_end_flush();
		
		while(!feof($fp)) 
		{
			$file_buffer = fread($fp, 2048);
			echo $file_buffer;
		}
 
		fclose($fp);

		exit;
		exit();
	}
	header('Content-type: text/html; charset=utf-8');
	echo "<!DOCTYPE HTML SYSTEM>" . 
		 "<html>" . 
		 "<head>" .
		 "<title>Thermal Specialists</title>";
	do_css();
	echo	 "</head>" .
		 "<body>" .
		 "<h3>The image cannot be retrieved.</h3>" .
		 "</body>" .
		 "</html>";
	exit();
}

?>
