<?php
namespace webAdmin;
include_once("passwords.php");

function test_config($config)
{
	if (!isset($config))
	{
		throw new \webAdmin\PermissionDeniedException();
	}
	if ($config==FALSE)
	{
		throw new \webAdmin\ConfigurationMissingException();
	}
}

function sitename($config)
{
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

function do_css($config)
{
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

function make_autocomplete($disp, $fill_val, $name, $id, $fillfunc, $suggestions, $autolist)
{
	echo "<div>\n";
	echo $disp . "\n";
	echo '<input class="fields" type="text" autocomplete="off" value="';
	if ($fill_val != 0)
	{
		echo print_contact($fill_val);
	}
	else
	{
		$fill_val = '';
	}
	echo '" name="' . $name . '" id="' . $name . '" 
		onkeyup="lookupLastName(this.value, \'' . $fillfunc . '\', 
			$(\'#' . $suggestions . '\'), 
			$(\'#' . $autolist . '\'),
			&quot;$(\'#' . $name . '\')&quot;,
			&quot;$(\'#' . $id . '\')&quot;,
			&quot;$(\'#' . $suggestions . '\')&quot;);"
		 >' . "\n";
		 //onblur="&quot;$(\'#' . $suggestions . '\')&quot;.hide().delay(500);"
		 //TODO when the onblur is added, autocomplete fails to insert data
	echo '	<div id="' . $suggestions . '" style="display: none;">' . "\n";
	echo '		<div id="' . $autolist . '">' . "\n";
	echo '			&nbsp;' . "\n";
	echo '		</div>' . "\n";
	echo '	</div><br >' . "\n";
	echo '	 <input type="hidden" value="' . $fill_val . '" name="' . $id . '" id="' . $id . '">' . "\n";
	echo "</div>\n";
}

function do_top_menu($indx, $config)
{
	echo "<div>\n<ul class=\"topmenu\">\n";
	echo "	<li><a ";
	if ($indx == 0)
	{
		echo "class=selected ";
	}
	echo "href=\"" . rootPageURL($config) . "/index.php\">Home</a></li>\n";
	echo "	<li><a ";
	if ($indx == 1)
	{
		echo "class=selected ";
	}
	echo "href=\"" . rootPageURL($config) . "/payments.php\">Payments</a></li>\n";
	echo "	<li><a ";
	if ($indx == 2)
	{
		echo "class=selected ";
	}
	echo "href=\"" . rootPageURL($config) . "/contacts.php\">Contacts</a></li>\n";
	echo "	<li><a ";
	if ($indx == 3)
	{
		echo "class=selected ";
	}
	echo "href=\"" . rootPageURL($config) . "/jobs.php\">Jobs</a></li>\n";
	echo "	<li><a ";
	if ($indx == 4)
	{
		echo "class=selected ";
	}
	echo "href=\"" . rootPageURL($config) . "/locations.php\">Locations</a></li>\n";
	echo "	<li><a ";
	if ($indx == 5)
	{
		echo "class=selected ";	
	}
	echo "href=\"" . rootPageURL($config) . "/maintenance.php\">Maintenance</a></li>\n";
	echo "	<li><a ";
	if ($indx == 6)
	{
		echo "class=selected ";	
	}
	echo "href=\"" . rootPageURL($config) . "/cp.php\">Control Panel</a></li>\n";
	echo "</ul>\n</div>\n";
	echo "<div class=\"clear\"></div>\n";
}

function curPageURL()
{
	$pageURL = 'http';
	if (array_key_exists("HTTPS", $_SERVER))
	{
		if ($_SERVER["HTTPS"] == "on") 
		{
			$pageURL .= "s";
		}
	}
	$pageURL .= "://";
	//set some defaults for unspecified things
	if (!array_key_exists("SERVER_PORT", $_SERVER))
	{
		$_SERVER["SERVER_PORT"] = "80";
	}
	if (!array_key_exists("SERVER_NAME", $_SERVER))
	{
		$_SERVER["SERVER_NAME"] = "www.example.com";
	}
	if (!array_key_exists("REQUEST_URI", $_SERVER))
	{
		$_SERVER["REQUEST_URI"] = "/example.php";
	}

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

function rootPageURL($config)
{
	$pageURL = 'http';
	if (array_key_exists("HTTPS", $_SERVER))
	{
		if ($_SERVER["HTTPS"] == "on") 
		{
			$pageURL .= "s";
		}
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
function openDatabase($config)
{
	$mysql_db = new \mysqli($config["database_server"], 
		$config["database_username"], $config["database_password"], 
		$config["database_name"], $config["database_port"]);
	if ($mysql_db->connect_errno)
	{
		throw new \webAdmin\DatabaseConnectionFailedException();
	}
	//TODO: implement calling this function
	//mysqli_set_charset()
	return $mysql_db;
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
	{
    		echo "selected ";
	}
	echo	"value=\"all\">Everything</option>\n";
	if ($result && $result->num_rows > 0)
	{
		while($row = $result->fetch_array(MYSQLI_BOTH))
		{
			echo "    	<option ";
			if ($_SESSION['period'] == $row["year(date_paid)"])
			{
				echo "selected ";
			}
			echo "value=\"" . $row["year(date_paid)"] . "\">" . $row["year(date_paid)"] . " Tax Year</options>\n";
		}
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
function closeDataBase($mysql_db)
{
	$mysql_db->close();
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
		{
			$query .= $id1;
		}
		else
		{
			$query .= $id2;
		}

		$query .= " LIMIT 1;";
		$result = $mysql_db->query($query);
		if ($phonerow = $result->fetch_array(MYSQLI_BOTH))
		{
			for ($j = 0; $j < 3; $j++)
			{
				if ($i == 0)
				{
					$phone[$i*3+$j]['name'] = print_contact($id1);
				}
				else
				{
					$phone[$i*3+$j]['name'] = print_contact($id2);
				}
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
	if ($pre_name != "")
	{
		echo "		<option value=\"" . $loc_num . "\">" . $pre_name . "</option>\n";
	}
	
	$query = "SELECT * FROM locations WHERE position = " . $loc_num . ";";
	$result = $mysql_db->query($query);
	while ($row = $result->fetch_array(MYSQLI_BOTH))
	{
		if ($row['id'] != $loc_num)
		{	
			if ($pre_name != "")
			{
				list_location($pre_name . ',' . $row['description'], $row['id']);
			}
			else
			{
				list_location($row['description'], $row['id']);
			}
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
		throw new Exception("Invalid item specified");
	}
}

function print_location($location)
{
	global $mysql_db;
	$query = "SELECT * FROM locations WHERE id = " . $location . ";";
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
