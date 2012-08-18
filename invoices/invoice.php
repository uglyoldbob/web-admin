<?php
include("../global.php");

start_my_session();
openDatabase();

$stop = 0;
echo '<div>' . "\n";
if (login_code(1) == 1)
{
	$stop = 1;
}
echo "</div>\n";

if ($stop == 0)
{
	
	$filename = $mysql_db->real_escape_string($_GET['id']);
	$filesize = 5;
	
	//verify permissions first
	
	$query = "SELECT * FROM payments WHERE invoice='" . $filename . ".pdf'";
	$results = $mysql_db->query($query);
	$permission = false;
	if ($row = @$results->fetch_array(MYSQLI_BOTH))
	{
		//permission is valid when they are logged in (probably too simple)
		$permission = true;
	}
	else
	{
		$error = "Invalid inspection report: " . $filename . ".pdf";
	}
	
	if ($permission == false)
	{
		header('Content-type: text/html; charset=utf-8');
		echo "<!DOCTYPE HTML>\n" . 
			 "<html>\n" . 
			 "<head>\n" .
			 "<title>Thermal Specialists Invoice</title>\n" .
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
		$file_path=$_SERVER['DOCUMENT_ROOT'].'/invoices/'.strip_tags(htmlentities($filename . ".pdf"));
	
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
			header('Content-Disposition: attachment; filename="invoice.pdf"');
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
			 "<title>Thermal Specialists Invoice</title>" .
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