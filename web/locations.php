<?php
require_once("global.php");
require_once("include/exceptions.php");

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

include_once("include/upload_file.php");
include_once("include/location.php");

try
{
	$config = parse_ini_file("config.ini");
	test_config($config);

	global $mysql_db;
	openDatabase($config);

	?>
	<title>Locations: <?php sitename()?></title>
	<?php do_css() ?>
	</head>

	<body>

	<?php


	if (isset($_GET["id"]))
	{
		$location = $_GET["id"];
	}
	else
	{
		$location = 0;
	}
	if (is_numeric($location) == FALSE)
	{
		$location = 0;
	}

	$stop = 0;
	if (login_code(0) == 1)
	{
		$stop = 1;
	}

	if ($stop == 0)
	{
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
	}
	

	if ($stop == 0)
	{
		do_top_menu(4);

		if ($_POST["action"] == "del_loc")
		{
			if ($root_location == 1)
			{
				throw new Exception('You cannot delete this location');
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
				throw new Exception('This location cannot be deleted because it contains locations/equipment');
			}
		
			$query = "DELETE FROM locations WHERE owner = " . $_SESSION['user']['emp_id'] . " AND id = " . $location . ";";
			if (!$mysql_db->query($query))
			{
				throw new Exception("Error: " . $mysql_db->error . "<br >\n");
			}
			else
			{
				echo "Location deleted successfully.<br >\n";
			}

			$location = $return_to;
			$_POST["action"] = "";
		}

		if ($_POST["action"] == "do_loc")
		{	//apply location changes
			do_loc(1);
		}

		if ($_POST["action"] == "add_loc")
		{	//create menu to ask how to change locations
			do_loc(0);
		}

		if ($_POST["action"] == "do_equ")
		{
			do_equ(1);
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
					throw new Exception("Error: " . $mysql_db->error . "<br >\n");
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
			{
				$location = 0;
			}
		
			$move_to = $_POST['move_to'];
			if (is_numeric($move_to) == FALSE)
			{
				throw new Exception("Invalid location specified");
			}
		
			
			$an_error = 0;
			
			for ($i = 0; $i < $amount; $i++)
			{
				$query = "UPDATE equipment SET location = " . $move_to . " WHERE OWNER = " . $_SESSION['user']['emp_id'] . 
					" AND id = " . $mysql_db->real_escape_string($_POST['data'][$i]['id']) . ";";
				if (!$mysql_db->query($query))
				{
					throw new Exception("Error: " . $mysql_db->error . "<br >\n");
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
				echo "	<input class=\"buttons\" type='submit' value='GO' >\n";
				echo "</form>\n";
			}
		}

		if (($_POST["action"] == "del_equ") && ($_POST["delete"] != ""))
		{
			$amount = $_POST['amount'];	//possible number of items to delete
			if (is_numeric($location) == FALSE)
			{
				$location = 0;
			}
		
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
				echo "	<input class=\"buttons\" type='submit' value='YES' >\n";
			}
			echo "</form>\n";
			if ($j == 0)
			{
				echo "No equipment was selected to be deleted<br >\n";
			}
		
			echo "<form method='post'>\n";
			echo "	<input class=\"buttons\" type='submit' value='NO' >\n";
			echo "</form>\n";
		}

		if ($_POST["action"] == "add_equ")
		{	
			do_equ(0);
		}


		if ($_POST["action"] == "")
		{
			$query = "SELECT * FROM locations WHERE owner = " . $_SESSION['user']['emp_id'] . " AND id = " . $location . ";";
			$results = $mysql_db->query($query);
			if ($row = $results->fetch_array(MYSQLI_BOTH))
			{
				$loc_name = $row['description'];
				$query2 = "SELECT * FROM locations WHERE owner = " . $_SESSION['user']['emp_id'] . 
					" AND id = " . $row['position'] . ";";
				$results2 = $mysql_db->query($query2);
				if ($row2 = $results2->fetch_array(MYSQLI_BOTH))
				{
					$loc_name = $row['description'];
					if ($root_location == 0)
					{
						echo "<h2>Information for location " . $row['description'] . 
							' (' . $row['location'] . ")</h2><br >\n";
						if ($row['img_id'])
						{
							echo '<img src="' . rootPageURL() . '/uploads/image.php?id=' . 
								$row['img_id'] . ".jpg\" alt=\"No image\"> <br >\n";
						}
						else
						{
							echo no_image() . " <br >\n";
						}
						//TODO: add capability of changing, removing, adding a photo for the location
					}
					else
					{
						//TODO: can a root location have a photo? if so the previous TODOS go after this if/else statement
						echo "<h2>Information for top-level locations</h2><br >\n";
					}
					if ($root_location == 0)
					{
						echo '<a href="' . rootPageURL() . '/locations.php?id=' . $row2['id'] . '">Return to ' . 
							$row2['description'] . '</a>' . "<br >\n";
					}
				}
				else
				{
					throw new Exception('An internal error occurred');
				}
			}
			else
			{
				throw new Exception('Invalid location (' . $location . ') specified');
			}
		
			if ($root_location == 0)
			{
				echo "<form action=\"" . curPageURL() . "\" method=\"post\">\n" .
					"	<input type=\"hidden\" name=\"action\" value=\"del_loc\"><br>\n" .
					"	<input class=\"buttons\" type=\"submit\" value=\"Delete this location (" .
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
						echo "<div class=\"loc_grid\">\n";
						echo "	<h3>Locations : </h3><br >\n";
						$locations_exist = 1;
					}

					echo "	<div class=\"loc_grid_elem\">\n";
					echo '		<a href="' . rootPageURL() . '/locations.php?id=' . $row['id'] . "\"><br>\n";
	
					if ($row['img_id'])
					{
						echo '		<img src="' . rootPageURL() .
							'/uploads/image.php?id=' . $row['img_id'] . ".jpg&amp;thumb=1\" alt=\"No image\">";
					}
					else
					{
						echo no_image();
					}
					echo $row['description'] . ' (' . $row['location'] . ')</a>' . "<br >\n";
					echo "	</div>\n";
				}
			}
		
			if ($locations_exist == 0)
			{
				echo "<h3>There are no locations here</h3><br >\n";
			}
			else
			{
				echo "</div>\n";
				echo "<div class=\"clear\"></div>\n";
			}
		
		    echo "<br>\n<br>\n<br>\n";
			echo "<form action=\"" . curPageURL() . "\" method=\"post\">\n" .
				"	<input type=\"hidden\" name=\"action\" value=\"add_loc\"><br>\n";
			//TODO : Seperate adding locations (no photo upload) and adding a location (photo upload)		
			if ($root_location == 0)
			{
				echo "	<input class=\"buttons\" type=\"submit\" value=\"Add locations to " . $loc_name . "\">\n";
			}
			else
			{
				echo "	<input class=\"buttons\" type=\"submit\" value=\"Add locations\">\n";
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
					echo "		<th>Photo</th>\n";
					echo "		<th>Quantity</th>\n";
					echo "		<th>Units</th>\n";
					echo "		<th>Name</th>\n";
					echo "		<th>Description</th>\n";
					echo "	</tr>\n";
				}
				//echo '<a href="' . rootPageURL() . '/equipment.php?id=' . $row['id'] . '">' . $row['description']. '</a>' . "<br >\n";
				//echo $row['quantity'] . " " . $row['unit'] . " " . $row['name'] . ", (" . $row['description'] . ")<br >\n";

				echo "	<tr>\n";

				echo "		<td>\n";
				echo "			<input name=\"data[" . $i . "][delete]\" type=\"checkbox\">\n";
				echo "			<input type=\"hidden\" name=\"data[" . $i . "][id]\" value=\"" . $row['id'] . "\">\n";
				echo "		</td>\n";
				$i = $i + 1;

				echo "		<td><a href=maintenance.php?id=" . $row['id'] . ">\n";
				if ($row['img_id'])
				{
					echo '<img src="' . rootPageURL() .
							'/uploads/image.php?id=' . $row['img_id'] . ".jpg&amp;thumb=1\" alt=\"No image\">\n";
					echo "		";
				}
				else
				{
					echo no_image() . "\n";
					echo "		";
				}
		        echo "</a></td>\n";



				echo "		<td>" . $row['quantity'] . "</td>\n";
				echo "		<td>" . $row['unit'] . "</td>\n";
				echo "		<td>" . $row['name'] . "</td>\n";
				echo "		<td>" . $row['description'] . "</td>\n";
				
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
		
			//TODO : seperate adding multiple pieces of equipment (no photo uploading capabilities and single equipment add (photo upload)
			if ($root_location == 0)
			{
				echo "	<input class=\"buttons\" type=\"submit\" value=\"Add equipment to " . $loc_name . "\">\n";
			}
			else
			{
				echo "	<input class=\"buttons\" type=\"submit\" value=\"Add equipment\">\n";
			}
		
			echo "</form>";
		}
	}
	
	closeDatabase();
}
catch (ConfigurationMissingException $e)
{
	?>
	<title>Site Configuration Error</title>
	<?php do_css() ?>
	</head>
	<body>
	<h1>Site configuration error</h1>
	<?php
}
catch (DatabaseConnectionFailedException $e)
{
	?>
	<title>Site Configuration Error</title>
	<?php do_css() ?>
	</head>
	<body>
	<h1>Site configuration error</h1>
	<?php
}
catch (PermissionDeniedException $e)
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
