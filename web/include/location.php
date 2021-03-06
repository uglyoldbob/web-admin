<?php

if ('location.php' == basename($_SERVER['SCRIPT_FILENAME']))
	throw new \webAdmin\PermissionDeniedException();


function do_loc($apply)
{
	global $mysql_db;
	global $location;
	if ($apply == 1)
	{
		echo "You added : <br >\n";
		$query = "INSERT INTO locations (owner, position, description, location, img_id) VALUES ";
		$first_record = 0;
		//TODO: the configurable number of locations also goes here
		for ($i = 0; $i < 5; $i++)
		{
			if ($_POST['data'][$i]['description'] != "")
			{
				$pid = 'NULL';
				if ($first_record == 1)
				{
					$query = $query . ", ";
				}
				else
				{
					upload_image($_FILES['file'], 5, "location", $pid);
				}
				$query = $query . "(\"" . $_SESSION['user']['emp_id'] . "\", \"" . 
					$mysql_db->real_escape_string($_POST['position']) . "\", \"" .
					$mysql_db->real_escape_string($_POST['data'][$i]['description']) . "\", \"" .
					$mysql_db->real_escape_string($_POST['data'][$i]['location']) . "\", \"" . 
					$pid . "\")";
				$first_record = 1;
				
				echo $mysql_db->real_escape_string($_POST['data'][$i]['description']) . ", " . 
					$mysql_db->real_escape_string($_POST['data'][$i]['location']) . "<br >\n";
			}
		}
		if ($first_record != 0)
		{
			$query = $query . ";";
		
			if (!$mysql_db->query($query))
			{
				throw new Exception("Error: " . $mysql_db->error . "<br >\n");
			}
			else
			{
				echo "Locations added successfully.<br >\n";
			}

		}
		else
		{
			echo "No locations were added.<br >\n";
		}
		$_POST["action"] = "";
	}
	else
	{
		//TODO: add configuration element to specify the number of locations to have spots for
		echo "<form method='post' enctype='multipart/form-data'>\n";
		echo "<table border=\"1\">\n";
		echo "	<tr>\n";
		echo "		<th>Description</th>\n";
		echo "		<th>Location</th>\n";
		echo "		<th>Photo</th>\n";
		echo "	</tr>\n";

		for ($i = 0; $i < 5; $i++)
		{
			echo "	<tr>\n";
			echo "		<td>\n";
			echo "			<input name=\"data[" . $i . "][description]\" type=\"text\" />\n";
			echo "		</td>\n";
			echo "		<td>\n";
			echo "			<input name=\"data[" . $i . "][location]\" type=\"text\" />\n";
			echo "		</td>\n";

			echo "		<td>";
			if ($i == 0)
			{	//only allow upload on the first file
				//TODO: possibly allow uploads for more than the first location?
				echo "\n			<input type=\"file\" name=\"file\" id=\"file\">\n";
				echo "		";
			}
			else
			{
				echo "&nbsp;";
			}
			echo "</td>\n";	
			echo "	</tr>\n";
		}

		echo "</table><br>\n";
		echo "  <input type=\"hidden\" name=\"position\" value=" . $location . "><br >\n";
		echo "	<input type=\"hidden\" name=\"action\" value=\"do_loc\"><br>\n";
		echo "	<input class=\"buttons\" type='submit' value='Add these locations' >\n";
		echo "</form>\n";

		echo '<a href="' . rootPageURL() . '/locations.php?id=' . $location . '">Nevermind, don\'t add locations</a>' . "<br >\n";
	}
}

function do_equ($apply)
{
	global $mysql_db;
	global $location;
	if ($apply == 1)
	{
		echo "You added : <br >\n";
		$query = "INSERT INTO equipment (owner, location, quantity, unit, name, description, img_id) VALUES ";
		$first_record = 0;
		for ($i = 0; $i < 10; $i++)
		{
			if ($_POST['data'][$i]['quantity'] != "")
			{
				$pid = "NULL";
				if ($first_record == 1)
				{
					$query = $query . ", ";
				}
				else
				{
					upload_image($_FILES['file'], 5, "equipment", $pid);
				}
				$query = $query . "(\"" . $_SESSION['user']['emp_id'] . "\", \"" . 
					$mysql_db->real_escape_string($_POST['position']) . "\", \"" .
					$mysql_db->real_escape_string($_POST['data'][$i]['quantity']) . "\", \"" .
					$mysql_db->real_escape_string($_POST['data'][$i]['unit']) . "\", \"" .
					$mysql_db->real_escape_string($_POST['data'][$i]['name']) . "\", \"" .
					$mysql_db->real_escape_string($_POST['data'][$i]['description']) . "\", \"" .
					$pid . "\")";
				$first_record = 1;
				
				echo $mysql_db->real_escape_string($_POST['data'][$i]['quantity']) . " " . 
					$mysql_db->real_escape_string($_POST['data'][$i]['unit']) . " of " . 
					$mysql_db->real_escape_string($_POST['data'][$i]['name']) . "<br >\n";
			}
		}
		if ($first_record != 0)
		{
			$query = $query . ";";
		
			if (!$mysql_db->query($query))
			{
				throw new Exception("Error: " . $mysql_db->error . "<br >\n");
			}
			else
			{
				echo "Equipment added successfully.<br >\n";
			}

		}
		else
		{
			echo "No equipment was added.<br >\n";
		}
		$_POST["action"] = "";
	}
	else
	{
		//TODO: add configuration element to set the number of pieces to add at a time
		//TODO: use this value to trigger uploading a photo?
		//TODO: perhaps only allow uploading a photo for the first piece of equipment
		echo "<form method='post' enctype='multipart/form-data'>\n";
		echo "<table border=\"1\">\n";
		echo "	<tr>\n";
		echo "		<th>Quantity</th>\n";
		echo "		<th>Units</th>\n";
		echo "		<th>Name</th>\n";
		echo "		<th>Description</th>\n";
		echo "		<th>Photo</th>\n";
		echo "	</tr>\n";

		for ($i = 0; $i < 10; $i++)
		{
			echo "	<tr>\n";
			
			echo "		<td>";
			echo "<input name=\"data[" . $i . "][quantity]\" type=\"text\" />";
			echo "		</td>\n";
			
			echo "		<td>";
			echo "<input name=\"data[" . $i . "][unit]\" type=\"text\" />";
			echo "		</td>\n";
			
			echo "		<td>";
			echo "<input name=\"data[" . $i . "][name]\" type=\"text\" />";
			echo "		</td>\n";
			
			echo "		<td>";
			echo "<input name=\"data[" . $i . "][description]\" type=\"text\" />";
			echo "		</td>\n";

			echo "		<td>";

			if ($i == 0)
			{
				echo "\n			<input type=\"file\" name=\"file\" id=\"file\">\n";
				echo "		";
			}
			else
			{
				echo "&nbsp;";
			}
			echo "</td>\n";
	
			echo "	</tr>\n";
		}

		echo "</table><br>\n";
		echo "  <input type=\"hidden\" name=\"position\" value=" . $location . "><br >\n";
		echo "	<input type=\"hidden\" name=\"action\" value=\"do_equ\"><br>\n";
		echo "	<input class=\"buttons\" type='submit' value='Add this equipment' >\n";
		echo "</form>\n";
		
		echo '<a href="' . rootPageURL() . '/locations.php?id=' . $location . '">Nevermind, don\'t add equipment</a>' . "<br >\n";
	}
}


class locations
{
	function __construct()
	{
	}	

	public static function create_user_first_location($uid)
	{
		global $mysql_db, $config;
		$query = "INSERT INTO `locations` (owner)" .
							" VALUES ('" . $uid . "');";
		$results = $mysql_db->query($query);
		
		$query = "SELECT * FROM locations WHERE owner='" . $uid . "' LIMIT 1;";
		$results = $mysql_db->query($query);
		if ($results)
		{
			if ($results->num_rows != 0)
			{
				$row = $results->fetch_array(MYSQLI_BOTH);
				return $row['id'];	//username exists
			}
		}
		return null;
	}
}

?>
