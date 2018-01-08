<?php

#TODO : add capability to delete password of a user

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
		$uid = $_SESSION['user']['emp_id'];
		
		$allow = check_permission("contact_permission", $uid, $this->contact, "%w%");
		if ($allow[0][1] == "none")
		{
			$_POST["action"] = "";
		}
		
		$query = "SELECT * FROM contacts, contact_permission WHERE " .
				 "(((id2 = " . $uid . ") OR (id2 IS NULL)) AND " .
				 "((id1 = emp_id) OR (id1 IS NULL)) AND " .
				 "(emp_id = " . $this->contact . ") AND " .
				 "(permission LIKE '%r%')) LIMIT 1;";
		$results = $mysql_db->query($query);
		if($row = $results->fetch_array(MYSQLI_BOTH))
		{
			if ($_POST["action"] == "")
			{	//viewing profile
				echo "<h3>Viewing Profile for: ";
			}
			else if ($_POST["action"] == "edit")
			{	//editing information
				echo "<h3>Editing Details for: ";
			}
			else if ($_POST["action"] == "view")
			{
				echo "<h3>Viewing Profile for: ";
				$_POST["action"] = "";
			}

			echo print_contact($this->contact);
			echo "</h3>\n";
			echo "<a href=\"" . rootPageURL() . "/payments.php?contact=" . $this->contact . "\">View payments</a><br>\n";

			echo "	<form action=\"" . rootPageURL() . "/payments.php\" method=\"post\">\n" .
				 "		<input type=\"hidden\" name=\"action\" value=\"edit\">\n" .
				 "		<input type=\"hidden\" name=\"id\" value=\"0\">\n" .
				 "		<input type=\"hidden\" name=\"payer\" value=\"" . $row['emp_id'] . "\">\n" .
				 "		<input class=\"buttons\" type=\"submit\" value=\"This contact made a payment\"/>\n" .
				 "	</form>\n";
			echo "	<form action=\"" . rootPageURL() . "/payments.php\" method=\"post\">\n" .
				 "		<input type=\"hidden\" name=\"action\" value=\"edit\">\n" .
				 "		<input type=\"hidden\" name=\"id\" value=\"0\">\n" .
				 "		<input type=\"hidden\" name=\"payee\" value=\"" . $row['emp_id'] . "\">\n" .
				 "		<input class=\"buttons\" type=\"submit\" value=\"This contact was paid\"/>\n" .
				 "	</form>\n";

			if ($_POST["action"] != "edit")
			{	//viewing profile
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
				{
					echo "<b>NOT</b> ";
				}
				echo "eligible to be paid<br>\n";
				
				echo "Soon to print payment information<br>\n";
				$this->make_form($row['emp_id'], $row['last_name'], $row['first_name'], $row['classification'],
					$row['payment_eligible'], $row['phone_mobile'], $row['phone_home'], $row['phone_other'],
					$row['website'], $row['email'], $row['address'], $row['city'], $row['state'], $row['zipcode'],
					$row['username']);
			}
			else
			{	//editing information
				$this->make_form($row['emp_id'], $row['last_name'], $row['first_name'], $row['classification'],
					$row['payment_eligible'], $row['phone_mobile'], $row['phone_home'], $row['phone_other'],
					$row['website'], $row['email'], $row['address'], $row['city'], $row['state'], $row['zipcode'],
					$row['username']);
			}
			
		}
		else
		{
			echo "<h3>Invalid contact id number</h3>\n";
		}
	}
	
	public function make_form($id, $last_name, $first_name, $classify, $eligible, $mobile, $home, $other,
		$website, $email, $street, $city, $state, $zip, $username)
	{	//TODO: implement drop down box with a yes/no
		if ($mobile == "")
			$mobile = "&nbsp;";
		if ($home == "")
			$home = "&nbsp;";
		if ($other == "")
			$other = "&nbsp;";
		$uid = $_SESSION['user']['emp_id'];
		$allow = check_permission("contact_permission", $uid, $id, "%w%");
		echo "<b> If a customer wants to be contacted about a job, contact information must be entered here</b><br >\n";
		if ($_POST["action"] == "")
		{
			if ($allow[0][1] != "none")
			{
				echo "	<form action=\"contacts.php?contact=" . $id . "\" method=\"post\">\n" .
					 "		<input type=\"hidden\" name=\"action\" value=\"edit\">\n" .
					 "		<input class=\"buttons\" type=\"submit\" value=\"Edit\"/>\n" .
					 "	</form>\n";
			}
			
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
				echo "	<input class=\"buttons\" type=\"submit\" value=\"Update\"/>\n";
			}
			else
			{
				echo "	<input class=\"buttons\" type=\"submit\" value=\"Insert\"/>\n";
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
		if ($_POST["action"] == "")
		{
			echo " disabled";
		}
		echo " >";
		echo "</td>\n";
		echo "		</tr>\n";
					
		echo "		<tr>\n";
		echo "			<td>First Name</td>\n";
		echo "			<td>";
		echo "<input type=\"text\" name=\"first_name\" value=\"" . $first_name . "\" size=\"70\"";
		if ($_POST["action"] == "")
		{
			echo " disabled";
		}
		echo " >";
		echo "</td>\n";
		echo "		</tr>\n";
		
		echo "		<tr>\n";
		echo "			<td>Username</td>\n";
		echo "			<td>";
		echo "<input type=\"text\" name=\"username\" value=\"" . $username . "\" size=\"70\"";
		if ($_POST["action"] == "")
		{
			echo " disabled";
		}
		echo " >";
		echo "</td>\n";
		echo "		</tr>\n";
					
		echo "		<tr>\n";
		echo "			<td>Classification</td>\n";
		echo "			<td>";
		echo "<input type=\"text\" name=\"classify\" value=\"" . $classify . "\" size=\"70\"";
		if ($_POST["action"] == "")
		{
			echo " disabled";
		}
		echo " >";
		echo "</td>\n";
		echo "		</tr>\n";
					
		echo "		<tr>\n";
		echo "			<td>Eligible for payment</td>\n";
		echo "			<td>";
		echo "<input type=\"text\" name=\"eligible\" value=\"" . $eligible . "\" size=\"70\"";
		if ($_POST["action"] == "")
		{
			echo " disabled";
		}
		echo " >";
		echo "</td>\n";
		echo "		</tr>\n";
					
		echo "		<tr>\n";
		echo "			<td>Phone(mobile)</td>\n";
		echo "			<td>";
		echo "<input type=\"text\" name=\"mobile\" value=\"" . $mobile . "\" size=\"70\"";
		if ($_POST["action"] == "")
		{
			echo " disabled";
		}
		echo " >";
		echo "</td>\n";
		echo "		</tr>\n";
					
		echo "		<tr>\n";
		echo "			<td>Phone(home)</td>\n";
		echo "			<td>";
		echo "<input type=\"text\" name=\"home\" value=\"" . $home . "\" size=\"70\"";
		if ($_POST["action"] == "")
		{
			echo " disabled";
		}
		echo " >";
		echo "</td>\n";
		echo "		</tr>\n";
					
		echo "		<tr>\n";
		echo "			<td>Phone(other)</td>\n";
		echo "			<td>";
		echo "<input type=\"text\" name=\"other\" value=\"" . $other . "\" size=\"70\"";
		if ($_POST["action"] == "")
		{
			echo " disabled";
		}
		echo " >";
		echo "</td>\n";
		echo "		</tr>\n";
					
		echo "		<tr>\n";
		echo "			<td>Website</td>\n";
		echo "			<td>";
		echo "<input type=\"text\" name=\"website\" value=\"" . $website . "\" size=\"70\"";
		if ($_POST["action"] == "")
		{
			echo " disabled";
		}
		echo " >";
		echo "</td>\n";
		echo "		</tr>\n";
					
		echo "		<tr>\n";
		echo "			<td>E-mail address</td>\n";
		echo "			<td>";
		echo "<input type=\"text\" name=\"email\" value=\"" . $email . "\" size=\"70\"";
		if ($_POST["action"] == "")
		{
			echo " disabled";
		}
		echo " >";
		echo "</td>\n";
		echo "		</tr>\n";
					
		echo "		<tr>\n";
		echo "			<td>Street Address</td>\n";
		echo "			<td>";
		echo "<input type=\"text\" name=\"street\" value=\"" . $street . "\" size=\"70\"";
		if ($_POST["action"] == "")
		{
			echo " disabled";
		}
		echo " >";
		echo "</td>\n";
		echo "		</tr>\n";
					
		echo "		<tr>\n";
		echo "			<td>City</td>\n";
		echo "			<td>";
		echo "<input type=\"text\" name=\"city\" value=\"" . $city . "\" size=\"70\"";
		if ($_POST["action"] == "")
		{
			echo " disabled";
		}
		echo " >";
		echo "</td>\n";
		echo "		</tr>\n";
					
		echo "		<tr>\n";
		echo "			<td>State</td>\n";
		echo "			<td>";
		echo "<input type=\"text\" name=\"state\" value=\"" . $state . "\" size=\"70\"";
		if ($_POST["action"] == "")
		{
			echo " disabled";
		}
		echo " >";
		echo "</td>\n";
		echo "		</tr>\n";
					
		echo "		<tr>\n";
		echo "			<td>Zipcode</td>\n";
		echo "			<td>";
		echo "<input type=\"text\" name=\"zip\" value=\"" . $zip . "\" size=\"70\"";
		if ($_POST["action"] == "")
		{
			echo " disabled";
		}
		echo " >";
		echo "</td>\n";
		echo "		</tr>\n";
		echo "	</table>\n";
	
		if ($_POST["action"] == "")
		{
			if ($allow[0][1] != "none")
			{
				echo "</form>\n" . 
					 "<form action=\"contacts.php?contact=" . $id . "\" method=\"post\">\n" .
					 "	<input type=\"hidden\" name=\"action\" value=\"edit\">\n" .
					 "	<input class=\"buttons\" type=\"submit\" value=\"Edit\"/>\n" .
					 "</form>\n";
			}
		}
		else
		{
			if ($id != 0)
			{
				echo "	<input class=\"buttons\" type=\"submit\" value=\"Update\"/>\n" .
					 "</form>\n";
			}
			else
			{
				echo "	<input class=\"buttons\" type=\"submit\" value=\"Insert\"/>\n" .
					 "</form>\n";
			}
		}
	}
	
	public function update($withdata)
	{
		global $mysql_db;
		$id_num = $this->contact;
		if (is_numeric($id_num) == FALSE)
		{
			$id_num = 0;
		}
		$last_name = $mysql_db->real_escape_string($withdata["last_name"]);
		$first_name = $mysql_db->real_escape_string($withdata["first_name"]);
		$username = $mysql_db->real_escape_string($withdata["username"]);
		$classification = $mysql_db->real_escape_string($withdata["classify"]);
		$eligibility = $withdata["eligible"];
		if (is_numeric($eligibility) == FALSE)
		{
			$eligibility = 0;
		}
		$mobile = $mysql_db->real_escape_string($withdata["mobile"]);
		$home = $mysql_db->real_escape_string($withdata["home"]);
		$other = $mysql_db->real_escape_string($withdata["other"]);
		$website = $mysql_db->real_escape_string($withdata["website"]);
		$email = $mysql_db->real_escape_string($withdata["email"]);
		$street = $mysql_db->real_escape_string($withdata["street"]);
		$city = $mysql_db->real_escape_string($withdata["city"]);
		$state = $mysql_db->real_escape_string($withdata["state"]);
		$zip = $mysql_db->real_escape_string($withdata["zip"]);

		$uid = $_SESSION['user']['emp_id'];
		$allowed_to_perform = 0;
		if ($id_num != 0)
		{
			$allow = check_permission("contact_permission", $uid, $id_num, "%w%");
			if ($allow[0][1] != "none")
			{
				$allowed_to_perform = 1;
			}
			
			$query = "UPDATE `contacts` SET " .
					"`last_name` = '" . $last_name .
					"', `first_name` = '" . $first_name .
					"', `username` = '" . $username .
					"', `classification` = '" . $classification .
					"', `payment_eligible` = " . $eligibility .
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
			$allowed_to_perform = 1;
			$query = "INSERT INTO `contacts` " .
					 "(last_name, first_name, username, classification, payment_eligible, " .
					 "phone_mobile, phone_home, phone_other, website, email, address, city, state, zipcode) " .
					 "VALUES (" .
					 "'" . $last_name .  "'," .
					 "'" . $first_name .  "'," .
					 "'" . $username . "', " .
					 "'" . $classification . "'," .
					 "'" . $eligibility .  "'," .
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
		if ($allowed_to_perform == 1)
		{
			if (!$mysql_db->query($query))
			{
				throw new Exception("Error: " . $mysql_db->error . "<br >\n");
			}
			else
			{
				if ($id_num == 0)
				{ 
					$new_id = $mysql_db->insert_id;
					mod_permission("contact_permission", $uid, $new_id, "+", 'r');
					mod_permission("contact_permission", $uid, $new_id, "+", 'w');
					mod_permission("contact_permission", $new_id, $new_id, "+", 'r');
					mod_permission("contact_permission", $new_id, $new_id, "+", 'w');
				}
				echo "Contact information updated successfully.<br >\n";
			}
		}
		else
		{
			echo "<b>You can't do that</b><br >\n";
		}
	}
	
	public function table()
	{
		global $mysql_db;
		global $config;
		
		$uid = $_SESSION['user']['emp_id'];
		$query = "SELECT * FROM contacts, contact_permission WHERE " .
				 "(((id2 = " . $uid . ") OR (id2 IS NULL)) AND " . 
				 "((id1 = emp_id) OR (id1 IS NULL)) AND ".
				 "(permission LIKE '%r%'))" .
				 " ORDER BY last_name ASC LIMIT " . 
				 ($this->start_page*30) . ", " . ($this->start_page*30+30);
		$contact_results = $mysql_db->query($query);
	
		echo "<table border=\"1\">\n";
		echo "	<tr>\n";
		echo "		<th>Options</th>\n";
		echo "		<th>Name</th>\n";
		echo "		<th>Classification</th>\n";
		echo "		<th>Phone</th>\n";
		echo "	</tr>\n";
	
		while($row = $contact_results->fetch_array(MYSQLI_BOTH))
		{
			echo "	<tr>\n";
	
			echo "		<td>" . "<a href=\"". rootPageURL() . 
				 "/contacts.php?contact=" . $row['emp_id'] . 
				 "\">View</a>";

			$uid = $_SESSION['user']['emp_id'];
			$allow = check_permission("contact_permission", $uid, $row['emp_id'], "%p%");
			if (check_specific_permission($allow, "global") == "yes")
			{
				if (is_null($row['password']))
				{
					echo "\n		<form action=\"" . rootPageURL() . "/contacts.php\" method=\"post\">\n" .
						 "			<input type=\"hidden\" name=\"action\" value=\"cpass\">\n" .
						 "			<input type=\"hidden\" name=\"id\" value=\"" . $row['emp_id'] . "\">\n" .
						 "			<input class=\"buttons\" type=\"submit\" value=\"Init Password\"/>\n" .
						 "		</form>";
				}
				else
				{
					echo "\n		<form action=\"" . rootPageURL() . "/contacts.php\" method=\"post\">\n" .
						 "			<input type=\"hidden\" name=\"action\" value=\"epass\">\n" .
						 "			<input type=\"hidden\" name=\"id\" value=\"" . $row['emp_id'] . "\">\n" .
						 "			<input class=\"buttons\" type=\"submit\" value=\"Edit Password\"/>\n" .
						 "		</form>";
				}
			}
	
			echo "</td>\n		<td>";

			if ($config['last_name_first'] == 1)
			{	
				$name_to_print = $row['last_name'] . ', ' . $row['first_name'];
			}
			else
			{
				$name_to_print = $row['first_name'] . ' ' . $row['last_name'];

			}
			if ($row['website'] != "")
			{
				echo " <a href=\"" . $row['website'] . "\" target=\"_blank\">" . $name_to_print . "</a> </td>\n";
			}
			else
			{
				echo $name_to_print . "</td>\n";
			}
			echo "		<td>" . $row['classification'] . "</td>\n";
			if ($row['phone_mobile'] != "") 
			{
				echo "		<td>" . $row['phone_mobile'] . "</td>\n";
			}
			else if ($row['phone_home'] != "")
			{
				echo "		<td>" . $row['phone_home'] . "</td>\n";
			}
			else
			{
				echo "		<td>&nbsp;</td>\n";
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
		{
			echo '<a href="' . rootPageURL() . '/contacts.php?page=' . ($this->start_page-1) . '">Previous page</a>  ';
		}
		if ($next_page == 1)
		{
			echo '<a href="/contacts.php?page=' . ($this->start_page+1) . '">Next page</a>' . "<br >\n";
		}
	
		echo "			<form action=\"" . rootPageURL() . "/contacts.php\" method=\"post\">\n" .
			 "				<input type=\"hidden\" name=\"action\" value=\"create\">\n" .
			 "				<input type=\"hidden\" name=\"id\" value=\"0\">\n" .
			 "				<input class=\"buttons\" type=\"submit\" value=\"New contact\"/>\n" .
			 "			</form>";
	}
	
	
	public static function give_me_pword($newpass)
	{
		global $config;
		
		//make a new salt
		$salt = generate_salt();
		//value in config file used when creating or storing passwords
		$hash_pass = hash_password($newpass, $salt, $config['key_stretching_value']);
		echo $hash_pass . "<br >\n";
		echo $salt . "<br >\n";
		echo $config['key_stretching_value'] . "<br >\n";
	}
	
	public static function setup_user_pword($uid, $newpass)
	{
		global $mysql_db, $config;
		
		$query = "SELECT fail_pass_change, username, password, " .
				 "salt FROM contacts WHERE emp_id = '" . 
				 $uid . "' LIMIT 1;";
		
		$results = $mysql_db->query($query);
		if ($results)
		{
			$row = $results->fetch_array(MYSQLI_BOTH);
			if ($row['fail_pass_change'] >= $config['max_fail_pass_changes'])
			{
				unset($_SESSION['username']);
				unset($_SESSION['password']);
			}
			else if (is_null($row['password']))
			{	//ok a password does not exist
				//make a new salt
				$salt = generate_salt();
		
				$query = "UPDATE contacts SET `salt` = '" . $salt . "' WHERE emp_id = " . $uid . ";";
				if ($mysql_db->query($query) == TRUE)
				{
					//echo "User salt stored successfully<br >\n";
					//value in config file used when creating or storing passwords
					$hash_pass = hash_password($newpass, $salt, $config['key_stretching_value']);
					$query = "UPDATE contacts SET `stretching` = '" . $config['key_stretching_value'] .
						"' WHERE emp_id = " . $uid . "; ";
					if ($mysql_db->query($query) == TRUE)
					{
						//echo "User stretching stored successfully<br >\n";
						$query = "UPDATE contacts SET `password` = '" . $hash_pass . "' WHERE emp_id = " . $uid . ";";
						if ($mysql_db->query($query) == TRUE)
						{
							//echo "User password stored successfully<br >\n";
						}
						else
						{
							echo "Failed to save user password<br >\n";
						}
					}
					else
					{
						echo "Failed to save user stretching<br >\n";
						echo $query . " 1 <br >\n";
					}
				}
				else
				{
					echo "Failed to save user salt<br >\n";
				}
			}
		}
	}
	
	public static function init_user_pword($uid, $newpass)
	{
		global $mysql_db, $config;
		
		$userid = $_SESSION['user']['emp_id'];
		$allow = check_permission("contact_permission", $userid, $uid, "%p%");
		if ($allow[0][1] == "none")
		{	//must have permission to initialize a users password
			echo "<b>You can't do that</b><br >\n";
			return;
		}
		
		$query = "SELECT fail_pass_change, username, password, " .
				 "salt FROM contacts WHERE emp_id = '" . 
				 $uid . "' LIMIT 1;";
		
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
			else if (is_null($row['password']))
			{	//ok a password does not exist
				//make a new salt
				$salt = generate_salt();
		
				$query = "UPDATE contacts SET `salt` = '" . $salt . "' WHERE emp_id = " . $uid . ";";
				if ($mysql_db->query($query) == TRUE)
				{
					//echo "User salt stored successfully<br >\n";
					//value in config file used when creating or storing passwords
					$hash_pass = hash_password($newpass, $salt, $config['key_stretching_value']);
					$query = "UPDATE contacts SET `stretching` = '" . $config['key_stretching_value'] .
						"' WHERE emp_id = " . $uid . "; ";
					if ($mysql_db->query($query) == TRUE)
					{
						//echo "User stretching stored successfully<br >\n";
						$query = "UPDATE contacts SET `password` = '" . $hash_pass . "' WHERE emp_id = " . $uid . ";";
						if ($mysql_db->query($query) == TRUE)
						{
							//echo "User password stored successfully<br >\n";
						}
						else
						{
							echo "Failed to save user password<br >\n";
						}
					}
					else
					{
						echo "Failed to save user stretching<br >\n";
						echo $query . " 1 <br >\n";
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
	
	public static function mod_user_pword($uid, $newpass)
	{
		global $mysql_db, $config;
		
		$userid = $_SESSION['user']['emp_id'];
		$allow = check_permission("contact_permission", $userid, $uid, "%p%");
		if ($allow[0][1] == "none")
		{	//must have permission to change a users password
			echo "<b>You can't do that</b><br >\n";
			return;
		}
		
		$query = "SELECT fail_pass_change, username, password, " .
				 "salt FROM contacts WHERE emp_id = '" . 
				 $uid . "' LIMIT 1;";
		
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
			else
			{
				$salt = generate_salt();
		
				$query = "UPDATE contacts SET `salt` = '" . $salt . "' WHERE emp_id = " . $uid . ";";
				if ($mysql_db->query($query) == TRUE)
				{
					echo "User salt stored successfully<br >\n";
					$hash_pass = hash_password($newpass, $salt, $config['key_stretching_value']);
					$query = "UPDATE contacts SET `stretching` = '" . $config['key_stretching_value'] .
						"' WHERE emp_id = " . $uid . "; ";
					echo "The query is " . $query . "<br >\n";
					if ($mysql_db->query($query) == TRUE)
					{
						echo "User stretching stored successfully<br >\n";
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
						echo "Failed to save user stretching<br >\n";
						echo $query . " 2 <br >\n";
					}
				}
				else
				{
					echo "Failed to save user salt<br >\n";
				}
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

	public static function store_user_pword($uid, $oldpass, $newpass)
	{
		global $mysql_db, $config;
		
		$userid = $_SESSION['user']['emp_id'];
		$allow = check_permission("contact_permission", $userid, $uid, "%p%");
		if ($allow[0][1] == "none")
		{
			echo "<b>You can't do that</b><br >\n";
			return;
		}
		
		$query = "SELECT fail_pass_change, username, password, " .
				 "salt, stretching FROM contacts WHERE emp_id = '" . 
				 $uid . "' LIMIT 1;";
		
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
			else if ($row['password'] == hash_password($oldpass, $row['salt'], $row['stretching']))
			{	//ok the old password matches
				$salt = generate_salt();
		
				$query = "UPDATE contacts SET `salt` = '" . $salt . "' WHERE emp_id = " . $uid . ";";
				if ($mysql_db->query($query) == TRUE)
				{
					echo "User salt stored successfully<br >\n";
					$hash_pass = hash_password($newpass, $salt, $config['key_stretching_value']);
					$query = "UPDATE contacts SET `stretching` = '" . $config['key_stretching_value'] .
						"' WHERE emp_id = " . $uid . "; ";
					if ($mysql_db->query($query) == TRUE)
					{
						echo "User stretching stored successfully<br >\n";
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
						echo "Failed to save user stretching<br >\n";
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
	
	public static function create_contact($username, $email)
	{
		global $mysql_db, $config;
		$query = "INSERT INTO `contacts` (username, email)" .
							" VALUES ('" . $username . "', '" . $email . "');";
		$results = $mysql_db->query($query);
	}
	
	public static function get_id_num($username)
	{
		global $mysql_db, $config;
		//check to see that the username does not exist first
		$query = "SELECT * FROM contacts WHERE username='" . $username . "' LIMIT 1;";
		$results = $mysql_db->query($query);
		if ($results)
		{
			if ($results->num_rows != 0)
			{
				$row = $results->fetch_array(MYSQLI_BOTH);
				return $row['emp_id'];	//username exists
			}
		}
		return 0;	//username does not exist
	}
	
	public static function does_user_exist($username)
	{
		global $mysql_db, $config;
		//check to see that the username does not exist first
		$query = "SELECT * FROM contacts WHERE username='" . $username . "' LIMIT 1;";
		$results = $mysql_db->query($query);
		if ($results)
		{
			if ($results->num_rows != 0)
			{
				return 1;	//username exists
			}
		}
		return 0;	//username does not exist
	}

	public function create_password($val)
	{	//creates a form to submit in order to create a password
		$userid = $_SESSION['user']['emp_id'];
		$allow = check_permission("contact_permission", $userid, $val, "%p%");
		if (check_specific_permission($allow, "global") == "no")
		{
			echo "<b>You can't do that</b><br >\n";
			return;
		}
		else
		{
			echo "<h3>Creating password for : " . print_contact($val) .
				"</h3>\n";
			echo "<form action=\"" . rootPageURL() . "/contacts.php\" method=\"post\">\n" .
				 "	<input type=\"hidden\" name=\"action\" value=\"apass\"><br>\n" .
				 "	<input type=\"hidden\" name=\"id\" value=\"" . $val . "\"><br>\n" .
				 "	Password: <input type=\"password\" name=\"pass2\" ><br>\n" .
				 "	Password again: <input type=\"password\" name=\"pass3\" ><br>\n" .
				 "	<input class=\"buttons\" type=\"submit\" value=\"Create the password\">\n" .
				 "</form>\n";
		}
	}
	
	public function edit_password($val)
	{	//creates a form to submit in order to change a password
		$userid = $_SESSION['user']['emp_id'];
		$allow = check_permission("contact_permission", $userid, $val, "%p%");
		if (check_specific_permission($allow, "global") == "no")
		{
			echo "<b>You can't do that</b><br >\n";
			return;
		}
		else
		{
			echo "<h3>Changing password for : " . print_contact($val) .
				"</h3>\n";

			echo "<form action=\"" . rootPageURL() . "/contacts.php\" method=\"post\">\n" .
				 "	<input type=\"hidden\" name=\"action\" value=\"apass\"><br>\n" .
				 "	<input type=\"hidden\" name=\"id\" value=\"" . $val . "\"><br>\n" .
				 "	New password: <input type=\"password\" name=\"pass2\" ><br>\n" .
				 "	New password again: <input type=\"password\" name=\"pass3\" ><br>\n" .
				 "	<input class=\"buttons\" type=\"submit\" value=\"Change the password\">\n" .
				 "</form>\n";
		}
	}
}

?>
