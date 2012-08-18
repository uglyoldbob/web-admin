<?php
include("../global.php");

start_my_session();
login_code();
openDatabase();
login_button(1);

$filename = $mysql_db->real_escape_string($_GET['id']);
$filesize = 5;

//verify permissions first

$query = "SELECT * FROM inspections WHERE report='" . $filename . ".pdf'";
$results = $mysql_db->query($query);
$permission = false;
if ($row = @$results->fetch_array(MYSQLI_BOTH))
{
	if (checkPermission($database, 2))
	{	//no need to check for the inspector (we are good)
		$permission = true;
	}
	else
	{
		if ($row['inspector'] == $_SESSION['user']['emp_id'])
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
	$error = "Invalid inspection report.";
}

if ($permission == false)
{
	header('Content-type: text/html; charset=utf-8');
	echo "<!DOCTYPE HTML SYSTEM>\n" . 
		 "<html>\n" . 
		 "<head>\n" .
		 "<title>Thermal Specialists Payment Details</title>\n" .
		 "</head>\n" .
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
 
		if($file_extension!='pdf')
		{
			header('Content-type: text/html; charset=utf-8');
			die('LOGGED! bad extension');
		}

		ob_start();
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="report.pdf"');
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
	echo "<!DOCTYPE HTML SYSTEM>" . 
		 "<html>" . 
		 "<head>" .
		 "<title>Thermal Specialists Payment Details</title>" .
		 "</head>" .
		 "<body>" .
		 "<h3>The report cannot be retrieved.</h3>" .
		 "</body>" .
		 "</html>";
	exit();
}

closeDatabase();
?>