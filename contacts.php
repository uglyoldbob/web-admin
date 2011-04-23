<?php

include("global.php");
start_my_session();
header('Content-type: text/html; charset=utf-8');

require("forms.php");

$start_page = $_GET["page"];
if (is_numeric($start_page) == FALSE)
	$start_page = 0;

//check for GET data instead of POST data
//TODO this should be cleaned up
$contact = $_GET["contact"];
if (is_numeric($contact) == FALSE)
{
	$contact = 0;
}

$database = openDatabase();
//TODO : create a header.php

?>

<!DOCTYPE HTML SYSTEM>
<html>
<head>
<title>Thermal Specialists Contact Listing</title>
</head>
<body>

<?php

login_code();
login_button($database);

echo '<a href="' . bottomPageURL() . '">Return to main</a>' . "<br >\n";

//update contact information
if ($_POST["action"] == "update")
{
	$id_num = $contact;
	if (is_numeric($id_num) == FALSE)
		$id_num = 0;
	$last_name = mysql_real_escape_string($_POST["last_name"]);
	$first_name = mysql_real_escape_string($_POST["first_name"]);
	$classification = mysql_real_escape_string($_POST["classify"]);
	$eligibility = $_POST["eligible"];
	if (is_numeric($eligibility) == FALSE)
		$eligibility = 0;
	$ssn = mysql_real_escape_string($_POST["ssn"]);
	$mobile = mysql_real_escape_string($_POST["mobile"]);
	$home = mysql_real_escape_string($_POST["home"]);
	$other = mysql_real_escape_string($_POST["other"]);
	$website = mysql_real_escape_string($_POST["website"]);
	$email = mysql_real_escape_string($_POST["email"]);
	$street = mysql_real_escape_string($_POST["street"]);
	$city = mysql_real_escape_string($_POST["city"]);
	$state = mysql_real_escape_string($_POST["state"]);
	$zip = mysql_real_escape_string($_POST["zip"]);

	if ($id_num != 0)
	{
		if (checkContactPermission($id_num, $database))
		{	//has unlimited access to the contact
			$query = "REPLACE INTO `contacts` " . 
					 "(emp_id, last_name, first_name, classification, payment_eligible, " .
					 "ssn, phone_mobile, phone_home, phone_other, website, email, address, city, state, zipcode) " . 
					 "VALUES (" . 
					 "'" . $id_num . "'," .
					 "'" . $last_name .  "'," .
					 "'" . $first_name .  "'," .
					 "'" . $classification . "'," .
					 "'" . $eligibility .  "'," .
					 "'" . $ssn .  "'," .
					 "'" . $mobile . "'," .
					 "'" . $home .  "'," .
					 "'" . $other .  "'," .
					 "'" . $website . "'," .
					 "'" . $email . "'," .
					 "'" . $street .  "'," .
					 "'" . $city .  "'," .
					 "'" . $state . "'," .
					 "'" . $zip . "'" .
					 ");";
		}
		else
		{	//limited access to the contact
				$query = "REPLACE INTO `contacts` " . 
					 "(emp_id, last_name, first_name, classification, payment_eligible, " .
					 "ssn, phone_mobile, phone_home, phone_other, website, email, address, city, state, zipcode) " . 
					 "VALUES (" . 
					 "'" . $id_num . "'," .
					 "'" . $last_name .  "'," .
					 "'" . $first_name .  "'," .
					 "'" . $classification . "'," .
					 "'" . $eligibility .  "'," .
					 "'" . $ssn .  "'," .
					 "'" . $mobile . "'," .
					 "'" . $home .  "'," .
					 "'" . $other .  "'," .
					 "'" . $website . "'," .
					 "'" . $email . "'," .
					 "'" . $street .  "'," .
					 "'" . $city .  "'," .
					 "'" . $state . "'," .
					 "'" . $zip . "'" .
					 ");";
		}
		$query = "UPDATE `contacts` SET " . 
				"`last_name` = '" . $last_name . 
				"', `first_name` = '" . $first_name . 
				"', `classification` = '" . $classification .
				"', `payment_eligible` = " . $eligibility . 
				", `ssn` = '" . $ssn . 
				"', `phone_mobile` = '" . $mobile .
				"', `phone_home` = '" . $home . 
				"', `phone_other` = '" . $other . 
				"', `website` = '" . $website .
				"', `email` = '" . $email .
				"', `address` = '" . $street . 
				"', `city` = '" . $city . 
				"', `state` = '" . $state .
				"', `zipcode` = '" . $zip .
				"' WHERE `emp_id` = " . $id_num . ";";
	}
	else
	{
		$query = "INSERT INTO `contacts` " . 
				 "(last_name, first_name, classification, payment_eligible, " .
				 "ssn, phone_mobile, phone_home, phone_other, website, email, address, city, state, zipcode) " . 
				 "VALUES (" . 
				 "'" . $last_name .  "'," .
				 "'" . $first_name .  "'," .
				 "'" . $classification . "'," .
				 "'" . $eligibility .  "'," .
				 "'" . $ssn .  "'," .
				 "'" . $mobile . "'," .
				 "'" . $home .  "'," .
				 "'" . $other .  "'," .
				 "'" . $website . "'," .
				 "'" . $email . "'," .
				 "'" . $street .  "'," .
				 "'" . $city .  "'," .
				 "'" . $state . "'," .
				 "'" . $zip . "'" .
				 ");";
	}
	if (!mysql_query($query, $database))
	{
		echo "Error: " . mysql_error() . "<br >\n";
		echo $query . "<br >\n";
		//die('Error: ' . mysql_error());
	}
	else
	{
		echo "Contact information updated.<br >\n";
	}
}

//edit or view contact information
if (($_POST["action"] == "edit") || ($contact != 0))
{
	$value = $contact;
	if (is_numeric($value) == FALSE)
		$value = 0;

	if ($value != 0)
	{	//display existing contact
		$query = "SELECT * FROM contacts WHERE emp_id = " . $value;
		$results = mysql_query($query, $database);
		if($row = mysql_fetch_array($results))
		{
			if ($_POST["action"] != "edit")
			{	//viewing profile
				echo "<h3>Viewing Profile for: ";
			}
			else
			{	//editing information
				echo "<h3>Editing Details for: ";
			}

			print_contact($value, $database);
			echo "</h3>\n";
			echo "<a href=\"" . bottomPageURL() . "/payments.php?contact=" . $value . "\">View payments</a><br>\n";
			echo "<a href=\"" . bottomPageURL() . "/inspections.php?contact=" . $value . "\">View inspections</a><br>\n";

			echo "<a href=\"" . bottomPageURL() . "/contacts.php\"> " . " Back to all contacts</a><br >\n\n";

			echo "	<form action=\"" . bottomPageURL() . "/payments.php\" method=\"post\">\n" .
				 "		<input type=\"hidden\" name=\"action\" value=\"edit\">\n" .
				 "		<input type=\"hidden\" name=\"id\" value=\"0\">\n" .
				 "		<input type=\"hidden\" name=\"payee\" value=\"" . $row['emp_id'] . "\">\n" .
				 "		<input type=\"submit\" value=\"This contact made a payment\"/>\n" .
				 "	</form>\n";
			echo "	<form action=\"" . bottomPageURL() . "/payments.php\" method=\"post\">\n" .
				 "		<input type=\"hidden\" name=\"action\" value=\"edit\">\n" .
				 "		<input type=\"hidden\" name=\"id\" value=\"0\">\n" .
				 "		<input type=\"hidden\" name=\"payer\" value=\"" . $row['emp_id'] . "\">\n" .
				 "		<input type=\"submit\" value=\"This contact was paid\"/>\n" .
				 "	</form>\n";

			if ($_POST["action"] != "edit")
			{	//viewing profile
				print_contact($contact, $database);
				if ($row['website'] != "")
				{
					echo " : Visit their website by ";
					echo " <a href=\"" . $row['website'] . "\" target=\"_blank\">Clicking Here" . "</a>";
				}
				echo "<br>\n";
				echo $row['address'];
				if ($row['city'] != "")
				{
					echo "<br>\n" . $row['city'];
				}
				if ($row['state'] != "")
				{
					echo ", " . $row['state'];
				}
				if ($row['zipcode'] != "")
				{
					echo " " . $row['zipcode'];
				}
				echo "<br >\n";
				echo "TODO: Add preferred method of contact<br>\n";
				if ($row['phone_mobile'] != "")
				{
					echo "Mobile: " . $row['phone_mobile'] . "<br>\n";
				}
				if ($row['phone_home'] != "")
				{
					echo "Home/Office: " . $row['phone_home'] . "<br>\n";
				}
				if ($row['phone_other'] != "")
				{
					echo "Other: " . $row['phone_other'] . "<br>\n";
				}
				if ($row['email'] != "")
				{
					echo "Contact via e-mail at " . $row['email'] . 
						' <a href="mailto:' . $row['email'] . '">e-mail</a>' . "<br>\n";
				}
				
				echo "This " . $row['classification'] . " is ";
				if ($row['payment_eligible'] == 0)
					echo "<b>NOT</b> ";
				echo "eligible to be paid<br>\n";
				
				echo "Soon to print payment information<br>\n";
				echo "Soon to print inspections performed (if applicable)<br>\n";
			}
			else
			{	//editing information
				contact_form($row['emp_id'], $row['last_name'], $row['first_name'], $row['classification'],
					$row['payment_eligible'], $row['ssn'], $row['phone_mobile'], $row['phone_home'], $row['phone_other'],
					$row['website'], $row['email'], $row['address'], $row['city'], $row['state'], $row['zipcode']);
			}
			
		}
		else
		{
			echo "<h3>Invalid contact id number</h3>\n";
		}
	}
	else
	{	//create new contact
		echo "<h3>Creating new contact:</h3>\n<a href=\"" . bottomPageURL() . "/contacts.php\"> " . " Back to all contacts</a><br >\n\n";
		contact_form(0, '', '', '',
			'', '', '', '', '',
			'', '', '', '', '', '');
	}
}
else
{	//display all contacts
	if ($contact != 0)
	{
		$query = "SELECT * FROM contacts WHERE emp_id = " . $contact . " ORDER BY last_name ASC LIMIT " . ($start_page*30) . ", " . ($start_page*30+30);
	}
	else
	{
		$query = "SELECT * FROM contacts ORDER BY last_name ASC LIMIT " . ($start_page*30) . ", " . ($start_page*30+30);
	}
	$contact_results = mysql_query($query, $database);

	echo "<table border=\"1\">\n";
	echo "	<tr>\n";
	echo "		<th>Options</th>\n";
	echo "		<th>Last name</th>\n";
	echo "		<th>First name</th>\n";
	echo "		<th>Classification</th>\n";
	echo "		<th>Phone(mobile)</th>\n";
	echo "		<th>Phone(home)</th>\n";
	echo "		<th>Can be paid?</th>\n";
	echo "	</tr>\n";

	while($row = mysql_fetch_array($contact_results))
	{
		echo "	<tr>\n";

		echo "		<td>\n";
					 
		echo "			<a href=\"". bottomPageURL() . "/contacts.php?contact=" . $row['emp_id'] . "\">View</a>\n";
		echo "		</td>\n";

		echo "		<td>";

		if ($row['website'] != "")
		{
			echo " <a href=\"" . $row['website'] . "\" target=\"_blank\">" . $row['last_name'] . "</a> </td>\n";
		}
		else
		{
			echo $row['last_name'] . "</td>\n";
		}
		echo "		<td>" . $row['first_name'] . "</td>\n";
		echo "		<td>" . $row['classification'] . "</td>\n";
		echo "		<td>" . $row['phone_mobile'] . "</td>\n";
		echo "		<td>" . $row['phone_home'] . "</td>\n";

		if ($row['payment_eligible'] == 0)
		{
			echo "		<td>No</td>\n";
		}
		else
		{
			echo "		<td>Yes</td>\n";
		}

		echo "	</tr>\n";
	}

	echo "</table><br>\n";

	if ($start_page > 0)
		echo '<a href="' . bottomPageURL() . '/contacts.php?page=' . ($start_page-1) . '">Previous page</a>  ';
	echo '<a href="/contacts.php?page=' . ($start_page+1) . '">Next page</a>' . "<br >\n";

	echo "			<form action=\"" . bottomPageURL() . "/contacts.php\" method=\"post\">\n" .
		 "				<input type=\"hidden\" name=\"action\" value=\"edit\">\n" .
		 "				<input type=\"hidden\" name=\"id\" value=\"0\">\n" .
		 "				<input type=\"submit\" value=\"New contact\"/>\n" .
		 "			</form>";
}

closeDatabase($database);

?>

</body>
</html>
