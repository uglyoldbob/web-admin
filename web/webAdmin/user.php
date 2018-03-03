<?php
namespace webAdmin;

class user
{
	private $config;
	private $mysql_db;
	private $table_name;
	
	private $root_ca_table;
	private $intermediate_ca_table;
	private $user_cert_table;
	
	private $userid;
	private $username;
	private $passhash;
	private $valid_cert;
	private $registered_cert;
	
	public function __construct($config, $mysql_db, $table_name)
	{
		$this->config = $config;
		$this->mysql_db = $mysql_db;
		$this->table_name = $table_name;
		$this->valid_cert = 0;
		$this->registered_cert = 0;
	}
	
	public function certificate_tables($rootca, $intca, $usercert)
	{
		$this->root_ca_table = $rootca;
		$this->intermediate_ca_table = $intca;
		$this->user_cert_table = $usercert;
		$this->verify_certificates();
	}
	
	private function verify_root_certificates()
	{
		$query = 'select cert from ' . $this->root_ca_table;
		$result = $this->mysql_db->query($query);
		$intermediate_check = new \File_X509();
		if ($result && ($result->num_rows > 0))
		{
			while ($row = $result->fetch_assoc())
			{
				$cert = $row['cert'];
				$x509 = new \File_X509();
				$x509->loadX509($cert);
				if (!($x509->validateSignature(false)))
					throw new SiteConfigurationException("Invalid ROOT CA certificate found");
				$intermediate_check->loadCA($cert);
			}
		}
		return $intermediate_check;
	}
	
	private function verify_certificates()
	{
		$intermediate_check = $this->verify_root_certificates();

		$query = 'select cert from ' . $this->intermediate_ca_table;
		$result = $this->mysql_db->query($query);
		if ($result && ($result->num_rows > 0))
		{
			while ($row = $result->fetch_assoc())
			{
				$cert = $row['cert'];
				$intermediate_check->loadX509($cert);
				if (!($intermediate_check->validateSignature()))
					throw new SiteConfigurationException("Invalid INTERMEDIATE CA certificate found");
				$intermediate_check->loadCA($cert);
			}
		}
		return $intermediate_check;
	}
	
	public function register_certificate()
	{
		$this->require_certificate();
		$this->require_login(1);
		$query = "INSERT INTO " . $this->user_cert_table . " (`serial`, `issuer`, `identifier`, `userid`) VALUES ('" .
		 $this->mysql_db->real_escape_string($_SERVER['SSL_CLIENT_M_SERIAL']) . "','" .
		 $this->mysql_db->real_escape_string($_SERVER['SSL_CLIENT_I_DN']) . "','" .
		 $this->mysql_db->real_escape_string($_SERVER['SSL_CLIENT_S_DN']) . "','" .
		 $this->userid . "');";
		$results = $this->mysql_db->query($query);
		if ($results)
		{
			$cert_id = $this->mysql_db->insert_id;
		}
		else
		{
			echo $this->mysql_db->error . "<br />\n";
			throw new CertificateException("Failed to insert user certificate");
		}
	}
	
	public function require_registered_certificate()
	{
		if ($this->registered_cert == 0)
		{
			$this->require_certificate();
			$query = "SELECT * FROM " . $this->user_cert_table . 
			 " INNER JOIN " . $this->table_name . " ON " . $this->table_name . ".emp_id = " .
			 $this->user_cert_table . ".userid" .
			 " WHERE `serial` = '" .
			 $this->mysql_db->real_escape_string($_SERVER['SSL_CLIENT_M_SERIAL']) .
			 "' AND `issuer` = '" .
			 $this->mysql_db->real_escape_string($_SERVER['SSL_CLIENT_I_DN']) .
			 "' AND `identifier` = '" .
			 $this->mysql_db->real_escape_string($_SERVER['SSL_CLIENT_S_DN']) . "';";
			$result = $this->mysql_db->query($query);
			if ($result->num_rows <= 0)
			{
				$this->registered_cert = -1;
				throw new CertificateException("Unregistered certificate");
			}
			else
			{
				$row = $result->fetch_array(MYSQLI_BOTH);
				$this->passhash = $row['password'];
				$this->userid = $row['emp_id'];
				$this->username = $row['username'];
				$this->registered_cert = 1;
			}
		}
		else if ($this->registered_cert == -1)
		{
			throw new CertificateException("Unregistered certificate");
		}
	}
	
	public function require_certificate()
	{
		if ($this->valid_cert == 0)
		{
			if (!(array_key_exists("SSL_CLIENT_CERT", $_SERVER)))
			{
				$this->valid_cert = -1;
				throw new CertificateException("No certificate");
			}
			if (!isset($_SERVER['SSL_CLIENT_M_SERIAL'])
				|| !isset($_SERVER['SSL_CLIENT_V_END'])
				|| !isset($_SERVER['SSL_CLIENT_V_START'])
				|| !isset($_SERVER['SSL_CLIENT_I_DN']) 
				|| !isset($_SERVER['SSL_CLIENT_V_REMAIN']) )
			{
				$this->valid_cert = -1;
				throw new CertificateException("Invalid signature on certificate found");
			}
			
			if ($_SERVER['SSL_CLIENT_V_REMAIN'] <= 0)
			{
				$this->valid_cert = -1;
				throw new CertificateException("Certificate is expired");
			}
			
			$check = $this->verify_certificates();
			$check->loadX509($_SERVER['SSL_CLIENT_CERT']);
			if (!($check->validateSignature()))
			{
				$this->valid_cert = -1;
				throw new CertificateException("Invalid signature on certificate found");
			}
			$this->valid_cert = 1;
		}
		else if ($this->valid_cert == -1)
		{
			throw new CertificateException("Invalid certificate");
		}
	}

	public function process_user_registration()
	{	//process POST data from attempted user registration
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
	}
	
	public function show_register_certificate_button()
	{
		try
		{
			$this->require_login_or_registered_certificate();
			$this->require_certificate();
			try
			{
				$this->require_registered_certificate();
				echo "You have a registered certificate<br />\n";
			}
			catch (CertificateException $e)
			{
				$this->require_login(1);
				echo "<form action=\"" . curPageURL($config) . "\" method=\"post\">\n" .
					 "	<input type=\"hidden\" name=\"action\" value=\"register_cert\">\n" .
					 "	<input class=\"buttons\" type=\"submit\" value=\"Register certificate\">\n" .
					 "</form>\n";
			}
		}
		catch (CertificateException $e)
		{
		}
	}
	
	public function require_login_or_registered_certificate()
	{
		try
		{
			$this->require_login(0);
		}
		catch (InvalidUsernameOrPasswordException $e)
		{
			try
			{
				$this->require_registered_certificate();
			}
			catch (CertificateException $f)
			{
				throw new NotLoggedInException();
			}
		}
		catch (NotLoggedInException $e)
		{
			try
			{
				$this->require_registered_certificate();
			}
			catch (CertificateException $f)
			{
				throw new NotLoggedInException();
			}
		}
	}

	public function require_login($quiet)
	{	//only successfully finishes if a user is logged in, otherwise it throws exceptions

		//if https is not detected, then assume https is not being used
		if (!(array_key_exists("HTTPS", $_SERVER)))
		{
			$_SERVER["HTTPS"] = "off";
		}
		
		if (($_SERVER["HTTPS"] != "on") && ($this->config['require_https'] == 1))
		{
			throw new SiteConfigurationException("HTTPS is required and has not been detected");
		}
		
		if ($_POST["action"] == "login")
		{       //retrieve submitted username and password, if applicable
			$username = $this->mysql_db->real_escape_string($_POST["username"]);
			$passworder = $this->mysql_db->real_escape_string($_POST["password"]);
			$_SESSION['username'] = $username;	
		}

		#logic for logging in and normal activity
		if (isset($_SESSION['username']))
		{
			$this->username = $_SESSION['username'];
			$query = "SELECT * FROM " . $this->table_name . " WHERE username='" . $_SESSION['username'] . "' LIMIT 1;";
			$results = $this->mysql_db->query($query);
			if ($results)
			{
				$row = $results->fetch_array(MYSQLI_BOTH);
				#TODO : more testing of the failed login logic
				if ($row['fail_logins'] >= $this->config['max_fail_logins'])
				{	//TODO: set time period for waiting to login
					unset($_SESSION['username']);
					unset($_SESSION['password']);
					throw new InvalidUsernameOrPasswordException();
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
					unset($passworder);
					$_SESSION['password'] = $temp;
					$this->passhash = $temp;
				}
				if (($row['password'] == $_SESSION['password']) && isset($_SESSION['password']) && ($_SESSION['password'] <> ""))
				{	#successful login
					#TODO : limit the number of valid sessions for users? create a valid session table?
					$_SESSION['user'] = $row;
					$this->userid = $row['emp_id'];
					if ($_POST["action"] == "login")
					{
						$query = "UPDATE " . $this->table_name . " SET fail_pass_change=0 WHERE emp_id = " . $_SESSION['user']['emp_id'] . ";";
						$this->mysql_db->query($query);
						$query = "UPDATE " . $this->table_name . " SET fail_logins=0 WHERE emp_id = " . $_SESSION['user']['emp_id'] . ";";
						$this->mysql_db->query($query);
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
