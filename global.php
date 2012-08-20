<?php

if ('global.php' == basename($_SERVER['SCRIPT_FILENAME']))
	die ('<h2>Direct File Access Prohibited</h2>');

include("passwords.php");

$config = parse_ini_file("/etc/web-admin/config.ini");
	
function curPageURL()
{
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
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
	if ($_SERVER["HTTPS"] == "on") 
		{$pageURL .= "s";}
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

function login_code($quiet)
{	//prints and executes code for the login script
	//return value of 1 means don't do anything else
		//the login script has closed the fence for some reason
	global $mysql_db, $config;
	$retv = 0;
	
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
			store_user_pword($uid, $oldpass, $newpass);
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
				$_SESSION['password'] = hash_password($_SESSION['password'], $row['salt']);
			}
			if ($row['password'] == $_SESSION['password'])
			{
				$_SESSION['user'] = $row;
				if ($quiet == 0)
				{
					if ($_POST["action"] == "login")
					{
						$query = "UPDATE contacts SET fail_pass_change=0 WHERE emp_id = " . $_SESSION['user']['emp_id'] . ";";
						$mysql_db->query($query);
						$query = "UPDATE contacts SET fail_logins=0 WHERE emp_id = " . $_SESSION['user']['emp_id'] . ";";
						$mysql_db->query($query);
					}
					echo 	"<h3>Welcome ";
					print_contact($_SESSION['user']['emp_id']);
					echo	"</h3><br >\n";
					echo	"<form action=\"" . curPageURL() . "\" method=\"post\">\n" .
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
		{	//contact not found
			unset($_SESSION['username']);
			unset($_SESSION['password']);
			echo	"<h3>Invalid username or password</h3><br >\n" . 
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
	{
		echo 	"<form action=\"" . curPageURL() . "\" method=\"post\">\n" .
				"	<input type=\"hidden\" name=\"action\" value=\"login\"><br>\n" .
				"	Username: <input type=\"text\" name=\"user\" ><br>\n" .
				"	Password: <input type=\"password\" name=\"password\" ><br>\n" .
				"	<input type=\"submit\" value=\"Login\">\n" .
				"</form>\n";
		$retv = 1;
	}
	return $retv;
}

function store_user_pword($uid, $oldpass, $newpass)
{
	//TODO: store the number used for key stretching
	//TODO: check the number actually used for key stretching against
		//the configured number upon successful password entry
		//regenerate the password if the numbers are different
	
	global $mysql_db, $config;
	
	$query = "SELECT * FROM contacts WHERE emp_id = '" . $uid . "' LIMIT 1;";
	
	$results = $mysql_db->query($query);
	if ($results)
	{
		$row = $results->fetch_array(MYSQLI_BOTH);
		if ($row['fail_pass_change'] >= $config['max_fail_pass_changes'])
		{
			unset($_SESSION['username']);
			unset($_SESSION['password']);
			echo	"<h3>Invalid username or password</h3><br >\n";
		}
		else if ($row['password'] == hash_password($oldpass, $row['salt']))
		{	//ok the old password matches
			$salt = generate_salt();
	
			$query = "UPDATE contacts SET `salt` = '" . $salt . "' WHERE emp_id = " . $uid . ";";
			if ($mysql_db->query($query) == TRUE)
			{
				echo "User salt stored successfully<br >\n";
				$hash_pass = hash_password($newpass, $salt);
				$query = "UPDATE contacts SET `password` = '" . $hash_pass . "' WHERE emp_id = " . $uid . ";";
				if ($mysql_db->query($query) == TRUE)
				{
					echo "User password stored successfully<br >\n";
				}
				else
				{
					echo "Failed to save user password<br >\n";
				}
			}
			else
			{
				echo "Failed to save user salt<br >\n";
			}
		}
		else
		{	//password fail match
			unset($_SESSION['username']);
			unset($_SESSION['password']);
			$query = "UPDATE contacts SET fail_pass_change=fail_pass_change+1 WHERE emp_id = " . $uid . ";";
			$mysql_db->query($query);
			echo	"<h3>Invalid username or password</h3><br >\n";
		}
	}
	else
	{	//contact not found
		unset($_SESSION['username']);
		unset($_SESSION['password']);
		echo	"<h3>Invalid username or password</h3><br >\n";	
	}
	$results->close();
}

function selectTimePeriod()
{	//used to select which (time period)'s information will be viewed

	if ($_POST['timeperiod'] == "2011")
	{
		$_SESSION['period'] = "2011";
	}
	else if ($_POST['timeperiod'] == "2012")
	{
		$_SESSION['period'] = "2012";
	}
	else if ($_POST['timeperiod'] == "all")
	{
		$_SESSION['period'] = "all";
	}

	echo "<div>\n" .
		 "<form action=\"" . rootPageURL() . "\" method=\"post\">\n" .
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
	global $mysql_db;
	$query = "SELECT * FROM contacts WHERE emp_id = " . $contact_id;
	
	$contact_results = $mysql_db->query($query);
	
	if ($row = $contact_results->fetch_array(MYSQLI_BOTH))
	{
		echo $row['last_name'];
		if ($row['first_name'] != "")
		{
			echo ", " . $row['first_name'];
		}
		$contact_results->free();
	}
	else
	{
		echo "ERROR";
	}
}

function print_prop($prop_id, $database)
{	//prints property information
	global $mysql_db;
	$query = "SELECT * FROM properties WHERE id = " . $prop_id;
	$contact_results = $mysql_db->query($query);
	
	if ($row = $contact_results->fetch_array(MYSQLI_BOTH))
	{
		echo $row['address'];
		if ($row['city'] != "")
		{
			echo ", " . $row['city'];
		}
		if ($row['state'] != "")
		{
			echo " " . $row['state'];
		}
		if ($row['zip'] != "")
		{
			echo " " . $row['zip'];
		}
		echo "<br >\n";
		if ($row['description'] != "")
		{
			echo " " . $row['description'];
		}
		$contact_results->free();
	}
	else
	{
		echo "ERROR";
	}
}


function get_category_sum($contact, $category, $database)
{
	global $mysql_db;
	$query = "SELECT * FROM payments WHERE (paid_by = " . $contact .
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

?>
