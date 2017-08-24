<?php
$config = parse_ini_file("config.ini");
include("global.php");

start_my_session();
header('Content-type: text/html; charset=utf-8');

function __autoload($class_name) {
    include 'include/' . $class_name . '.php';
}
require("include/forms.php");

$contacts = new contacts();

openDatabase();
//TODO : create a header.php

?>

<!DOCTYPE HTML>
<html>
<head>
<title>Contact Listing: <?php sitename()?></title>
<?php do_css() ?>
</head>
<body>

<?php

#TODO : add photos for contacts

$stop = 0;
if (login_code(0) == 1)
{
	$stop = 1;
}
if ($stop == 0)
{
	do_top_menu(2);	
	
	//update contact information
	if ($_POST["action"] == "update")
	{
		$contacts->update($_POST);
		$_POST["action"] = "";	//go back to contact viewing
	}
	else if ($_POST["action"] == "cpass")
	{
		$val = $_POST["id"];
		if (is_numeric($val) == FALSE)
			$val = 0;
		$contacts->create_password($val);
	}
	else if ($_POST["action"] == "epass")
	{
		$val = $_POST["id"];
		if (is_numeric($val) == FALSE)
			$val = 0;
		$contacts->edit_password($val);
	}
	else if ($_POST["action"] == "apass")
	{
		$val = $_POST["id"];
		if (is_numeric($val) == FALSE)
			$val = 0;
		$userid = $_SESSION['user']['emp_id'];
		$allow = check_permission("contact_permission", $userid, $val, "%p%");
		if (check_specific_permission($allow, "global") == "yes")
		{
			$newpass = $mysql_db->real_escape_string($_POST['pass2']);
			$passmatch = $mysql_db->real_escape_string($_POST['pass3']);
			if ($newpass == $passmatch)
			{
				contacts::mod_user_pword($val, $newpass);
			}
			else
			{
				echo "<h3>Passwords do not match</h3><br >\n";
			}
		}
		else
		{
			echo "<b>You can't do that</b><br >\n";
		}
		
	}
	

	//edit or view contact information
	if (($_POST["action"] == "edit") || ($contacts->contact != 0))
	{
		$contacts->single();
	}
	else if ($_POST["action"] == "create")
	{
		echo "<h3>Creating new contact:</h3>\n<br >\n";
		$contacts->make_form(0, '', '', '',
			'', '', '', '', '',
			'', '', '', '', '', '', '');
	}
	else if ($_POST["action"] == "")
	{	//display all contacts
		$contacts->table();
	}
}

closeDatabase();

?>

</body>
</html>
