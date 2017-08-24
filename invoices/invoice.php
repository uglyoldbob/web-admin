<?php
$config = parse_ini_file("../config.ini");
include("../global.php");
start_my_session();	//start php session

header('Content-type: text/html; charset=utf-8');

global $mysql_db;
global $config;
openDatabase();

?>
<!DOCTYPE HTML>
<html>
<head>
<title></title>

<?php

$stop = 0;
if (login_code(1) == 1)
{
	$stop = 1;
}

if ($stop == 0)
{
	
	$filename = $mysql_db->real_escape_string($_GET['id']);
	$filesize = 5;
	
	//verify permissions first
	
	$query = "SELECT * FROM payments WHERE invoice='" . $filename . "'";
	$results = $mysql_db->query($query);
	$permission = false;
	if ($row = @$results->fetch_array(MYSQLI_BOTH))
	{
		//permission is valid when they are logged in (probably too simple)
		$permission = true;
	}
	else
	{
		$error = "Invalid invoice: " . $filename;
	}
    $permission = true;
	
	if ($permission == false)
	{
		header('Content-type: text/html; charset=utf-8');
		echo "<!DOCTYPE HTML>\n" . 
			 "<html>\n" . 
			 "<head>\n" .
			 "<title>" . sitename() . " Invoice</title>\n" .
			 "</head>\n" .
			 "<body>\n" .
			 "<h3>The invoice cannot be retrieved.</h3>\n<br >\n" .
			 $error .
			 "\n</body>\n" .
			 "\n</html>";
		exit();
	}
	else
	{
		$file_path=$_SERVER['DOCUMENT_ROOT'].$config['location'].'/invoices/'.strip_tags(htmlentities($filename));
	
		if ($fp = fopen ($file_path, "r"))
		{
			$file_size = filesize($file_path);
			$file_info = pathinfo($file_path);
			$file_extension = strtolower($file_info["extension"]);
	 
			if($file_extension!='pdf')
			{
				header('Content-type: text/html; charset=utf-8');
				die('LOGGED! bad extension');
			}
	
			ob_start();
			header('Content-type: application/pdf');
			header('Content-Disposition: attachment; filename=' . $filename);
			header("Content-length: $file_size");
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
		echo "<!DOCTYPE HTML>" . 
			 "<html>" . 
			 "<head>" .
			 "<title>" . sitename() . " Invoice</title>" .
			 "</head>" .
			 "<body>" .
			 "<h3>The invoice cannot be retrieved.</h3>" .
			 "</body>" .
			 "</html>";
		exit();
	}
}

closeDatabase();
?>
