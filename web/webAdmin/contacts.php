<?php
namespace webAdmin;
#TODO : add capability to delete password of a user

class contacts
{
	private $config;
	private $contact;
	private $mysql_db;

	function __construct($config)
	{
		$this->contact = 0;	//TODO fix this?
	}
	
	public function table()
	{
		global $mysql_db;
		$start_page = 0;	//TODO: fix this
		$uid = $_SESSION['user']['emp_id'];
		$query = "SELECT * FROM contacts, contact_permission WHERE " .
				 "(((id2 = " . $uid . ") OR (id2 IS NULL)) AND " . 
				 "((id1 = emp_id) OR (id1 IS NULL)) AND ".
				 "(permission LIKE '%r%'))" .
				 " ORDER BY last_name ASC LIMIT " . 
				 ($start_page*30) . ", " . ($start_page*30+30);
		$contact_results = $mysql_db->query($query);
	
		echo "<table border=\"1\">\n";
		echo "	<tr>\n";
		echo "		<th>Options</th>\n";
		echo "		<th>Name</th>\n";
		echo "		<th>Classification</th>\n";
		echo "		<th>Phone</th>\n";
		echo "	</tr>\n";
	
		if ($contact_results && ($contact_results->num_rows > 0))
		{
			while($row = $contact_results->fetch_array(MYSQLI_BOTH))
			{
				echo "	<tr>\n";
		
				echo "		<td>" . "<a href=\"". rootPageURL($this->config) . 
					 "/contacts.php?contact=" . $row['emp_id'] . 
					 "\">View</a>";

				$uid = $_SESSION['user']['emp_id'];
				$allow = check_permission("contact_permission", $uid, $row['emp_id'], "%p%");
				if (check_specific_permission($allow, "global") == "yes")
				{
					if (is_null($row['password']))
					{
						echo "\n		<form action=\"" . rootPageURL($this->config) . "/contacts.php\" method=\"post\">\n" .
							 "			<input type=\"hidden\" name=\"action\" value=\"cpass\">\n" .
							 "			<input type=\"hidden\" name=\"id\" value=\"" . $row['emp_id'] . "\">\n" .
							 "			<input class=\"buttons\" type=\"submit\" value=\"Init Password\"/>\n" .
							 "		</form>";
					}
					else
					{
						echo "\n		<form action=\"" . rootPageURL($this->config) . "/contacts.php\" method=\"post\">\n" .
							 "			<input type=\"hidden\" name=\"action\" value=\"epass\">\n" .
							 "			<input type=\"hidden\" name=\"id\" value=\"" . $row['emp_id'] . "\">\n" .
							 "			<input class=\"buttons\" type=\"submit\" value=\"Edit Password\"/>\n" .
							 "		</form>";
					}
				}
		
				echo "</td>\n		<td>";

				if ($this->config['last_name_first'] == 1)
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
		}
	
		echo "</table><br>\n";
		
		if ($contact_results && ($contact_results->num_rows > 30))
		{
			$next_page = 1;
		}
		else
		{
			$next_page = 0;
		}
	
		if ($start_page > 0)
		{
			echo '<a href="' . rootPageURL($this->config) . '/contacts.php?page=' . ($start_page-1) . '">Previous page</a>  ';
		}
		if ($next_page == 1)
		{
			echo '<a href="/contacts.php?page=' . ($start_page+1) . '">Next page</a>' . "<br >\n";
		}
	
		echo "			<form action=\"" . rootPageURL($this->config) . "/contacts.php\" method=\"post\">\n" .
			 "				<input type=\"hidden\" name=\"action\" value=\"create\">\n" .
			 "				<input type=\"hidden\" name=\"id\" value=\"0\">\n" .
			 "				<input class=\"buttons\" type=\"submit\" value=\"New contact\"/>\n" .
			 "			</form>";
	}
	
	public static function give_me_pword($newpass)
	{
		//make a new salt
		$salt = generate_salt();
		//value in config file used when creating or storing passwords
		$hash_pass = hash_password($newpass, $salt, $this->config['key_stretching_value']);
		echo $hash_pass . "<br >\n";
		echo $salt . "<br >\n";
		echo $this->config['key_stretching_value'] . "<br >\n";
	}
		
	
	public static function create_contact($username, $email)
	{
		global $mysql_db;
		$query = "INSERT INTO `contacts` (username, email)" .
							" VALUES ('" . $username . "', '" . $email . "');";
		$results = $mysql_db->query($query);
	}
	
	public static function get_id_num($username)
	{
		global $mysql_db;
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
			echo "<form action=\"" . rootPageURL($this->config) . "/contacts.php\" method=\"post\">\n" .
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

			echo "<form action=\"" . rootPageURL($this->config) . "/contacts.php\" method=\"post\">\n" .
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
