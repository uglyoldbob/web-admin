<?php
include("global.php");
start_my_session();
header('Content-type: text/html; charset=utf-8');

require("forms.php");

$location = $_GET["id"];
$database = openDatabase();

if (is_numeric($location) == FALSE)
	$location = 0;

$query = "SELECT * FROM locations WHERE owner = " . $_SESSION['id'] . " AND id = position;";
$results = mysql_query($query, $database);
if ($row = mysql_fetch_array($results))
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

<!DOCTYPE HTML SYSTEM>
<html>
<head>
<title>Equipment Management System: Locations</title>
</head>

<script type="text/javascript" src="jquery-1.2.1.pack.js"></script>
<script type="text/javascript">
	function lookupPayer(textId) 
	{	//operates the autocomplete for a textbox
		if(textId.length == 0) 
		{
			// Hide the suggestion box.
			$('#payer_suggestions').hide();
		}
		else 
		{
			$.post("payerId.php", 
				{queryString: ""+textId+""}, 
				function(data)
			{
				if(data.length >0) 
				{
				$('#payer_suggestions').show();
				$('#payer_autoSuggestionsList').html(data);
				}
			});
		}
	} // lookup
	
	function lookupPayee(textId) 
	{	//operates the autocomplete for a textbox
		if(textId.length == 0) 
		{
			// Hide the suggestion box.
			$('#payee_suggestions').hide();
		}
		else 
		{
			$.post("payeeId.php",
				{queryString: ""+textId+""},
				function(data)
			{
				if(data.length >0) 
				{
				$('#payee_suggestions').show();
				$('#payee_autoSuggestionsList').html(data);
				}
			});
		}
	} // lookup
	
	function updateNamePayer(nameId)
	{	//fills out the contact name when the contact id is changed
		if(nameId.length == 0) 
		{
			// Hide the suggestion box.
			$('#payer_suggestions').hide();
		}
		else 
		{
			$.post("getnamePayer.php", {queryString: ""+nameId+""}, function(data)
			{
				if(data.length >0) 
				{
					$('#payer_suggestions').show();
					$('#payer_autoSuggestionsList').html(data);
				}
			});
		}
	}
	
	function updateNamePayee(nameId)
	{	//fills out the contact name when the contact id is changed
		if(nameId.length == 0) 
		{
			// Hide the suggestion box.
			$('#payee_suggestions').hide();
		}
		else 
		{
			$.post("getnamePayee.php", 
				{queryString: ""+nameId+""}, 
				function(data)
			{
				if(data.length >0) 
				{
				$('#payee_suggestions').show();
				$('#payee_autoSuggestionsList').html(data);
				}
			});
		}
	}
	
	function fillPayer(thisValue, thatValue) 
	{	//fills in the value when an autocomplete value is selected
		$('#name_payer').val(thisValue);
		$('#id_payer').val(thatValue);
		setTimeout("$('#payer_suggestions').hide();", 200);
	}
	
	function fillPayee(thisValue, thatValue) 
	{	//fills in the value when an autocomplete value is selected
		$('#name_payee').val(thisValue);
		$('#id_payee').val(thatValue);
		setTimeout("$('#payee_suggestions').hide();", 200);
	}
	
</script>

<body>

<?php

login_code();
login_button($database);

echo '<a href="' . bottomPageURL() . '">Return to main</a>' . "<br >\n";

if ($_POST["action"] == "apply")
{
}

if ($_POST["action"] == "del_loc")
{
	if ($root_location == 1)
	{
		die('You cannot delete this location');
	}
	
	$scan_loc = 0;
	
	$query = "SELECT * FROM locations WHERE owner = " . $_SESSION['id'] . " AND id = " . $location . ";";
	$results = mysql_query($query, $database);
	if ($row = mysql_fetch_array($results))
	{
		$return_to = $row['position'];
	}
	
	$query = "SELECT * FROM locations WHERE owner = " . $_SESSION['id'] . " AND position = " . $location . ";";
	$results = mysql_query($query, $database);
	if ($row = mysql_fetch_array($results))
	{
		$scan_loc = 1;
	}
	
	$query = "SELECT * FROM equipment WHERE owner = " . $_SESSION['id'] . " AND location = " . $location . ";";
	$results = mysql_query($query, $database);
	if ($row = mysql_fetch_array($results))
	{
		$scan_loc = 1;
	}
	
	if ($scan_loc == 1)
	{
		die('This location cannot be deleted because it contains locations/equipment');
	}
	
	$query = "DELETE FROM locations WHERE owner = " . $_SESSION['id'] . " AND id = " . $location . ";";
	if (!mysql_query($query, $database))
	{
		echo "Error: " . mysql_error() . "<br >\n";
		echo $query . "<br >\n";
		//die('Error: ' . mysql_error());
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
			$query = $query . "(\"" . $_SESSION['id'] . "\", \"" . 
				mysql_real_escape_string($_POST['position']) . "\", \"" .
				mysql_real_escape_string($_POST['data'][$i]['description']) . "\", \"" .
				mysql_real_escape_string($_POST['data'][$i]['location']) . "\")";
			$first_record = 1;
			
			echo mysql_real_escape_string($_POST['data'][$i]['description']) . ", " . 
				mysql_real_escape_string($_POST['data'][$i]['location']) . "<br >\n";
		}
	}
	if ($first_record != 0)
	{
		$query = $query . ";";
	
		if (!mysql_query($query, $database))
		{
			echo "Error: " . mysql_error() . "<br >\n";
			echo $query . "<br >\n";
			//die('Error: ' . mysql_error());
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
	
	echo '<a href="' . bottomPageURL() . 'locations.php?id=' . $location . '">Nevermind, don\'t add locations</a>' . "<br >\n";
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
			$query = $query . "(\"" . $_SESSION['id'] . "\", \"" . 
				mysql_real_escape_string($_POST['position']) . "\", \"" .
				mysql_real_escape_string($_POST['data'][$i]['quantity']) . "\", \"" .
				mysql_real_escape_string($_POST['data'][$i]['unit']) . "\", \"" .
				mysql_real_escape_string($_POST['data'][$i]['name']) . "\", \"" .
				mysql_real_escape_string($_POST['data'][$i]['description']) . "\")";
			$first_record = 1;
			
			echo mysql_real_escape_string($_POST['data'][$i]['quantity']) . " " . 
				mysql_real_escape_string($_POST['data'][$i]['unit']) . " of " . 
				mysql_real_escape_string($_POST['data'][$i]['name']) . "<br >\n";
		}
	}
	if ($first_record != 0)
	{
		$query = $query . ";";
	
		if (!mysql_query($query, $database))
		{
			echo "Error: " . mysql_error() . "<br >\n";
			echo $query . "<br >\n";
			//die('Error: ' . mysql_error());
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
	
	echo '<a href="' . bottomPageURL() . 'locations.php?id=' . $location . '">Nevermind, don\'t add equipment</a>' . "<br >\n";
}


if ($_POST["action"] == "")
{
	$query = "SELECT * FROM locations WHERE owner = " . $_SESSION['id'] . " AND id = " . $location . ";";
	$results = mysql_query($query, $database);
	if ($row = mysql_fetch_array($results))
	{
		$loc_name = $row['description'];
		$query2 = "SELECT * FROM locations WHERE owner = " . $_SESSION['id'] . " AND id = " . $row['position'] . ";";
		$results2 = mysql_query($query2, $database);
		if ($row2 = mysql_fetch_array($results2))
		{
			$loc_name = $row['description'];
			if ($root_location == 0)
				echo "<h2>Information for location " . $row['description'] . ' (' . $row['location'] . ")</h2><br >\n";
			else
				echo "<h2>Information for top-level locations</h2><br >\n";
			if ($root_location == 0)
			{
				echo '<a href="' . bottomPageURL() . 'locations.php?id=' . $row2['id'] . '">Return to ' . 
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
	
	$query = "SELECT * FROM locations WHERE owner = " . $_SESSION['id'] . " AND position = " . $location . 
		" ORDER BY description;";
	$results = mysql_query($query, $database);
	
	$locations_exist = 0;
	
	while($row = mysql_fetch_array($results))
	{
		if ($row['id'] != $root_number)
		{
			if ($locations_exist == 0)
			{
				echo "<h3>Locations : </h3><br >\n";
				$locations_exist = 1;
			}
			echo '<a href="' . bottomPageURL() . 'locations.php?id=' . $row['id'] . '">' . $row['description'] . 
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
	
	$query = "SELECT * FROM equipment WHERE owner = " . $_SESSION['id'] . " AND location = " . $location . 
		" ORDER BY name;";
	$results = mysql_query($query, $database);
	$equipment_exist = 0;
	
	while($row = mysql_fetch_array($results))
	{
		if ($equipment_exist == 0)
		{
			echo "<h3>Equipment : </h3><br >\n";
			$equipment_exist = 1;
			echo "<table border=\"1\">\n";
			echo "	<tr>\n";
			echo "		<th>Quantity</th>\n";
			echo "		<th>Units</th>\n";
			echo "		<th>Name</th>\n";
			echo "		<th>Description</th>\n";
			echo "	</tr>\n";
		}
		//echo '<a href="' . bottomPageURL() . 'equipment.php?id=' . $row['id'] . '">' . $row['description']. '</a>' . "<br >\n";
		//echo $row['quantity'] . " " . $row['unit'] . " " . $row['name'] . ", (" . $row['description'] . ")<br >\n";

		echo "	<tr>\n";

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

closeDatabase($database);

?>

</body>
</html>
