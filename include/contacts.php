<?php

class contacts
{
	protected $contact_list;	//holds the list of contact information
	public $contact;
	protected $start_page;

	function __construct()
	{
		//load the page number
		if (!(array_key_exists("page", $_GET)))
		{
			$this->start_page = 0;
		}
		else
		{
			$this->start_page = $_GET["page"];
			if (is_numeric($this->start_page) == FALSE)
				$this->start_page = 0;
		}

		if (!(array_key_exists("contact", $_GET)))
		{
			$this->contact = 0;
		}
		else
		{
			$this->contact = $_GET["contact"];
			if (is_numeric($this->contact) == FALSE)
				$this->contact = 0;
		}
	}
	
	public function single()
	{
		global $mysql_db;
		$query = "SELECT * FROM contacts WHERE emp_id = " . $this->contact;
		$results = $mysql_db->query($query);
		if($row = $results->fetch_array(MYSQLI_BOTH))
		{
			if ($_POST["action"] != "edit")
			{	//viewing profile
				echo "<h3>Viewing Profile for: ";
			}
			else
			{	//editing information
				echo "<h3>Editing Details for: ";
			}

			echo print_contact($this->contact);
			echo "</h3>\n";
			echo "<a href=\"" . rootPageURL() . "/payments.php?contact=" . $this->contact . "\">View payments</a><br>\n";
			echo "<a href=\"" . rootPageURL() . "/inspections.php?contact=" . $this->contact . "\">View inspections</a><br>\n";

			echo "<a href=\"" . rootPageURL() . "/contacts.php\"> " . " Back to all contacts</a><br >\n\n";

			echo "	<form action=\"" . rootPageURL() . "/payments.php\" method=\"post\">\n" .
				 "		<input type=\"hidden\" name=\"action\" value=\"edit\">\n" .
				 "		<input type=\"hidden\" name=\"id\" value=\"0\">\n" .
				 "		<input type=\"hidden\" name=\"payee\" value=\"" . $row['emp_id'] . "\">\n" .
				 "		<input type=\"submit\" value=\"This contact made a payment\"/>\n" .
				 "	</form>\n";
			echo "	<form action=\"" . rootPageURL() . "/payments.php\" method=\"post\">\n" .
				 "		<input type=\"hidden\" name=\"action\" value=\"edit\">\n" .
				 "		<input type=\"hidden\" name=\"id\" value=\"0\">\n" .
				 "		<input type=\"hidden\" name=\"payer\" value=\"" . $row['emp_id'] . "\">\n" .
				 "		<input type=\"submit\" value=\"This contact was paid\"/>\n" .
				 "	</form>\n";

			if ($_POST["action"] != "edit")
			{	//viewing profile
				echo print_contact($this->contact);
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
				$this->make_form($row['emp_id'], $row['last_name'], $row['first_name'], $row['classification'],
					$row['payment_eligible'], $row['ssn'], $row['phone_mobile'], $row['phone_home'], $row['phone_other'],
					$row['website'], $row['email'], $row['address'], $row['city'], $row['state'], $row['zipcode']);
			}
			else
			{	//editing information
				$this->make_form($row['emp_id'], $row['last_name'], $row['first_name'], $row['classification'],
					$row['payment_eligible'], $row['ssn'], $row['phone_mobile'], $row['phone_home'], $row['phone_other'],
					$row['website'], $row['email'], $row['address'], $row['city'], $row['state'], $row['zipcode']);
			}
			
		}
		else
		{
			echo "<h3>Invalid contact id number</h3>\n";
		}
	}
	
	public function make_form($id, $last_name, $first_name, $classify, $eligible, $ssn, $mobile, $home, $other,
		$website, $email, $street, $city, $state, $zip)
	{	//TODO: implement drop down box with a yes/no
		echo "<b> If a customer wants to be contacted about a job, contact information must be entered here</b><br >\n";
		if (($_POST["action"] == "view") || ($_POST["action"] == ""))
		{
			echo "	<form action=\"contacts.php?contact=" . $id . "\" method=\"post\">\n" .
				 "		<input type=\"hidden\" name=\"action\" value=\"edit\">\n" .
				 "		<input type=\"submit\" value=\"Edit\"/>\n" .
				 "	</form>\n";
			
			echo "<form action=\"contacts.php?contact=" . $id . "\" method=\"post\">\n" .
				 "	<input type=\"hidden\" name=\"action\" value=\"edit\"><br>\n";
					
			echo "	<table border=\"1\" width=\"50%\">\n";
		}
		else
		{
			echo "<form action=\"contacts.php?contact=" . $id . "\" method=\"post\">\n" .
				 "	<input type=\"hidden\" name=\"action\" value=\"update\"><br>\n";
			echo "	<table border=\"1\" width=\"50%\">\n";
			if ($id != 0)
			{
				echo "	<input type=\"submit\" value=\"Update\"/>\n";
			}
			else
			{
				echo "	<input type=\"submit\" value=\"Insert\"/>\n";
			}
		}
			
		if ($id != 0)
		{	
			echo "		<tr>\n";
			echo "			<td>Id number</td>\n";
			echo "			<td>";
			echo "<input type=\"text\" name=\"id\" value=\"" . $id . "\" size=\"70\" disabled >";
			echo "</td>\n";
			echo "		</tr>\n";
		}
					
		echo "		<tr>\n";
		echo "			<td>Last Name</td>\n";
		echo "			<td>";
		echo "<input type=\"text\" name=\"last_name\" value=\"" . $last_name . "\" size=\"70\"";
		if (($_POST["action"] == "view") || ($_POST["action"] == ""))
			echo " disabled";
		echo " >";
		echo "</td>\n";
		echo "		</tr>\n";
					
		echo "		<tr>\n";
		echo "			<td>First Name</td>\n";
		echo "			<td>";
		echo "<input type=\"text\" name=\"first_name\" value=\"" . $first_name . "\" size=\"70\"";
		if (($_POST["action"] == "view") || ($_POST["action"] == ""))
			echo " disabled";
		echo " >";
		echo "</td>\n";
		echo "		</tr>\n";
					
		echo "		<tr>\n";
		echo "			<td>Classification</td>\n";
		echo "			<td>";
		echo "<input type=\"text\" name=\"classify\" value=\"" . $classify . "\" size=\"70\"";
		if (($_POST["action"] == "view") || ($_POST["action"] == ""))
			echo " disabled";
		echo " >";
		echo "</td>\n";
		echo "		</tr>\n";
					
		echo "		<tr>\n";
		echo "			<td>Eligible for payment</td>\n";
		echo "			<td>";
		echo "<input type=\"text\" name=\"eligible\" value=\"" . $eligible . "\" size=\"70\"";
		if (($_POST["action"] == "view") || ($_POST["action"] == ""))
			echo " disabled";
		echo " >";
		echo "</td>\n";
		echo "		</tr>\n";
					
		echo "		<tr>\n";
		echo "			<td>SSN</td>\n";
		echo "			<td>";
		echo "<input type=\"text\" name=\"ssn\" value=\"" . $ssn . "\" size=\"70\"";
		if (($_POST["action"] == "view") || ($_POST["action"] == ""))
			echo " disabled";
		echo " >";
		echo "</td>\n";
		echo "		</tr>\n";
					
		echo "		<tr>\n";
		echo "			<td>Phone(mobile)</td>\n";
		echo "			<td>";
		echo "<input type=\"text\" name=\"mobile\" value=\"" . $mobile . "\" size=\"70\"";
		if (($_POST["action"] == "view") || ($_POST["action"] == ""))
			echo " disabled";
		echo " >";
		echo "</td>\n";
		echo "		</tr>\n";
					
		echo "		<tr>\n";
		echo "			<td>Phone(home)</td>\n";
		echo "			<td>";
		echo "<input type=\"text\" name=\"home\" value=\"" . $home . "\" size=\"70\"";
		if (($_POST["action"] == "view") || ($_POST["action"] == ""))
			echo " disabled";
		echo " >";
		echo "</td>\n";
		echo "		</tr>\n";
					
		echo "		<tr>\n";
		echo "			<td>Phone(other)</td>\n";
		echo "			<td>";
		echo "<input type=\"text\" name=\"other\" value=\"" . $other . "\" size=\"70\"";
		if (($_POST["action"] == "view") || ($_POST["action"] == ""))
			echo " disabled";
		echo " >";
		echo "</td>\n";
		echo "		</tr>\n";
					
		echo "		<tr>\n";
		echo "			<td>Website</td>\n";
		echo "			<td>";
		echo "<input type=\"text\" name=\"website\" value=\"" . $website . "\" size=\"70\"";
		if (($_POST["action"] == "view") || ($_POST["action"] == ""))
			echo " disabled";
		echo " >";
		echo "</td>\n";
		echo "		</tr>\n";
					
		echo "		<tr>\n";
		echo "			<td>E-mail address</td>\n";
		echo "			<td>";
		echo "<input type=\"text\" name=\"email\" value=\"" . $email . "\" size=\"70\"";
		if (($_POST["action"] == "view") || ($_POST["action"] == ""))
			echo " disabled";
		echo " >";
		echo "</td>\n";
		echo "		</tr>\n";
					
		echo "		<tr>\n";
		echo "			<td>Street Address</td>\n";
		echo "			<td>";
		echo "<input type=\"text\" name=\"street\" value=\"" . $street . "\" size=\"70\"";
		if (($_POST["action"] == "view") || ($_POST["action"] == ""))
			echo " disabled";
		echo " >";
		echo "</td>\n";
		echo "		</tr>\n";
					
		echo "		<tr>\n";
		echo "			<td>City</td>\n";
		echo "			<td>";
		echo "<input type=\"text\" name=\"city\" value=\"" . $city . "\" size=\"70\"";
		if (($_POST["action"] == "view") || ($_POST["action"] == ""))
			echo " disabled";
		echo " >";
		echo "</td>\n";
		echo "		</tr>\n";
					
		echo "		<tr>\n";
		echo "			<td>State</td>\n";
		echo "			<td>";
		echo "<input type=\"text\" name=\"state\" value=\"" . $state . "\" size=\"70\"";
		if (($_POST["action"] == "view") || ($_POST["action"] == ""))
			echo " disabled";
		echo " >";
		echo "</td>\n";
		echo "		</tr>\n";
					
		echo "		<tr>\n";
		echo "			<td>Zipcode</td>\n";
		echo "			<td>";
		echo "<input type=\"text\" name=\"zip\" value=\"" . $zip . "\" size=\"70\"";
		if (($_POST["action"] == "view") || ($_POST["action"] == ""))
			echo " disabled";
		echo " >";
		echo "</td>\n";
		echo "		</tr>\n";
		echo "	</table>\n";
	
		if (($_POST["action"] == "view") || ($_POST["action"] == ""))
		{
			echo "</form>\n" . 
				 "<form action=\"contacts.php?contact=" . $id . "\" method=\"post\">\n" .
				 "	<input type=\"hidden\" name=\"action\" value=\"edit\">\n" .
				 "	<input type=\"submit\" value=\"Edit\"/>\n" .
				 "</form>\n";
		}
		else
		{
			if ($id != 0)
			{
				echo "	<input type=\"submit\" value=\"Update\"/>\n" .
					 "</form>\n";
			}
			else
			{
				echo "	<input type=\"submit\" value=\"Insert\"/>\n" .
					 "</form>\n";
			}
		}
	}
	
	public function update($withdata)
	{
		global $mysql_db;
		$id_num = $this->contact;
		if (is_numeric($id_num) == FALSE)
			$id_num = 0;
		$last_name = $mysql_db->real_escape_string($withdata["last_name"]);
		$first_name = $mysql_db->real_escape_string($withdata["first_name"]);
		$classification = $mysql_db->real_escape_string($withdata["classify"]);
		$eligibility = $withdata["eligible"];
		if (is_numeric($eligibility) == FALSE)
			$eligibility = 0;
		$ssn = $mysql_db->real_escape_string($withdata["ssn"]);
		$mobile = $mysql_db->real_escape_string($withdata["mobile"]);
		$home = $mysql_db->real_escape_string($withdata["home"]);
		$other = $mysql_db->real_escape_string($withdata["other"]);
		$website = $mysql_db->real_escape_string($withdata["website"]);
		$email = $mysql_db->real_escape_string($withdata["email"]);
		$street = $mysql_db->real_escape_string($withdata["street"]);
		$city = $mysql_db->real_escape_string($withdata["city"]);
		$state = $mysql_db->real_escape_string($withdata["state"]);
		$zip = $mysql_db->real_escape_string($withdata["zip"]);
	
		if ($id_num != 0)
		{
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
		if (!$mysql_db->query($query))
		{
			echo "Error: " . $mysql_db->error() . "<br >\n";
			//die('Error: ' . $mysql_db->error());
		}
		else
		{
			echo "Contact information updated successfully.<br >\n";
		}
	}
	
	public function table()
	{
		global $mysql_db;
		$query = "SELECT * FROM contacts ORDER BY last_name ASC LIMIT " . ($this->start_page*30) . ", " . ($this->start_page*30+30);
		$contact_results = $mysql_db->query($query);
	
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
	
		while($row = $contact_results->fetch_array(MYSQLI_BOTH))
		{
			echo "	<tr>\n";
	
			echo "		<td>" . "<a href=\"". rootPageURL() . 
				 "/contacts.php?contact=" . $row['emp_id'] . 
				 "\">View</a></td>\n";
	
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
		
		if ($contact_results->num_rows > 30)
		{
			$next_page = 1;
		}
		else
		{
			$next_page = 0;
		}
	
		if ($this->start_page > 0)
			echo '<a href="' . rootPageURL() . '/contacts.php?page=' . ($this->start_page-1) . '">Previous page</a>  ';
		if ($next_page == 1)
			echo '<a href="/contacts.php?page=' . ($this->start_page+1) . '">Next page</a>' . "<br >\n";
	
		echo "			<form action=\"" . rootPageURL() . "/contacts.php\" method=\"post\">\n" .
			 "				<input type=\"hidden\" name=\"action\" value=\"create\">\n" .
			 "				<input type=\"hidden\" name=\"id\" value=\"0\">\n" .
			 "				<input type=\"submit\" value=\"New contact\"/>\n" .
			 "			</form>";
	}
}


?>
