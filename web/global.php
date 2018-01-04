<?php

if (!isset($config))
{
	die ('<h2>Direct File Access Prohibited</h2>');
}

include("passwords.php");
include("include/contacts.php");

function sitename()
{
	global $config;
	echo $config["name"];
}

function blank_check($checkme)
{
	if ($checkme == "")
		return "&nbsp;";
	else
		return $checkme;
}

function do_css_one_file($name, $extras)
{
	echo '<link rel="stylesheet" type="text/css" href="' . $name . '" ' . 
		$extras .
		'/>' . "\n";
}

function do_css()
{
	global $config;
	$pfile = "global";
	if ($config['testing'] == 1)
	{
		$file = 'css/' . $pfile . rand() . ".css";
	}
	else
	{
		$file = 'css/' . $pfile . '1' . '.css';
	}
	do_css_one_file($file, "media=\"screen\"");

	$pfile = "pglobal";
	if ($config['testing'] == 1)
	{
		$file = 'css/' . $pfile . rand() . ".css";
	}
	else
	{
		$file = 'css/' . $pfile . '1' . '.css';
	}
	do_css_one_file($file, "media=\"print\"");


	$pfile = "mglobal";
	if ($config['testing'] == 1)
	{
		$file = 'css/' . $pfile . rand() . ".css";
	}
	else
	{
		$file = 'css/' . $pfile . '1' . '.css';
	}
	do_css_one_file($file, "media=\"only screen and (max-device-width: 800px)\"");

}

function do_top_menu($indx)
{
	echo "<div>\n<ul class=\"topmenu\">\n";
	echo "	<li><a ";
	if ($indx == 0)
		echo "class=selected ";
	echo "href=\"" . rootPageURL() . "/index.php\">Home</a></li>\n";
	echo "	<li><a ";
	if ($indx == 1)
		echo "class=selected ";
	echo "href=\"" . rootPageURL() . "/payments.php\">Payments</a></li>\n";
	echo "	<li><a ";
	if ($indx == 2)
		echo "class=selected ";
	echo "href=\"" . rootPageURL() . "/contacts.php\">Contacts</a></li>\n";
	echo "	<li><a ";
	if ($indx == 3)
		echo "class=selected ";
	echo "href=\"" . rootPageURL() . "/jobs.php\">Jobs</a></li>\n";
	echo "	<li><a ";
	if ($indx == 4)
		echo "class=selected ";
	echo "href=\"" . rootPageURL() . "/locations.php\">Locations</a></li>\n";
	echo "	<li><a ";
	if ($indx == 5)
		echo "class=selected ";	
	echo "href=\"" . rootPageURL() . "/maintenance.php\">Maintenance</a></li>\n";
	echo "	<li><a ";
	if ($indx == 6)
		echo "class=selected ";	
	echo "href=\"" . rootPageURL() . "/cp.php\">Control Panel</a></li>\n";
	echo "</ul>\n</div>\n";
	echo "<div class=\"clear\"></div>\n";
}

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
		if (isset($_SERVER['HTTP_USER_AGENT']))
		{
			if ($_SESSION['HTTP_USER_AGENT'] != md5($_SERVER['HTTP_USER_AGENT']))
			{	/* Prompt for password */
				unset($_SESSION['username']);
				unset($_SESSION['password']);
				unset($_SESSION['HTTP_USER_AGENT']);
				//exit;
			}
		}
		else
		{
			if ($_SESSION['HTTP_USER_AGENT'] != md5(""))
			{	/* Prompt for password */
				unset($_SESSION['username']);
				unset($_SESSION['password']);
				unset($_SESSION['HTTP_USER_AGENT']);
				//exit;
			}
		}
	}
	else
	{
		if (isset($_SERVER['HTTP_USER_AGENT']))
		{
			$_SESSION['HTTP_USER_AGENT'] = md5($_SERVER['HTTP_USER_AGENT']);
		}
		else
		{
			$_SESSION['HTTP_USER_AGENT'] = md5("");
		}
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
	print_r($config);
	$mysql_db = new mysqli($config["database_server"], 
		$config["database_username"], $config["database_password"], 
		$config["database_name"], $config["database_port"]);
	if ($mysql_db->connect_errno)
	{
		echo "Failed to connect to MySQL: (" . $mysql_db->connect_errno . ") " .
			$mysql_db->connect_error . "<br >\n";
		die("Database connection failed");
	}
	//TODO: implement calling this function
	//mysqli_set_charset()
}


//r = read
//w = write
//p = modify password
function check_permission($table, $idfrom, $idto, $mask)
{	//returns an array containing "master", "public", "global", "normal", "none"
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
}

function login_button()
{
	global $mysql_db;
	echo "<b>Please login</b>\n" .
		"<form action=\"" . curPageURL() . "\" method=\"post\" autocomplete=\"on\" >\n" .
		"	<input type=\"hidden\" name=\"action\" value=\"login\">\n" .
		"	<label for=\"username\"> Username: <input type=\"text\" name=\"username\" autocomplete=\"on\" ><br>\n" .
		"	<label for=\"password\"> Password: <input type=\"password\" name=\"password\" autocomplete=\"on\" ><br>\n" .
		"	<input class=\"buttons\" type=\"submit\" name=\"do_login\" value=\"Login\">\n" .
		"</form>\n";
}

function invalid_username_password()
{
	echo	"<h3>Invalid username or password</h3>\n";
	login_button();
}

function attempt_registration($attempt_username, $attempt_email, $attempt_pw)
{
	global $mysql_db, $config;
	//validate the email address?
	if (!filter_var($attempt_email, FILTER_VALIDATE_EMAIL))
	{
		echo "Invalid email address!<br>\n";
		$_POST["action"] = "register";
		return 0;	//invalid email
	}
	if (contacts::does_user_exist($attempt_username))
	{
		return 0;
	}
	
	//ok, create the user
	contacts::create_contact($attempt_username, $attempt_email);
	$temp_uid = contacts::get_id_num($attempt_username);
	contacts::setup_user_pword($temp_uid, $attempt_pw);
	
	return 1;
}

function login_code($quiet)
{	//prints and executes code for the login script
	//return value of 1 means don't do anything else
		//the login script has closed the fence for some reason
	global $mysql_db, $config;
	#TODO : produce the div tags when quiet=1 and output is actually produced
	if ($quiet == 0)
	{
		//this div includes login, logout, and change password widgets
		echo "<div id=\"login_control\">\n";
	}
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

	if (($_POST["action"] == "create_user") && ($config['allow_user_create']=1))
	{
		if (isset($_POST["username"]))
		{
			$attempt_username = $mysql_db->real_escape_string($_POST["username"]);
			
			if (isset($_POST["email"]))
			{
				$attempt_email = $mysql_db->real_escape_string($_POST["email"]);
				if (isset($_POST["pass2"]))
				{
					$attempt_pass1 = $mysql_db->real_escape_string($_POST["pass2"]);
					if (isset($_POST["pass3"]))
					{
						$attempt_pass2 = $mysql_db->real_escape_string($_POST["pass3"]);						
						if ($attempt_pass1 != $attempt_pass2)
						{
							echo "Passwords do not match!<br>\n";
							$_POST["action"] = "register";
						}
						else
						{
							if (($attempt_pass1 != '') && 
								($attempt_username != '') &&
								($attempt_email != ''))
							{
								if (attempt_registration($attempt_username, $attempt_email, $attempt_pass1)==0)
								{
									echo "Failed to register<br>\n";
								}
								else
								{
									echo "Registered successfully<br>\n";
								}
							}
							else
							{
								$_POST["action"] = "register";
							}
						}
					}
				}
			}
		}
	}
	
	//If chain to determine what to do
	if (($_POST["action"] == "register") && ($config['allow_user_create']=1))
	{
		if (isset($_POST["username"]))
		{
			$previous_username = $mysql_db->real_escape_string($_POST["username"]);
		}
		else
		{
			$previous_username = "";
		}
		if (isset($_POST["email"]))
		{
			$previous_email = $mysql_db->real_escape_string($_POST["email"]);
		}
		else
		{
			$previous_email = "";
		}
		echo 	"<form action=\"" . curPageURL() . "\" method=\"post\">\n" .
					"	<input type=\"hidden\" name=\"action\" value=\"create_user\">\n" .
					"	Username: <input type=\"text\" name=\"username\" ><br>\n" .
					"	Email: <input type=\"text\" name=\"email\" ><br>\n" .
					"	Password: <input type=\"password\" name=\"pass2\" ><br>\n" .
					"	Password again: <input type=\"password\" name=\"pass3\" ><br>\n" .
					"	<input class=\"buttons\" type=\"submit\" value=\"Register\">\n" .
					"</form>\n";
	}
	else if ($_POST["action"] == "login")
	{	//retrieve submitted username and password, if applicable
		$username = $mysql_db->real_escape_string($_POST["username"]);
		$passworder = $mysql_db->real_escape_string($_POST["password"]);
	
		$_SESSION['username'] = $username;
	}
	else if ($_POST["action"] == "logout")
	{
		echo "Logout<br>\n";
		unset($_SESSION['username']);
		unset($_SESSION['password']);
	}
	else if ($_POST["action"] == "change_pass")
	{
		$retv = 1;
		if ($quiet == 0)
		{
			#TODO : create button to change mind on changing password
			echo 	"<form action=\"" . curPageURL() . "\" method=\"post\">\n" .
					"	<input type=\"hidden\" name=\"action\" value=\"apply_pass\">\n" .
					"	Old password: <input type=\"password\" name=\"pass1\" ><br>\n" .
					"	New password: <input type=\"password\" name=\"pass2\" ><br>\n" .
					"	New password again: <input type=\"password\" name=\"pass3\" ><br>\n" .
					"	<input class=\"buttons\" type=\"submit\" value=\"Change my password\">\n" .
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

	#logic for logging in and normal activity
	if (isset($_SESSION['username']))
	{
		$query = "SELECT * FROM contacts WHERE username='" . $_SESSION['username'] . "' LIMIT 1;";
		$results = $mysql_db->query($query);
		if ($results)
		{
			$row = $results->fetch_array(MYSQLI_BOTH);
			#TODO : more testing of the failed login logic
			if ($row['fail_logins'] >= $config['max_fail_logins'])
			{	//TODO: set time period for waiting to login
				echo "Failed login too many times error<br>\n";
				unset($_SESSION['username']);
				unset($_SESSION['password']);
			}
			
			if ($_POST["action"] == "login")
			{
				//check to see if the password matches and the stretching does not match
				//this piece allows the stretching value to be changed at any given time
				//the only drawback is the password is hashed twice when the user logs in
				//in order to change the stretching value
				$temp = hash_password($passworder, $row['salt'], $row['stretching']);
				if ( ($row['password'] == $temp) && ($row['stretching'] != $config['key_stretching_value']) )
				{	//password is good, key stretching needs to be fixed
					contacts::mod_user_pword($row['emp_id'], $passworder);
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
					$temp = hash_password($passworder, $row['salt'], $config['key_stretching_value']);
					$row['password'] = $temp;
				}

				$_SESSION['password'] = $temp;
			}
			if (($row['password'] == $_SESSION['password']) && isset($_SESSION['password']) && ($_SESSION['password'] <> ""))
			{	#successful login
				#TODO : limit the number of valid sessions for users? create a valid session table?
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
					echo "</h3>\n";
					echo "<form action=\"" . curPageURL() . "\" method=\"post\">\n" .
						 "	<input type=\"hidden\" name=\"action\" value=\"logout\">\n" .
						 "	<input class=\"buttons\" type=\"submit\" value=\"Logout\">\n" .
						 "</form>\n";
					if ($_POST["action"] != "change_pass")
					{
						echo	"<form action=\"" . curPageURL() . "\" method=\"post\">\n" .
								"	<input type=\"hidden\" name=\"action\" value=\"change_pass\">\n" .
								"	<input class=\"buttons\" type=\"submit\" value=\"Change my password\">\n" .
								"</form>\n";
					}
				}
			}
			else
			{	//password fail match
				$query = "UPDATE contacts SET fail_logins=fail_logins+1 WHERE username = " . $_SESSION['username'] . ";";
				$mysql_db->query($query);
				unset($_SESSION['username']);
				unset($_SESSION['password']);
				invalid_username_password();
				$retv = 1;
			}
			$results->close();
		}
		else
		{	//contact not found
			unset($_SESSION['username']);
			unset($_SESSION['password']);
			invalid_username_password();
			$retv = 1;	
		}
	}
	else
	{
		login_button();
		if ($config['allow_user_create']=1)
		{
			echo "<form action=\"" . curPageURL() . "\" method=\"post\">\n" .
					"	<input type=\"hidden\" name=\"action\" value=\"register\">\n" .
					"	<input class=\"buttons\" type=\"submit\" value=\"Register\">\n" .
					"</form>\n";
		}
		$retv = 1;
	}

	if ($quiet == 0)
	{
		echo "</div>\n";
	}	

	return $retv;
}

function selectTimePeriod()
{	//used to select which (time period)'s information will be viewed
	global $mysql_db;

	if (!(isset($_SESSION['period'])))
	{
		$_SESSION['period'] = "all";
	}
	if (isset($_POST['timeperiod']))
	{
        	$_SESSION['period'] = $_POST['timeperiod'];
	}

	echo "<div id=\"tax_year_select\">\n" .
		 "<form action=\"" . curPageURL() . "\" method=\"post\">\n" .
		 "	<select name=timeperiod>\n";
	
	$query = "SELECT DISTINCT year(date_paid) FROM `payments` WHERE 1";
	$result = $mysql_db->query($query);
	echo "        <option ";
	if ($_SESSION['period'] == "all")
    		echo "selected ";
	echo	"value=\"all\">Everything</option>\n";    
	while($row = $result->fetch_array(MYSQLI_BOTH))
	{
        echo "    	<option ";
        if ($_SESSION['period'] == $row["year(date_paid)"])
    	    echo "selected ";
        echo "value=\"" . $row["year(date_paid)"] . "\">" . $row["year(date_paid)"] . " Tax Year</options>\n";
	}
	echo	"	</select>\n";
	echo "	<input class=\"buttons\" type=\"submit\" value=\"Go\">\n" .
		"</form>\n" .
		"</div>\n";
}

function getPeriodComparison($fieldname)
{	//returns the proper portion of a mysql statement to filter for the time period selected
	if (!(isset($_SESSION['period'])))
	{
		$_SESSION['period'] = "all";
	}
	if ($_SESSION['period'] == "all")
	{
        return "";
	}
    else
    {
		return " $fieldname  >= '" . $_SESSION['period'] . "-01-01'" .
			" AND $fieldname  <= '" . $_SESSION['period'] . "-12-31'";
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

function get_category_sum($contact, $category)
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