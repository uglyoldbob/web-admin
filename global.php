<?php

if ('global.php' == basename($_SERVER['SCRIPT_FILENAME']))
	die ('<h2>Direct File Access Prohibited</h2>');

include("passwords.php");
include("include/contacts.php");

$config = parse_ini_file("/etc/web-admin/config.ini");

function curPageURL()
{
	$pageURL = 'http';
	if (array_key_exists("HTTPS", $_SERVER))
	{
		if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	}
	$pageURL .= "://";
	if (($_SERVER["SERVER_PORT"] != "80") && ($_SERVER["SERVER_PORT"] != "443"))
	{
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	}
	else
	{
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	
	return $pageURL;
}

function rootPageURL()
{
	global $config;
	$pageURL = 'http';
	if (array_key_exists("HTTPS", $_SERVER))
	{
		if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	}
	$pageURL .= "://";
	if (($_SERVER["SERVER_PORT"] != "80") && ($_SERVER["SERVER_PORT"] != "443"))
	{
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"]. $config["location"];
	}
	else
	{
		$pageURL .= $_SERVER["SERVER_NAME"].$config["location"];
	}
	
	return $pageURL;
}
	
function start_my_session()
{
	session_start();
	if (!isset($_SESSION['initiated']))
	{
		session_regenerate_id();
		$_SESSION['initiated'] = true;
	}

	if (isset($_SESSION['HTTP_USER_AGENT']))
	{
		if ($_SESSION['HTTP_USER_AGENT'] != md5($_SERVER['HTTP_USER_AGENT']))
		{	/* Prompt for password */
			unset($_SESSION['username']);
			unset($_SESSION['password']);
			exit;
		}
	}
	else
	{
		$_SESSION['HTTP_USER_AGENT'] = md5($_SERVER['HTTP_USER_AGENT']);
	}

	if (!(array_key_exists("action", $_POST)))
	{
		$_POST["action"] = "";
	}
}

//open database connection
function openDatabase()
{
	global $mysql_db, $config;
	$mysql_db = new mysqli($config["database_server"], 
		$config["database_username"], $config["database_password"], 
		$config["database_name"], $config["database_port"]);
	if ($mysql_db->connect_errno)
	{
		echo "Failed to connect to MySQL: (" . $mysq_db->connect_errno . ") " .
			$mysq_db->connect_error . "<br >\n";
		die("Database connection failed");
	}
	//TODO: implement calling this function
	//mysqli_set_charset()
}

function check_permission($table, $idfrom, $idto, $mask)
{	//returns "master", "public", "global", "normal", "none"
	global $mysql_db;
	$output = array();
	$query = "SELECT * FROM `" . $table . "` WHERE " .
		"((id1 IS NULL) OR (id1 = " . $idto . ")) AND " .
		"((id2 IS NULL) OR (id2 = " . $idfrom . ")) " .
		"AND (permission LIKE '" . $mask . "');";
	$result = $mysql_db->query($query);
	if ($row = $result->fetch_array(MYSQLI_BOTH))
	{
		do
		{
			if (!is_null($row['id1']) && !is_null($row['id2']))
			{
				array_push($output, array($row['id'], "normal"));
			}
			else if (is_null($row['id1']) && !is_null($row['id2']))
			{
				array_push($output, array($row['id'], "global"));
			}
			else if (!is_null($row['id1']) && is_null($row['id2']))
			{
				array_push($output, array($row['id'], "public"));
			}
			else if (is_null($row['id1']) && is_null($row['id2']))
			{
				array_push($output, array($row['id'], "master"));
			}
		} while ($row = $result->fetch_array(MYSQLI_BOTH));
	}
	else
	{
		array_push($output, array($row['id'], "none"));
	}
	$result->close();
	
	return $output;
}

function check_specific_permission($results, $permission)
{	//check for the presence of a certain type of permission
	//use on the results of check_permission
	foreach ($results as $permcheck)
	{
		if ($permcheck[1] == $permission)
			return "yes";
	}
	return "no";
}

function mod_permission($table, $idfrom, $idto, $op, $perm)
{	//used to add or remove a single attribute from a permission table
	global $mysql_db;
	
	//should detect null values
	if ((is_numeric($idto) == FALSE) || (is_numeric($idfrom) == FALSE))
	{
		echo "<b>You can't do that</b><br >\n";
		return;
	}
	
	$permcheckarray = check_permission($table, $idfrom, $idto, '%' . $perm . '%');

	//because there could be multiple elements 	
	foreach ($permcheckarray as $permcheck)
	{
		if ($permcheck[1] == "normal")
		{	//regular permission exists
			if ($op == "-")
			{	//remove the permission that exists
				$query = "UPDATE `" . $table . "` SET permission = " .
					"REPLACE(permission, '" . $perm . "', '') WHERE (id = " .
					$permcheck[0] . ") AND (id1 = " . $idto . ");";
				$mysql_db->query($query);
				//TODO: remove rows that do not add permissions
			}
		}
		else if ($permcheck[1] == "none")
		{	//no permission exists
			if ($op == "+")
			{	//try to add to an existing normal permission
				$query = "UPDATE `" . $table . "` SET permission = " .
					"CONCAT(permission, '" . $perm . "') WHERE (id = " .
					$idfrom . ") AND (id1 = " . $idto . ");";
				if($result = $mysql_db->query($query))
				{
					if ($mysql_db->affected_rows == 0)
					{	//add a new normal permission entry
						$query = "INSERT INTO `" . $table . "` (id1, id2, permission)" .
							" VALUES ('" . $idto . "', '" . $idfrom . "', '" . $perm .
							"');";
						$mysql_db->query($query);
					}
				}
			}
		}
	}

	return $output;
}

function login_code($quiet)
{	//prints and executes code for the login script
	//return value of 1 means don't do anything else
		//the login script has closed the fence for some reason
	global $mysql_db, $config;
	$retv = 0;
	if (!(array_key_exists("HTTPS", $_SERVER)))
	{
		$_SERVER["HTTPS"] = "off";
	}
	
	if (($_SERVER["HTTPS"] != "on") && ($config['require_https'] == 1))
	{
		echo "HTTPS is required<br >\n";
		$retv = 1;
	}
	if ($_POST["action"] == "login")
	{	//retrieve submitted username and password, if applicable
		$username = $mysql_db->real_escape_string($_POST["user"]);
		$passworder = $mysql_db->real_escape_string($_POST["password"]);
	
		$_SESSION['username'] = $username;
		$_SESSION['password'] = $passworder;
			//password is briefly stored in plain text when the user logs in
			//it is unset or replaced with the hash in the login_button function
	}
	else if ($_POST["action"] == "logout")
	{
		unset($_SESSION['username']);
		unset($_SESSION['password']);
	}
	else if ($_POST["action"] == "change_pass")
	{
		$retv = 1;
		if ($quiet == 0)
		{
			echo 	"<form action=\"" . curPageURL() . "\" method=\"post\">\n" .
					"	<input type=\"hidden\" name=\"action\" value=\"apply_pass\"><br>\n" .
					"	Old password: <input type=\"password\" name=\"pass1\" ><br>\n" .
					"	New password: <input type=\"password\" name=\"pass2\" ><br>\n" .
					"	New password again: <input type=\"password\" name=\"pass3\" ><br>\n" .
					"	<input type=\"submit\" value=\"Change my password\">\n" .
					"</form>\n";
		}
	}
	else if ($_POST["action"] == "apply_pass")
	{
		$oldpass = $mysql_db->real_escape_string($_POST['pass1']);
		$newpass = $mysql_db->real_escape_string($_POST['pass2']);
		$passmatch = $mysql_db->real_escape_string($_POST['pass3']);
		if ($newpass == $passmatch)
		{
			$uid = $_SESSION['user']['emp_id'];
			contacts::store_user_pword($uid, $oldpass, $newpass);
		}
		else
		{
			echo "<h3>Passwords do not match</h3><br >\n";
		}
	}

	if (isset($_SESSION['username']))
	{
		$query = "SELECT * FROM contacts WHERE username='" . $_SESSION['username'] . "' LIMIT 1;";
		$results = $mysql_db->query($query);
		if ($results)
		{
			$row = $results->fetch_array(MYSQLI_BOTH);
			if ($row['fail_logins'] >= $config['max_fail_logins'])
			{	//TODO: set time period for waiting to login
				unset($_SESSION['username']);
				unset($_SESSION['password']);
			}
			
			if ($_POST["action"] == "login")
			{
				//check to see if the password matches and the stretching does not match
				$temp = hash_password($_SESSION['password'], $row['salt'], $row['stretching']);
				if ( ($row['password'] == $temp) && ($row['stretching'] != $config['key_stretching_value']) )
				{	//password is good, key stretching needs to be fixed
					contacts::mod_user_pword($row['emp_id'], $_SESSION['password']);
					$fquery = "SELECT * From contacts WHERE username='" . $_SESSION['username'] . "'LIMIT 1;";
					$fresults = $mysql_db->query($fquery);
					if ($fresults)
					{
						$row = $fresults->fetch_array(MYSQLI_BOTH);
					}
					else
					{	//this should never happen
						die("Failed to reformat password");
					}
					$temp = hash_password($_SESSION['password'], $row['salt'], $config['key_stretching_value']);
					$row['password'] = $temp;
				}

				$_SESSION['password'] = $temp;
			}
			if ($row['password'] == $_SESSION['password'])
			{
				$_SESSION['user'] = $row;
				if ($_POST["action"] == "login")
				{
					$query = "UPDATE contacts SET fail_pass_change=0 WHERE emp_id = " . $_SESSION['user']['emp_id'] . ";";
					$mysql_db->query($query);
					$query = "UPDATE contacts SET fail_logins=0 WHERE emp_id = " . $_SESSION['user']['emp_id'] . ";";
					$mysql_db->query($query);
				}
				if ($quiet == 0)
				{
					echo "<h3>Welcome ";
					echo print_contact($_SESSION['user']['emp_id']);
					echo "</h3><br >\n";
					echo "<form action=\"" . curPageURL() . "\" method=\"post\">\n" .
						 "	<input type=\"hidden\" name=\"action\" value=\"logout\">\n" .
						 "	<input type=\"submit\" value=\"Logout\">\n" .
						 "</form>\n";
					if ($_POST["action"] != "change_pass")
					{
						echo	"<form action=\"" . curPageURL() . "\" method=\"post\">\n" .
								"	<input type=\"hidden\" name=\"action\" value=\"change_pass\">\n" .
								"	<input type=\"submit\" value=\"Change my password\">\n" .
								"</form><br >\n";
					}
				}
			}
			else
			{	//password fail match
				$query = "UPDATE contacts SET fail_logins=fail_logins+1 WHERE emp_id = " . $_SESSION['user']['emp_id'] . ";";
				$mysql_db->query($query);
				unset($_SESSION['username']);
				unset($_SESSION['password']);
				echo	"<h3>Invalid username or password</h3><br >\n" .
						"<b>Please login<br >\n" .
						"<form action=\"" . curPageURL() . "\" method=\"post\">\n" .
						"	<input type=\"hidden\" name=\"action\" value=\"login\"><br>\n" .
						"	Username: <input type=\"text\" name=\"user\" ><br>\n" .
						"	Password: <input type=\"password\" name=\"password\" ><br>\n" .
						"	<input type=\"submit\" value=\"Login\">\n" .
						"</form>\n";
				$retv = 1;
			}
			$results->close();
		}
		else
		{	//contact not found
			unset($_SESSION['username']);
			unset($_SESSION['password']);
			echo	"<h3>Invalid username or password</h3><br >\n" .
					"<b>Please login<br >\n" .
					"<form action=\"" . curPageURL() . "\" method=\"post\">\n" .
					"	<input type=\"hidden\" name=\"action\" value=\"login\"><br>\n" .
					"	Username: <input type=\"text\" name=\"user\" ><br>\n" .
					"	Password: <input type=\"password\" name=\"password\" ><br>\n" .
					"	<input type=\"submit\" value=\"Login\">\n" .
					"</form>\n";
			$retv = 1;	
		}
	}
	else
	{
		echo 	"<b>Please login<br >\n" .
				"<form action=\"" . curPageURL() . "\" method=\"post\">\n" .
				"	<input type=\"hidden\" name=\"action\" value=\"login\"><br>\n" .
				"	Username: <input type=\"text\" name=\"user\" ><br>\n" .
				"	Password: <input type=\"password\" name=\"password\" ><br>\n" .
				"	<input type=\"submit\" value=\"Login\">\n" .
				"</form>\n";
		$retv = 1;
	}
	return $retv;
}

function selectTimePeriod()
{	//used to select which (time period)'s information will be viewed
	if (!(array_key_exists("timeperiod", $_POST)))
	{
		$_POST['timeperiod'] = "all";
	}
	
	if ($_POST['timeperiod'] == "2011")
	{
		$_SESSION['period'] = "2011";
	}
	else if ($_POST['timeperiod'] == "2012")
	{
		$_SESSION['period'] = "2012";
	}
	else if ($_POST['timeperiod'] == "2013")
	{
		$_SESSION['period'] = "2013";
	}
	else if ($_POST['timeperiod'] == "all")
	{
		$_SESSION['period'] = "all";
	}

	echo "<div>\n" .
		 "<form action=\"" . curPageURL() . "\" method=\"post\">\n" .
		 "	<select name=timeperiod>\n";
	
	echo "		<option ";
	if ($_SESSION['period'] == "all")
		echo "selected ";
	echo	"value=\"all\">Everything</option>\n";
	
	echo "		<option ";
	if ($_SESSION['period'] == "2011")
		echo "selected ";
	echo	"value=\"2011\">2011 Tax Year</option>\n";
	
	echo "		<option ";
	if ($_SESSION['period'] == "2012")
		echo "selected ";
	echo	"value=\"2012\">2012 Tax Year</option>\n";
	echo "		<option ";
	if ($_SESSION['period'] == "2013")
		echo "selected ";
	echo	"value=\"2013\">2013 Tax Year</option>\n";
	echo	"	</select>\n";
	echo "	<input type=\"submit\" value=\"Go\">\n" .
		"</form>\n" .
		"</div>\n";
}

function getPeriodComparison($fieldname)
{	//returns the proper portion of a mysql statement to filter for the time period selected
	if ($_SESSION['period'] == "2011")
	{
		return " $fieldname  > '2010-12-31'" .
			" AND $fieldname  < '2012-01-01'";
	}
	else if ($_SESSION['period'] == "2012")
	{
		return " $fieldname  > '2011-12-31'" .
			" AND $fieldname < '2013-01-01'";
	}
	else if ($_SESSION['period'] == "2013")
	{
		return " $fieldname  > '2012-12-31'" .
			" AND $fieldname < '2014-01-01'";
	}
	else
	{
		return "";
	}
}

//close the database connection
function closeDataBase()
{
	global $mysql_db;
	$mysql_db->close();
}

function print_contact($contact_id)
{	//outputs the contact name
	global $mysql_db, $config;

	$output = "";

	$query = "SELECT last_name, first_name FROM contacts WHERE emp_id = " . $contact_id;
	
	$contact_results = $mysql_db->query($query);
	
	$last_name_first = $config['last_name_first']; 
	
	if ($row = $contact_results->fetch_array(MYSQLI_BOTH))
	{
		if ($last_name_first == 1)
		{
			if ($row['last_name'] != "")
				$output .= $row['last_name'];
			if ($row['first_name'] != "")
				$output .= ', ' . $row['first_name'];
		}
		else
		{
			if ($row['first_name'] != "")
				$output .= $row['first_name'];
			if ($row['last_name'] != "")
				$output .= ' ' . $row['last_name'];
		}
		$contact_results->free();
	}
	else
	{
		$output .= "ERROR";
	}
	return $output;
}

function print_prop($prop_id, $database)
{	//prints property information
	global $mysql_db;
	$query = "SELECT address, city, state, zip, description FROM properties WHERE id = " . $prop_id;
	$output = '';
	$contact_results = $mysql_db->query($query);
	
	if ($row = $contact_results->fetch_array(MYSQLI_BOTH))
	{
		$output .= $row['address'];
		if ($row['city'] != "")
		{
			$output .= ", " . $row['city'];
		}
		if ($row['state'] != "")
		{
			$output .= " " . $row['state'];
		}
		if ($row['zip'] != "")
		{
			$output .= " " . $row['zip'];
		}
		echo "<br >\n";
		if ($row['description'] != "")
		{
			$output .= " " . $row['description'];
		}
		$contact_results->free();
	}
	else
	{
		$output .= "ERROR";
	}
	return $output;
}


function get_category_sum($contact, $category, $database)
{
	global $mysql_db;
	$query = "SELECT date_paid, amount_earned, pay_to, paid_by FROM payments WHERE (paid_by = " . $contact .
		" OR pay_to = " . $contact . ")" .
		" AND `category` = '" .
		$category . "'";
	if (getPeriodComparison("date_earned") != "")
	{
		$query = $query . " AND" . getPeriodComparison("date_earned");
	}
	$query = $query . " ORDER BY date_paid DESC ";
	$payment_results = $mysql_db->query($query);

	$assets = 0.0;
	$liable = 0.0;
	$o_assets = 0.0;
	$o_liable = 0.0;	
	while($row = $payment_results->fetch_array(MYSQLI_BOTH))
	{
		if ($row['date_paid'] != "0000-00-00")
		{
			if ($row['pay_to'] == $contact)
			{
				$assets += $row['amount_earned'];
			}
			if ($row['paid_by'] == $contact)
			{
				$liable += $row['amount_earned'];
			}
		}
		else
		{
			if ($row['pay_to'] == $contact)
			{
				$o_assets += $row['amount_earned'];
			}
			else
			{
				$o_liable += $row['amount_earned'];
			}
		}
	}
	$payment_results->free();
	$value = "$" . $assets . " [$" . $o_assets . "], " .
		"($" . $liable . ") [($" . $o_liable . ")]";
//	echo "Assets: $" . $assets . "<br>\n";
//	echo "Outstanding assets: $" . $o_assets . "<br>\n";
//	echo "Liabilities: $" . $liable . "<br>\n";
//	echo "Outstanding liabilities: $" . $o_liable . "<br>\n";
//	echo "Net Worth: $" . ($assets - $liable) . " ($";
//	echo ($assets + $o_assets - $liable - $o_liable). ")<br>\n";
	return $value;
}

function get_phone_options($id1, $id2)
{
	global $mysql_db;
	for ($i = 0; $i < 2; $i++)
	{
		$query = "SELECT phone_mobile, phone_home, phone_other " .
			"FROM contacts WHERE emp_id = ";
		if ($i == 0)
			$query .= $id1;
		else
			$query .= $id2;

		$query .= " LIMIT 1;";
		$result = $mysql_db->query($query);
		if ($phonerow = $result->fetch_array(MYSQLI_BOTH))
		{
			for ($j = 0; $j < 3; $j++)
			{
				if ($i == 0)
					$phone[$i*3+$j]['name'] = print_contact($id1);
				else
					$phone[$i*3+$j]['name'] = print_contact($id2);
				switch($j)
				{
				case 1:
					$phone[$i*3+$j]['number'] = $mysql_db->real_escape_string($phonerow['phone_home']);
					break;
				case 2:
					$phone[$i*3+$j]['number'] = $mysql_db->real_escape_string($phonerow['phone_other']);
					break;
				default:
					$phone[$i*3+$j]['number'] = $mysql_db->real_escape_string($phonerow['phone_mobile']);
					break;
				}
			}
		}
	}
	return $phone;
}

function list_location($pre_name, $loc_num)
{
	global $mysql_db;
	echo "		<option value=\"" . $loc_num . "\">" . $pre_name . "</option>\n";
	
	$query = "SELECT * FROM locations WHERE owner = " . $_SESSION['user']['emp_id'] . " AND position = " . $loc_num . ";";
	$result = $mysql_db->query($query);
	while ($row = $result->fetch_array(MYSQLI_BOTH))
	{
		if ($row['id'] != $loc_num)
		{	
			list_location($pre_name . ',' . $row['description'], $row['id']);
		}
	}
}

function get_location($equ)
{
	global $mysql_db;
	$query = "SELECT * FROM equipment WHERE owner = " . $_SESSION['user']['emp_id'] . " AND id = " . $equ . ";";
	$result = $mysql_db->query($query);
	if ($row = $result->fetch_array(MYSQLI_BOTH))
	{
		return $row['location'];	
	}
	else
	{
		die ("Invalid item specified");
	}
}

function print_location($location)
{
	global $mysql_db;
	$query = "SELECT * FROM locations WHERE owner = " . $_SESSION['user']['emp_id'] . " AND id = " . $location . ";";
	$result = $mysql_db->query($query);
	if ($row = $result->fetch_array(MYSQLI_BOTH))
	{
		if ($row['position'] != $location)
		{
			print_location($row['position']);
			echo ", " . $row['description'];
			if ($row['location'] != 0)
			{
				echo "[" . $row['location'] . "]";
			}
		}
		else
		{
			echo $row['description'];
			if ($row['location'] != 0)
			{
				echo "[" . $row['location'] . "]";
			}
		}
	}
}

?>
