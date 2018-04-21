<?php
namespace webAdmin;
require_once "global.php";
require_once "webAdmin/table.php";

class user
{
	private $config;
	private $mysql_db;
	private $table_name;
	private $permission_table;
	
	private $root_ca_table;
	private $intermediate_ca_table;
	private $user_cert_table;
	
	private $userid;
	private $username;
	private $passhash;
	private $valid_cert;
	private $registered_cert;

	public function __construct($config, $mysql_db, $table_name, $permission_table = "user_permission")
	{
		$this->config = $config;
		$this->mysql_db = $mysql_db;
		$this->table_name = $table_name;
		$this->permission_table = $permission_table;
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
		if (is_dir($this->config['root_cert_folder']))
		{
			$files_to_check = scandir($this->config['root_cert_folder']);
			foreach ($files_to_check as $f)
			{
				$fname = $this->config['root_cert_folder'] . '/' . $f;
				if (is_file($fname))
				{
					$cert = file_get_contents($fname);
					$x509 = new \File_X509();
					$x509->loadX509($cert);
					if (!($x509->validateSignature(false)))
					{
						throw new SiteConfigurationException("Invalid ROOT CA certificate file found: " . $f);
					}
					$intermediate_check->loadCA($cert);
				}
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
		if (is_dir($this->config['int_cert_folder']))
		{
			$files_to_check = scandir($this->config['int_cert_folder']);
			foreach ($files_to_check as $f)
			{
				$fname = $this->config['int_cert_folder'] . '/' . $f;
				if (is_file($fname))
				{
					$cert = file_get_contents($fname);
					$intermediate_check->loadX509($cert);
					if (!($intermediate_check->validateSignature()))
						throw new SiteConfigurationException("Invalid INTERMEDIATE CA certificate file found: " . $f);
					$intermediate_check->loadCA($cert);
				}
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
				echo "<form action=\"" . curPageURL($this->config) . "\" method=\"post\">\n" .
					 "	<input type=\"hidden\" name=\"action\" value=\"register_cert\">\n" .
					 "	<input class=\"buttons\" type=\"submit\" value=\"Register certificate\">\n" .
					 "</form>\n";
			}
		}
		catch (CertificateException $e)
		{
		}
	}
	
	//check to see if a request has been made to revoke a certificate
	public function revoke_own_certificates()
	{
		if ($_POST["action"] == "revoke_certificate")
		{
			$this->require_login_or_registered_certificate();
			$query = "UPDATE " . $this->user_cert_table . 
			 " SET userid=NULL WHERE id='" . $_POST["id"] . "' " .
			 " AND userid='" . $this->userid . "';";
			$result = $this->mysql_db->query($query);
			if (!$result)
			{
				echo $this->mysql_db->error . "<br />\n";
				throw new CertificateException("Failed to revoke user certificate");
			}
		}
	}
	
	public function registered_certs_data()
	{
		$ret = [];
		try
		{
			$this->require_login_or_registered_certificate();
			$query = "SELECT `id`,`serial`,`identifier`,`issuer` FROM " . $this->user_cert_table . 
			 " WHERE `userid` = '" . $this->userid . "';";
			$result = $this->mysql_db->query($query);
			if ($result->num_rows <= 0)
			{
				$this->registered_cert = -1;
				throw new CertificateException("Unregistered certificate");
			}
			else
			{
				$ret = make_double_array_mysqli($result);
				$ret[0][0] = "Action";
				for ($i = 1; $i < count($ret[0]); $i++)
				{
					$ret[0][$i] = "<form action=\"" . curPageURL($this->config) . "\" method=\"post\"> " .
						 " <input type=\"hidden\" name=\"action\" value=\"revoke_certificate\"> " .
						 " <input type=\"hidden\" name=\"id\" value=\"" . $ret[0][$i] . "\"> " .
						 " <input class=\"buttons\" type=\"submit\" value=\"Revoke\">" .
						 "</form>\n";
				}
			}
		}
		catch (CertificateException $e)
		{
			$ret[0] = [];
			$ret[0][0] = "Action";
			$ret[1] = [];
			$ret[1][0] = "serial";
			$ret[2] = [];
			$ret[2][0] = "identifier";
			$ret[3] = [];
			$ret[3][0] = "issuer";
		}
		return $ret;
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
	
	//permissions
	//read specific field of a specific user
	//write specific field of a specific user
	//read specific field of all users
	//write specific field of all users
	//create users (create on all users)
	//delete users (delete on all users)
	
	//r = read
	//w = write
	//p = modify password
	function check_permission($idfrom, $idto, $mask)
	{	//returns an array containing "master", "public", "global", "normal", "none"
		global $mysql_db;
		$output = array();
		$query = "SELECT * FROM `" . $this->permission_table . "` WHERE " .
			"((id1 IS NULL) OR (id1 = " . $idto . ")) AND " .
			"((id2 IS NULL) OR (id2 = " . $idfrom . ")) " .
			"AND (permission LIKE '" . $mask . "');";
		$result = $mysql_db->query($query);
		if (($result) && ($row = $result->fetch_array(MYSQLI_BOTH)) )
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
			$result->close();
		}
		else
		{
			$output[0] = array(NULL, "none");
		}
		return $output;
	}

	function check_specific_permission($results, $permission)
	{	//check for the presence of a certain type of permission
		//use on the results of check_permission
		foreach ($results as $permcheck)
		{
			if ($permcheck[1] == $permission)
			{
				return "yes";
			}
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
		
		$permcheckarray = $this->check_permission($idfrom, $idto, '%' . $perm . '%');

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
	
	public function table()
	{
		global $mysql_db;
		$start_page = 0;	//TODO: fix this
		$uid = $_SESSION['user']['emp_id'];
		$query = "SELECT * FROM " . $this->table_name . ", user_permission WHERE " .
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
				$allow = $this->check_permission($uid, $row['emp_id'], "%p%");
				if ($this->check_specific_permission($allow, "global") == "yes")
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
	
	public function print_user($contact_id)
	{	//outputs the contact name
		$output = "";
		$query = "SELECT last_name, first_name FROM contacts WHERE emp_id = " . $contact_id;
		
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

	public function single()
	{
		global $mysql_db;
		$uid = $_SESSION['user']['emp_id'];
		
		$contact = $_GET["contact"];
		if (!is_numeric($contact))
		{
			$contact = 0;
		}
		
		$allow = $this->check_permission($uid, $contact, "%w%");
		if ($allow[0][1] == "none")
		{
			$_POST["action"] = "";
		}
		
		$query = "SELECT * FROM users, user_permission WHERE " .
				 "(((id2 = " . $uid . ") OR (id2 IS NULL)) AND " .
				 "((id1 = emp_id) OR (id1 IS NULL)) AND " .
				 "(emp_id = " . $contact . ") AND " .
				 "(permission LIKE '%r%')) LIMIT 1;";
		$results = $mysql_db->query($query);
		if ($results && ($row = $results->fetch_array(MYSQLI_BOTH)))
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
			//echo print_contact($this->contact);
			echo "</h3>\n";
			echo "<a href=\"" . rootPageURL($this->config) . "/payments.php?contact=" . $contact . "\">View payments</a><br>\n";
			echo "	<form action=\"" . rootPageURL($this->config) . "/payments.php\" method=\"post\">\n" .
				 "		<input type=\"hidden\" name=\"action\" value=\"edit\">\n" .
				 "		<input type=\"hidden\" name=\"id\" value=\"0\">\n" .
				 "		<input type=\"hidden\" name=\"payer\" value=\"" . $row['emp_id'] . "\">\n" .
				 "		<input class=\"buttons\" type=\"submit\" value=\"This contact made a payment\"/>\n" .
				 "	</form>\n";
			echo "	<form action=\"" . rootPageURL($this->config) . "/payments.php\" method=\"post\">\n" .
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
		$allow = $this->check_permission($uid, $id, "%w%");
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
		}
		else
		{
			echo "<form action=\"contacts.php?contact=" . $id . "\" method=\"post\">\n" .
				 "	<input type=\"hidden\" name=\"action\" value=\"update\"><br>\n";
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
			$table_data["Id number"] = "<input type=\"text\" name=\"id\" value=\"" . $id . "\" size=\"70\" readonly >";
		}
		
		$is_disabled = "";
		if ($_POST["action"] == "")
		{
			$is_disabled = " readonly";
		}
		
		$table_data["Last Name"] = "<input type=\"text\" name=\"last_name\" value=\"" . 
			$last_name . "\" size=\"70\"" . $is_disabled . " >";
		$table_data["First Name"] = "<input type=\"text\" name=\"first_name\" value=\"" . 
			$first_name . "\" size=\"70\"" . $is_disabled . " >";
		$table_data["Username"] = "<input type=\"text\" name=\"username\" value=\"" . 
			$username . "\" size=\"70\"" . $is_disabled . " >";
		$table_data["Classification"] = "<input type=\"text\" name=\"classify\" value=\"" . 
			$classify . "\" size=\"70\"" . $is_disabled . " >";
		$table_data["Eligible for Payment"] = "<input type=\"text\" name=\"eligible\" value=\"" . 
			$eligible . "\" size=\"70\"" . $is_disabled . " >";
		$table_data["Phone(mobile)"] = "<input type=\"text\" name=\"mobile\" value=\"" . 
			$mobile . "\" size=\"70\"" . $is_disabled . " >";
		$table_data["Phone(home)"] = "<input type=\"text\" name=\"home\" value=\"" . 
			$home . "\" size=\"70\"" . $is_disabled . " >";
		$table_data["Phone(other)"] = "<input type=\"text\" name=\"other\" value=\"" . 
			$other . "\" size=\"70\"" . $is_disabled . " >";
		$table_data["Website"] = "<input type=\"text\" name=\"website\" value=\"" . 
			$website . "\" size=\"70\"" . $is_disabled . " >";
		$table_data["E-mail address"] = "<input type=\"text\" name=\"email\" value=\"" . 
			$email . "\" size=\"70\"" . $is_disabled . " >";
		$table_data["Street Address"] = "<input type=\"text\" name=\"street\" value=\"" . 
			$street . "\" size=\"70\"" . $is_disabled . " >";
		$table_data["City"] = "<input type=\"text\" name=\"city\" value=\"" . 
			$city . "\" size=\"70\"" . $is_disabled . " >";
		$table_data["State"] = "<input type=\"text\" name=\"state\" value=\"" . 
			$state . "\" size=\"70\"" . $is_disabled . " >";
		$table_data["Zipcode"] = "<input type=\"text\" name=\"zip\" value=\"" . 
			$zip . "\" size=\"70\"" . $is_disabled . " >";
		basic_table($table_data);
	
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
		if (array_key_exists("id", $withdata))
		{
			$id_num = $withdata["id"];
		}
		else
		{
			$id_num = 0;
		}
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
			$allow = $this->check_permission($uid, $id_num, "%w%");
			if ($allow[0][1] != "none")
			{
				$allowed_to_perform = 1;
			}
			
			$query = "UPDATE `" . $this->table_name . "` SET " .
					"`last_name` = '" . $last_name .
					"', `first_name` = '" . $first_name;
			
			if ($username != "")
			{
				$query = $query . "', `username` = '" . $username . "'";
			}
			else
			{
				$query = $query . "', `username` = NULL";
			}
			$query = $query . ", `classification` = '" . $classification .
					"', `payment_eligible` = '" . $eligibility .
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
			$query = "INSERT INTO `" . $this->table_name . "` " .
					 "(last_name, first_name, username, classification, payment_eligible, " .
					 "phone_mobile, phone_home, phone_other, website, email, address, city, state, zipcode) " .
					 "VALUES (" .
					 "'" . $last_name .  "'," .
					 "'" . $first_name .  "',";
					 
			if ($username != "")
			{
				$query = $query . "'" . $username . "', ";
			}
			else
			{
				$query = $query . "NULL, ";
			}
			$query = $query . "'" . $classification . "'," .
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
				throw new \Exception("Error: " . $mysql_db->error . "<br >\n");
			}
			else
			{
				if ($id_num == 0)
				{ 
					$new_id = $mysql_db->insert_id;
					$this->mod_permission("contact_permission", $uid, $new_id, "+", 'r');
					$this->mod_permission("contact_permission", $uid, $new_id, "+", 'w');
					$this->mod_permission("contact_permission", $new_id, $new_id, "+", 'r');
					$this->mod_permission("contact_permission", $new_id, $new_id, "+", 'w');
				}
				echo "Contact information updated successfully.<br >\n";
			}
		}
		else
		{
			echo "<b>You can't do that</b><br >\n";
		}
	}
}
?>
