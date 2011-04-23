<?php

if ('global.php' == basename($_SERVER['SCRIPT_FILENAME']))
	die ('<h2>Direct File Access Prohibited</h2>');
	
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

function bottomPageURL()
{	//TODO: rename this function, its not very accurately named
	//TODO: fix this function
	//this is supposed to get the address of the current folder the page is in (https://www.example.com/folder)
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .= "://";
	if (($_SERVER["SERVER_PORT"] != "80") && ($_SERVER["SERVER_PORT"] != "443"))
	{
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
	}
	else
	{
		$pageURL .= $_SERVER["SERVER_NAME"];
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

function login_code()
{	//prints and executes code for the login script
	if ($_POST["action"] == "login")
	{	//retrieve submitted username and password, if applicable
		$username = $_POST["user"];
		$passworder = $_POST["password"];
	
		$_SESSION['username'] = $username;
		$_SESSION['password'] = $passworder;
	}
	if ($_POST["action"] == "logout")
	{
		unset($_SESSION['username']);
		unset($_SESSION['password']);
	}
}

function quiet_login($database)
{
	if (isset($_SESSION['username']))
	{
		$query = "SELECT * FROM contacts WHERE username='" . $_SESSION['username'] . "' LIMIT 1;";
		$results = mysql_query($query, $database);
		if ($row = mysql_fetch_array($results))
		{
			$_SESSION['id'] = $row['emp_id'];
			//good
		}
		else
		{	//force logout and print error message
			unset($_SESSION['username']);
			unset($_SESSION['password']);
			echo	"<h3>Unregistered username</h3><br >\n" . 
					"<form action=\"" . curPageURL() . "\" method=\"post\">\n" .
					"	<input type=\"hidden\" name=\"action\" value=\"login\"><br>\n" .
					"	Username: <input type=\"text\" name=\"user\" ><br>\n" .
					"	Password: <input type=\"password\" name=\"password\" ><br>\n" .
					"	<input type=\"submit\" value=\"Login\"/>\n" .
					"</form>";
			exit(1);	
		}
	}
	else
	{
		echo 	"<form action=\"" . curPageURL() . "\" method=\"post\">\n" .
				"	<input type=\"hidden\" name=\"action\" value=\"login\"><br>\n" .
				"	Username: <input type=\"text\" name=\"user\" ><br>\n" .
				"	Password: <input type=\"password\" name=\"password\" ><br>\n" .
				"	<input type=\"submit\" value=\"Login\" />\n" .
				"</form>";
		exit(1);
	}
}

function login_button($database)
{	
	if (isset($_SESSION['username']))
	{
		$query = "SELECT * FROM contacts WHERE username='" . $_SESSION['username'] . "' LIMIT 1;";
		$results = mysql_query($query, $database);
		if ($row = mysql_fetch_array($results))
		{
			$_SESSION['id'] = $row['emp_id'];
			echo 	"<h3>Welcome ";
			print_contact($row['emp_id'], $database);
			echo	"</h3><br >\n";
			echo	"<form action=\"" . bottomPageURL() . "\" method=\"post\">\n" .
					"	<input type=\"hidden\" name=\"action\" value=\"logout\"><br>\n" .
					"	<input type=\"submit\" value=\"Logout\"/>\n" .
					"</form>";
		}
		else
		{	//force logout and print error message
			unset($_SESSION['username']);
			unset($_SESSION['password']);
			echo	"<h3>Unregistered username</h3><br >\n" . 
					"<form action=\"" . curPageURL() . "\" method=\"post\">\n" .
					"	<input type=\"hidden\" name=\"action\" value=\"login\"><br>\n" .
					"	Username: <input type=\"text\" name=\"user\" ><br>\n" .
					"	Password: <input type=\"password\" name=\"password\" ><br>\n" .
					"	<input type=\"submit\" value=\"Login\"/>\n" .
					"</form>";
			exit(1);	
		}
	}
	else
	{
		echo 	"<form action=\"" . curPageURL() . "\" method=\"post\">\n" .
				"	<input type=\"hidden\" name=\"action\" value=\"login\"><br>\n" .
				"	Username: <input type=\"text\" name=\"user\" ><br>\n" .
				"	Password: <input type=\"password\" name=\"password\" ><br>\n" .
				"	<input type=\"submit\" value=\"Login\"/>\n" .
				"</form>";
		exit(1);
	}
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
	else
	{
		$_SESSION['period'] = "";
	}

	echo	"<form action=\"" . curPageURL() . "\" method=\"post\">\n" .
		"	<input type=\"hidden\" name=\"action\" value=\"change_period\"><br>\n" .
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
	echo	"value=\"2011\">2012 Tax Year</option>\n";
	echo "	<input type=\"submit\" value=\"Go\"/>\n" .
		"</form>";
}

function getPeriodComparison($fieldname)
{	//returns the proper portion of a mysql statement to filter for the time period selected
	if ($_SESSION['period'] == "2011")
	{
		return " $fieldname > 2010-04-14 AND $fieldname < 2011-04-16";
	}
	else if ($_SESSION['period'] == "2012")
	{
		return " $fieldname > 2011-04-14 AND $fieldname < 2012-04-16";
	}
	else
	{
		return "";
	}
}

//open database connection
//be sure to either change account information here or something
function openDatabase()
{
	if (isset($_SESSION['username']))
	{
		$username = $_SESSION['username'];
		$passworder = $_SESSION['password'];
	}
	else
	{
		$username = $_POST["user"];
		$passworder = $_POST["password"];
	
		$_SESSION['username'] = $username;
		$_SESSION['password'] = $passworder;
	}
	
	if ($username == "")
	{
		$username = "anon";
		$passworder = "";
	}
	
	$dbase = @mysql_connect("localhost", $username, $passworder);
	if (!$dbase)
	{
		unset($_SESSION['username']);
		unset($_SESSION['password']);
		die ('Invalid login credentials');
	}
	mysql_select_db("thermal", $dbase);
	return $dbase;
}

function closeDataBase($dbase)
{
	//close the database connection
	mysql_close($dbase);
}

//permission flags
	//0 = access to all contacts when true
	//1 = public contact when true
	//2 = access all reports
function checkPermission($database, $element)
{
	$query = "SELECT permissions FROM contacts WHERE emp_id=" . $_SESSION['id'];
	$results = mysql_query($query, $database);
	if($row = @mysql_fetch_array($results))
	{
		$perms = str_split($row['permissions']);
		if ($perms[$element] == '1')
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	else
	{	//definitely return false
		return false;
	}
}
	
function checkContactPermission($contact, $database)
{	//determines if contact access is unlimited (returns true)
	
	$query = "SELECT permissions FROM contacts WHERE emp_id=" . $contact;
	$results = mysql_query($query, $database);
	if($row = mysql_fetch_array($results))
	{
		$perms = str_split($row['permissions']);
		if ($perms[$flag_id] == '1')
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	else
	{	//definitely return false
		return false;
	}
}

function print_contact($contact_id, $database)
{	//outputs the contact name
	$query = "SELECT * FROM contacts WHERE emp_id = " . $contact_id;
	$contact_results = mysql_query($query, $database);
	
	if ($row = mysql_fetch_array($contact_results))
	{
		echo $row['last_name'];
		if ($row['first_name'] != "")
		{
			echo ", " . $row['first_name'];
		}
	}
	else
	{
		echo "ERROR";
	}
}

function print_prop($prop_id, $database)
{	//prints property information
	$query = "SELECT * FROM properties WHERE id = " . $prop_id;
	$contact_results = mysql_query($query, $database);
	
	if ($row = mysql_fetch_array($contact_results))
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
	}
	else
	{
		echo "ERROR";
	}
}


function get_category_sum($contact, $category, $database)
{
	$query = "SELECT * FROM payments WHERE (paid_by = " . $contact .
		" OR pay_to = " . $contact . ")" .
		" AND `category` = '" .
		$category . "'";
	if (getPeriodComparison("date_earned") != "")
	{
		$query = $query . " AND" . getPeriodComparison("date_earned");
	}
	$query = $query . " ORDER BY date_paid DESC ";
	$payment_results = mysql_query($query, $database);

	$assets = 0.0;
	$liable = 0.0;
	$o_assets = 0.0;
	$o_liable = 0.0;	
	while($row = mysql_fetch_array($payment_results))
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
