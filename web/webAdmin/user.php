<?php
namespace webAdmin;
class user
{
	private $config;
	private $mysql_db;
	private $table_name;
	
	public function __construct($config, $mysql_db, $table_name)
	{
		$this->config = $config;
		$this->mysql_db = $mysql_db;
		$this->table_name = $table_name;
	}
	
	public function login($quiet)
	{	//prints and executes code for the login script
		//returns exceptions
		#TODO : produce the div tags when quiet=1 and output is actually produced
		if ($quiet == 0)
		{
			//this div includes login, logout, and change password widgets
			echo "<div id=\"login_control\">\n";
		}

		//if https is not detected, then assume https is not being used
		if (!(array_key_exists("HTTPS", $_SERVER)))
		{
			$_SERVER["HTTPS"] = "off";
		}
		
		if (($_SERVER["HTTPS"] != "on") && ($this->config['require_https'] == 1))
		{
			throw new SiteConfigurationException("HTTPS is required and has not been detected");
		}

		//process POST data from attempted user registration
		if (($_POST["action"] == "create_user") && ($this->config['allow_user_create']=1))
		{
			if (isset($_POST["username"]))
			{
				$attempt_username = $this->mysql_db->real_escape_string($_POST["username"]);
				
				if (isset($_POST["email"]))
				{
					$attempt_email = $this->mysql_db->real_escape_string($_POST["email"]);
					if (isset($_POST["pass2"]))
					{
						$attempt_pass1 = $this->mysql_db->real_escape_string($_POST["pass2"]);
						if (isset($_POST["pass3"]))
						{
							$attempt_pass2 = $this->mysql_db->real_escape_string($_POST["pass3"]);
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
									if ($this->register($attempt_username, $attempt_email, $attempt_pass1)==0)
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
		if (($_POST["action"] == "register") && ($this->config['allow_user_create']=1))
		{	//show registration form
			if (isset($_POST["username"]))
			{
				$previous_username = $this->mysql_db->real_escape_string($_POST["username"]);
			}
			else
			{
				$previous_username = "";
			}
			if (isset($_POST["email"]))
			{
				$previous_email = $this->mysql_db->real_escape_string($_POST["email"]);
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
			$username = $this->mysql_db->real_escape_string($_POST["username"]);
			$passworder = $this->mysql_db->real_escape_string($_POST["password"]);
		
			$_SESSION['username'] = $username;
		}
		else if ($_POST["action"] == "logout")
		{
			echo "Logout<br>\n";
			unset($_SESSION['username']);
			unset($_SESSION['password']);
		}
		else if ($_POST["action"] == "change_pass")
		{	//show form to allow changing password
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
		{	//attempt to change the user password
			$oldpass = $this->mysql_db->real_escape_string($_POST['pass1']);
			$newpass = $this->mysql_db->real_escape_string($_POST['pass2']);
			$passmatch = $this->mysql_db->real_escape_string($_POST['pass3']);
			if ($newpass == $passmatch)
			{
				$uid = $_SESSION['user']['emp_id'];
				$this->store_user_pword($uid, $oldpass, $newpass);
			}
			else
			{
				echo "<h3>Passwords do not match</h3><br >\n";
			}
		}

		#logic for logging in and normal activity
		if (isset($_SESSION['username']))
		{
			$query = "SELECT * FROM " . $this->table_name . " WHERE username='" . $_SESSION['username'] . "' LIMIT 1;";
			$results = $this->mysql_db->query($query);
			if ($results)
			{
				$row = $results->fetch_array(MYSQLI_BOTH);
				#TODO : more testing of the failed login logic
				if ($row['fail_logins'] >= $this->config['max_fail_logins'])
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
					if ( ($row['password'] == $temp) && ($row['stretching'] != $this->config['key_stretching_value']) )
					{	//password is good, key stretching needs to be fixed
						$this->set_user_pword($row['emp_id'], $passworder);
						$fquery = "SELECT * From " . $this->table_name . " WHERE username='" . $_SESSION['username'] . "'LIMIT 1;";
						$fresults = $this->mysql_db->query($fquery);
						if ($fresults)
						{
							$row = $fresults->fetch_array(MYSQLI_BOTH);
						}
						else
						{	//this should never happen
							throw new Exception("Failed to reformat password");
						}
						$temp = hash_password($passworder, $row['salt'], $this->config['key_stretching_value']);
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
						$query = "UPDATE " . $this->table_name . " SET fail_pass_change=0 WHERE emp_id = " . $_SESSION['user']['emp_id'] . ";";
						$this->mysql_db->query($query);
						$query = "UPDATE " . $this->table_name . " SET fail_logins=0 WHERE emp_id = " . $_SESSION['user']['emp_id'] . ";";
						$this->mysql_db->query($query);
					}
					if ($quiet == 0)
					{
						echo "<h3>Welcome ";
						echo $this->get_name($_SESSION['user']['emp_id']);
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
					$query = "UPDATE " . $this->table_name . " SET fail_logins=fail_logins+1 WHERE username = " . $_SESSION['username'] . ";";
					$this->mysql_db->query($query);
					unset($_SESSION['username']);
					unset($_SESSION['password']);
					throw new InvalidUsernameOrPasswordException();
				}
				$results->close();
			}
			else
			{	//contact not found
				unset($_SESSION['username']);
				unset($_SESSION['password']);
				throw new InvalidUsernameOrPasswordException();
			}
		}
		else
		{
			throw new NotLoggedInException();
		}

		if ($quiet == 0)
		{
			echo "</div>\n";
		}
	}

	private function change_user_pword($uid, $oldpass, $newpass)
	{
		$userid = $_SESSION['user']['emp_id'];
		//TODO check for permission to modify password

		$query = "SELECT fail_pass_change, username, password, " .
				 "salt, stretching FROM " . $this->table_name . " WHERE emp_id = '" .
				 $uid . "' LIMIT 1;";

		$results = $this->mysql_db->query($query);
		if ($results)
		{
			$row = $results->fetch_array(MYSQLI_BOTH);
			if ($row['fail_pass_change'] >= $this->config['max_fail_pass_changes'])
			{
				unset($_SESSION['username']);
				unset($_SESSION['password']);
				echo	"<h3>Invalid username or password</h3><br >\n";
			}
			else if ($row['password'] == hash_password($oldpass, $row['salt'], $row['stretching']))
			{	//ok the old password matches
				$salt = generate_salt();

				$query = "UPDATE " . $this->table_name . " SET `salt` = '" . $salt . "' WHERE emp_id = " . $uid . ";";
				if ($this->mysql_db->query($query) == TRUE)
				{
					echo "User salt stored successfully<br >\n";
					$hash_pass = hash_password($newpass, $salt, $this->config['key_stretching_value']);
					$query = "UPDATE " . $this->table_name . " SET `stretching` = '" . $this->config['key_stretching_value'] .
						"' WHERE emp_id = " . $uid . "; ";
					if ($this->mysql_db->query($query) == TRUE)
					{
						echo "User stretching stored successfully<br >\n";
						$query = "UPDATE " . $this->table_name . " SET `password` = '" . $hash_pass . "' WHERE emp_id = " . $uid . ";";
						if ($this->mysql_db->query($query) == TRUE)
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
				$query = "UPDATE " . $this->table_name . " SET fail_pass_change=fail_pass_change+1 WHERE emp_id = " . $uid . ";";
				$this->mysql_db->query($query);
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

	public function get_name($contact_id)
	{	//outputs the contact name
		$output = "";
		$query = "SELECT last_name, first_name FROM " . $this->table_name . " WHERE emp_id = " . $contact_id;
		$contact_results = $this->mysql_db->query($query);
		$last_name_first = $this->config['last_name_first'];

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
	
	private function does_user_exist($username)
	{
		//check to see that the username does not exist first
		$query = "SELECT * FROM " . $this->table_name . " WHERE username='" . $username . "' LIMIT 1;";
		$results = $this->mysql_db->query($query);
		if ($results)
		{
			if ($results->num_rows != 0)
			{
				return 1;	//username exists
			}
		}
		return 0;	//username does not exist
	}

	private function create_user($username, $email)
	{
		$query = "INSERT INTO `" . $this->table_name . "` (username, email)" .
				 " VALUES ('" . $username . "', '" . $email . "');";
		$results = $this->mysql_db->query($query);
		//TODO: handle errors?
		return $this->mysql_db->insert_id;
	}

	private function set_user_password($userid, $pw)
	{
		$query = "SELECT fail_pass_change, username, password, " .
				 "salt FROM " . $this->table_name . " WHERE emp_id = '" .
				 $userid . "' LIMIT 1;";

		$results = $this->mysql_db->query($query);
		if ($results)
		{
			$row = $results->fetch_array(MYSQLI_BOTH);
			if ($row['fail_pass_change'] >= $this->config['max_fail_pass_changes'])
			{
				unset($_SESSION['username']);
				unset($_SESSION['password']);
			}
			else if (is_null($row['password']))
			{	//ok a password does not exist
				//make a new salt
				$salt = generate_salt();

				$query = "UPDATE " . $this->table_name . " SET `salt` = '" . $salt . "' WHERE emp_id = " . $userid . ";";
				if ($this->mysql_db->query($query) == TRUE)
				{
					//echo "User salt stored successfully<br >\n";
					//value in config file used when creating or storing passwords
					$hash_pass = hash_password($pw, $salt, $this->config['key_stretching_value']);
					$query = "UPDATE " . $this->table_name . " SET `stretching` = '" . $this->config['key_stretching_value'] .
						"' WHERE emp_id = " . $userid . "; ";
					if ($this->mysql_db->query($query) == TRUE)
					{
						//echo "User stretching stored successfully<br >\n";
						$query = "UPDATE " . $this->table_name . " SET `password` = '" . $hash_pass . "' WHERE emp_id = " . $userid . ";";
						if ($this->mysql_db->query($query) == TRUE)
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

	public function register($attempt_username, $attempt_email, $attempt_pw)
	{
		//validate the email address?
		if (!filter_var($attempt_email, FILTER_VALIDATE_EMAIL))
		{
			echo "Invalid email address!<br>\n";
			$_POST["action"] = "register";
			return 0;	//invalid email
		}
		if ($this->does_user_exist($attempt_username))
		{
			return 0;
		}
		
		if ($this->config['user_create_type'] != "direct")
		{	//other nonimplemented registration method
			return 0;
		}
		//ok, create the user
		$userid = $this->create_user($attempt_username, $attempt_email);
		$this->set_user_password($userid, $attempt_pw);
		return 1;
	}
}
?>