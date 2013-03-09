<?php
include("global.php");
start_my_session();	//start php session
header('Content-type: text/html; charset=utf-8');

global $mysql_db;
openDatabase();

$location = $_GET["id"];
if (is_numeric($location) == FALSE)
	$location = 0;

$query = "SELECT * FROM locations WHERE owner = " . $_SESSION['user']['emp_id'] . " AND id = position;";
$results = $mysql_db->query($query);
if ($row = $results->fetch_array(MYSQLI_BOTH))
{
	if (($location == 0) || ($row['id'] == $location))
	{
		$root_number = $row['id'];
		$location = $root_number;
		$root_location = 1;
	}
	else
	{
		$root_location = 0;
	}
}
else
{
	$root_location = 0;
}
	
?>

<!DOCTYPE HTML>
<html>
<head>
<title>Thermal Specialists Management System</title>
<link rel="stylesheet" type="text/css" href="css/global.css" />
</head>
<body>

<?php

$stop = 0;
echo '<div>' . "\n";
if (login_code(0) == 1)
{
	$stop = 1;
}
echo "</div>\n";

if ($stop == 0)
{
	echo '<a href="' . rootPageURL() . '">Return to main</a>' . "<br >\n";

	if ($_POST["action"] == "del_loc")
	{
		if ($root_location == 1)
		{
			die('You cannot delete this location');
		}
		
		$scan_loc = 0;
		
		$query = "SELECT * FROM locations WHERE owner = " . $_SESSION['user']['emp_id'] . " AND id = " . $location . ";";
		$results = $mysql_db->query($query);
		if ($row = $results->fetch_array(MYSQLI_BOTH))
		{
			$return_to = $row['position'];
		}
		
		$query = "SELECT * FROM locations WHERE owner = " . $_SESSION['user']['emp_id'] . " AND position = " . $location . ";";
		$results = $mysql_db->query($query);
		if ($row = $results->fetch_array(MYSQLI_BOTH))
		{
			$scan_loc = 1;
		}
		
		$query = "SELECT * FROM equipment WHERE owner = " . $_SESSION['user']['emp_id'] . " AND location = " . $location . ";";
		$results = $mysql_db->query($query);
		if ($row = $results->fetch_array(MYSQLI_BOTH))
		{
			$scan_loc = 1;
		}
		
		if ($scan_loc == 1)
		{
			die('This location cannot be deleted because it contains locations/equipment');
		}
		
		$query = "DELETE FROM locations WHERE owner = " . $_SESSION['user']['emp_id'] . " AND id = " . $location . ";";
		if (!$mysql_db->query($query))
		{
			echo "Error: " . $mysql_db->error . "<br >\n";
			echo $query . "<br >\n";
			//die('Error: ' . $mysql_db->error);
		}
		else
		{
			echo "Location deleted successfully.<br >\n";
		}

		$location = $return_to;
		$_POST["action"] = "";
	}

	if ($_POST["action"] == "do_loc")
	{
		echo "You added : <br >\n";
		$query = "INSERT INTO locations (owner, position, description, location) VALUES ";
		$first_record = 0;
		for ($i = 0; $i < 5; $i++)
		{
			if ($_POST['data'][$i]['description'] != "")
			{
				if ($first_record == 1)
				{
					$query = $query . ", ";
				}
				$query = $query . "(\"" . $_SESSION['user']['emp_id'] . "\", \"" . 
					$mysql_db->real_escape_string($_POST['position']) . "\", \"" .
					$mysql_db->real_escape_string($_POST['data'][$i]['description']) . "\", \"" .
					$mysql_db->real_escape_string($_POST['data'][$i]['location']) . "\")";
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
				echo "Error: " . $mysql_db->error . "<br >\n";
				echo $query . "<br >\n";
				//die('Error: ' . $mysql_db->error);
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

	if ($_POST["action"] == "add_loc")
	{
		echo "<form method='post'>\n";
		echo "<table border=\"1\">\n";
		echo "	<tr>\n";
		echo "		<th>Description</th>\n";
		echo "		<th>Location</th>\n";
		echo "	</tr>\n";

		for ($i = 0; $i < 5; $i++)
		{
			echo "	<tr>\n";
			echo "		<td>";
			echo "<input name=\"data[" . $i . "][description]\" type=\"text\" />";
			echo "		</td>\n";
			
			echo "		<td>";
			echo "<input name=\"data[" . $i . "][location]\" type=\"text\" />";
			echo "		</td>\n";
			
			echo "	</tr>\n";
		}

		echo "</table><br>\n";
		echo "  <input type=\"hidden\" name=\"position\" value=" . $location . "><br >\n";
		echo "	<input type=\"hidden\" name=\"action\" value=\"do_loc\"><br>\n";
		echo "	<input type='submit' value='Add these locations' >\n";
		echo "</form>\n";
		
		echo '<a href="' . rootPageURL() . '/locations.php?id=' . $location . '">Nevermind, don\'t add locations</a>' . "<br >\n";
	}

	if ($_POST["action"] == "do_equ")
	{
		echo "You added : <br >\n";
		$query = "INSERT INTO equipment (owner, location, quantity, unit, name, description) VALUES ";
		$first_record = 0;
		for ($i = 0; $i < 10; $i++)
		{
			if ($_POST['data'][$i]['quantity'] != "")
			{
				if ($first_record == 1)
				{
					$query = $query . ", ";
				}
				$query = $query . "(\"" . $_SESSION['user']['emp_id'] . "\", \"" . 
					$mysql_db->real_escape_string($_POST['position']) . "\", \"" .
					$mysql_db->real_escape_string($_POST['data'][$i]['quantity']) . "\", \"" .
					$mysql_db->real_escape_string($_POST['data'][$i]['unit']) . "\", \"" .
					$mysql_db->real_escape_string($_POST['data'][$i]['name']) . "\", \"" .
					$mysql_db->real_escape_string($_POST['data'][$i]['description']) . "\")";
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
				echo "Error: " . $mysql_db->error . "<br >\n";
				echo $query . "<br >\n";
				//die('Error: ' . $mysql_db->error);
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

	if ($_POST["action"] == "confirm_del_equ")
	{
		$amount = $_POST['amount'];	//possible number of items to delete
		if (is_numeric($location) == FALSE)
			$location = 0;
			
		$an_error = 0;
			
		for ($i = 0; $i < $amount; $i++)
		{
			$query = "DELETE FROM equipment WHERE owner = " . $_SESSION['user']['emp_id'] . 
				" AND id = " . $mysql_db->real_escape_string($_POST['data'][$i]['id']) . ";";
			if (!$mysql_db->query($query))
			{
				echo "Error: " . $mysql_db->error . "<br >\n";
				echo $query . "<br >\n";
				//die('Error: ' . $mysql_db->error);
				$an_error = 1;
			}
		}
		
		if ($an_error == 0)
		{
			echo "Equipment deleted successfully<br >\n";
		}
		$_POST["action"] = "";
	}

	if ($_POST['action'] == "move_equ")
	{
		$amount = $_POST['amount'];	//possible number of items to delete
		if (is_numeric($location) == FALSE)
			$location = 0;
		
		$move_to = $_POST['move_to'];
		if (is_numeric($move_to) == FALSE)
		{
			die("Invalid location specified");
		}
		
			
		$an_error = 0;
			
		for ($i = 0; $i < $amount; $i++)
		{
			$query = "UPDATE equipment SET location = " . $move_to . " WHERE OWNER = " . $_SESSION['user']['emp_id'] . 
				" AND id = " . $mysql_db->real_escape_string($_POST['data'][$i]['id']) . ";";
			if (!$mysql_db->query($query))
			{
				echo "Error: " . $mysql_db->error . "<br >\n";
				echo $query . "<br >\n";
				//die('Error: ' . $mysql_db->error);
				$an_error = 1;
			}
		}
		
		if ($an_error == 0)
		{
			echo "Equipment moved successfully<br >\n";
		}
		$_POST["action"] = "";

	}

	if (($_POST["action"] == "del_equ") && ($_POST["move"] != ""))
	{
		$amount = $_POST['amount'];	//possible number of items to delete
		
		echo "<form method='POST'>\n";
		for ($i = 0; $i < $amount; $i++)
		{
			if ($mysql_db->real_escape_string($_POST['data'][$i]['delete']) == "on")
			{
				$query = "SELECT * FROM equipment WHERE owner = " . $_SESSION['user']['emp_id'] . " AND id = " .
					$mysql_db->real_escape_string($_POST['data'][$i]['id']) . ";";
				$results =$mysql_db->query($query);
				if ($row = $results->fetch_array(MYSQLI_BOTH))
				{
					echo $row['quantity'] . " " . $row['unit'] . " of " .
						$row['name'] . " (" . $row['description'] . ")<br >\n";
					echo "	<input type=\"hidden\" name=\"data[" . $j . "][id]\" value=\"" . 
						$mysql_db->real_escape_string($_POST['data'][$i]['id']) . "\">\n";
					$j = $j + 1;
				}
			}
		}


		$query = "SELECT * FROM locations WHERE owner = " . $_SESSION['user']['emp_id'] . " AND id = position;";
		$results = $mysql_db->query($query);
		if ($row = $results->fetch_array(MYSQLI_BOTH))
		{
			echo "	<select name=\"move_to\">\n";
			echo "		<option value=\"nothing\">Select a location</option>\n";
			list_location("MASTER", $row['id'], $database);
			echo "	</select>\n";
			echo "	<input type=\"hidden\" name=\"action\" value=\"move_equ\"><br>\n";
			echo "  <input type=\"hidden\" name=\"amount\" value=" . $j . "><br >\n";
			echo "	<input type='submit' value='GO' >\n";
			echo "</form>\n";
		}
	}

	if (($_POST["action"] == "del_equ") && ($_POST["delete"] != ""))
	{
		$amount = $_POST['amount'];	//possible number of items to delete
		if (is_numeric($location) == FALSE)
			$location = 0;
		
		$j = 0;	//actual number of items to delete
		
		echo "<h1> Are you sure you want to DELETE this equipment?</h1>\n";
		echo "<form method='post'>\n";
		
		for ($i = 0; $i < $amount; $i++)
		{
			if ($mysql_db->real_escape_string($_POST['data'][$i]['delete']) == "on")
			{
				$query = "SELECT * FROM equipment WHERE owner = " . $_SESSION['user']['emp_id'] . " AND id = " .
					$mysql_db->real_escape_string($_POST['data'][$i]['id']) . ";";
				$results = $mysql_db->query($query);
				if ($row = $results->fetch_array(MYSQLI_BOTH))
				{
					echo $row['quantity'] . " " . $row['unit'] . " of " .
						$row['name'] . " (" . $row['description'] . ")<br >\n";
					echo "	<input type=\"hidden\" name=\"data[" . $j . "][id]\" value=\"" . 
						$mysql_db->real_escape_string($_POST['data'][$i]['id']) . "\">\n";
					$j = $j + 1;
				}
			}
		}
		
		echo "  <input type=\"hidden\" name=\"position\" value=" . $location . "><br >\n";
		echo "	<input type=\"hidden\" name=\"action\" value=\"confirm_del_equ\"><br>\n";
		echo "  <input type=\"hidden\" name=\"amount\" value=" . $j . "><br >\n";
		if ($j > 0)
		{
			echo "	<input type='submit' value='YES' >\n";
		}
		echo "</form>\n";
		if ($j == 0)
		{
			echo "No equipment was selected to be deleted<br >\n";
		}
		
		echo "<form method='post'>\n";
		echo "	<input type='submit' value='NO' >\n";
		echo "</form>\n";
	}

	if ($_POST["action"] == "add_equ")
	{
		echo "<form method='post'>\n";
		echo "<table border=\"1\">\n";
		echo "	<tr>\n";
		echo "		<th>Quantity</th>\n";
		echo "		<th>Units</th>\n";
		echo "		<th>Name</th>\n";
		echo "		<th>Description</th>\n";
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
			
			echo "	</tr>\n";
		}

		echo "</table><br>\n";
		echo "  <input type=\"hidden\" name=\"position\" value=" . $location . "><br >\n";
		echo "	<input type=\"hidden\" name=\"action\" value=\"do_equ\"><br>\n";
		echo "	<input type='submit' value='Add this equipment' >\n";
		echo "</form>\n";
		
		echo '<a href="' . rootPageURL() . '/locations.php?id=' . $location . '">Nevermind, don\'t add equipment</a>' . "<br >\n";
	}


	if ($_POST["action"] == "")
	{
		$query = "SELECT * FROM locations WHERE owner = " . $_SESSION['user']['emp_id'] . " AND id = " . $location . ";";
		$results = $mysql_db->query($query);
		if ($row = $results->fetch_array(MYSQLI_BOTH))
		{
			$loc_name = $row['description'];
			$query2 = "SELECT * FROM locations WHERE owner = " . $_SESSION['user']['emp_id'] . " AND id = " . $row['position'] . ";";
			$results2 = $mysql_db->query($query2);
			if ($row2 = $results2->fetch_array(MYSQLI_BOTH))
			{
				$loc_name = $row['description'];
				if ($root_location == 0)
					echo "<h2>Information for location " . $row['description'] . ' (' . $row['location'] . ")</h2><br >\n";
				else
					echo "<h2>Information for top-level locations</h2><br >\n";
				if ($root_location == 0)
				{
					echo '<a href="' . rootPageURL() . '/locations.php?id=' . $row2['id'] . '">Return to ' . 
						$row2['description'] . '</a>' . "<br >\n";
				}
			}
			else
			{
				die('An internal error occurred');
			}
		}
		else
		{
			die('Invalid location (' . $location . ') specified');
		}
		
		if ($root_location == 0)
		{
			echo "<form action=\"" . curPageURL() . "\" method=\"post\">\n" .
				"	<input type=\"hidden\" name=\"action\" value=\"del_loc\"><br>\n" .
				"	<input type=\"submit\" value=\"Delete this location (" .
					$loc_name . ")\">\n" .
				"</form>";
		}
		
		$query = "SELECT * FROM locations WHERE owner = " . $_SESSION['user']['emp_id'] . " AND position = " . $location . 
			" ORDER BY description;";
		$results = $mysql_db->query($query);
		
		$locations_exist = 0;
		
		while($row = $results->fetch_array(MYSQLI_BOTH))
		{
			if ($row['id'] != $root_number)
			{
				if ($locations_exist == 0)
				{
					echo "<h3>Locations : </h3><br >\n";
					$locations_exist = 1;
				}
				echo '<a href="' . rootPageURL() . '/locations.php?id=' . $row['id'] . '">' . $row['description'] . 
					' (' . $row['location'] . ')</a>' . "<br >\n";
			}
		}
		
		if ($locations_exist == 0)
		{
			echo "<h3>There are no locations here</h3><br >\n";
		}
		
		echo "<form action=\"" . curPageURL() . "\" method=\"post\">\n" .
			"	<input type=\"hidden\" name=\"action\" value=\"add_loc\"><br>\n";
			
		if ($root_location == 0)
		{
			echo "	<input type=\"submit\" value=\"Add locations to " . $loc_name . "\">\n";
		}
		else
		{
			echo "	<input type=\"submit\" value=\"Add locations\">\n";
		}
		echo "</form>";
		
		$query = "SELECT * FROM equipment WHERE owner = " . $_SESSION['user']['emp_id'] . " AND location = " . $location . 
			" ORDER BY name;";
		$results = $mysql_db->query($query);
		$equipment_exist = 0;
		
		while($row = $results->fetch_array(MYSQLI_BOTH))
		{
			if ($equipment_exist == 0)
			{
				echo "<h3>Equipment : </h3><br >\n";
				$equipment_exist = 1;
				$i = 0;
				echo "<form method='post'>\n";
				echo "<table border=\"1\">\n";
				echo "	<tr>\n";
				echo "		<th></th>\n";
				echo "		<th>Quantity</th>\n";
				echo "		<th>Units</th>\n";
				echo "		<th>Name</th>\n";
				echo "		<th>Description</th>\n";
				echo "	</tr>\n";
			}
			//echo '<a href="' . rootPageURL() . '/equipment.php?id=' . $row['id'] . '">' . $row['description']. '</a>' . "<br >\n";
			//echo $row['quantity'] . " " . $row['unit'] . " " . $row['name'] . ", (" . $row['description'] . ")<br >\n";

			echo "	<tr>\n";
			
			echo "		<td>";
			echo "			<input name=\"data[" . $i . "][delete]\" type=\"checkbox\">\n";
			echo "			<input type=\"hidden\" name=\"data[" . $i . "][id]\" value=\"" . $row['id'] . "\">\n";
			echo "		</td>\n";
			$i = $i + 1;

			echo "		<td>";
			echo $row['quantity'];
			echo "		</td>\n";
				
			echo "		<td>";
			echo $row['unit'];
			echo "		</td>\n";
				
			echo "		<td>";
			echo $row['name'];
			echo "		</td>\n";
				
			echo "		<td>";
			echo $row['description'];
			echo "		</td>\n";
				
			echo "	</tr>\n";
		}
		
		if ($equipment_exist == 0)
		{
			echo "<h3>There is no equipment here</h3><br >\n";
		}
		else
		{
			echo "</table><br>\n";
			echo "  <input type=\"hidden\" name=\"position\" value=" . $location . "><br >\n";
			echo "	<input type=\"hidden\" name=\"action\" value=\"del_equ\"><br>\n";
			echo "  <input type=\"hidden\" name=\"amount\" value=" . $i . "><br >\n";
			echo "	<input type='submit' value='Delete selected equipment' name=\"delete\">\n";
			echo "	<input type='submit' value='Move selected equipment' name=\"move\">\n";
			echo "</form>\n";
		}
		
		echo "<form action=\"" . curPageURL() . "\" method=\"post\">\n" .
			"	<input type=\"hidden\" name=\"action\" value=\"add_equ\"><br>\n";
		if ($root_location == 0)
		{
			echo "	<input type=\"submit\" value=\"Add equipment to " . $loc_name . "\">\n";
		}
		else
		{
			echo "	<input type=\"submit\" value=\"Add equipment\">\n";
		}
		
		echo "</form>";
	}
}
	
closeDatabase();

?>

</body>
</html>
