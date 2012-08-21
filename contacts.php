<?php

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
<title>Thermal Specialists Contact Listing</title>
<link rel="stylesheet" type="text/css" href="css/global.css" />
</head>
<body>

<?php
$stop = 0;
echo "<div>\n";
if (login_code(0) == 1)
{
	$stop = 1;
}
echo "</div>\n";
if ($stop == 0)
{
	selectTimePeriod();
	
	echo '<a href="' . rootPageURL() . '">Return to main</a>' . "<br >\n";
	
	//update contact information
	if ($_POST["action"] == "update")
	{
		$contacts->update($_POST);
	}

	//edit or view contact information
	if (($_POST["action"] == "edit") || ($contacts->contact != 0))
	{
		$contacts->single();
	}
	else if ($_POST["action"] == "create")
	{
		echo "<h3>Creating new contact:</h3>\n<a href=\"" . rootPageURL() . "/contacts.php\"> " . " Back to all contacts</a><br >\n\n";
		$contacts->make_form(0, '', '', '',
			'', '', '', '', '',
			'', '', '', '', '', '');
	}
	else
	{	//display all contacts
		$contacts->table();
	}
}

closeDatabase();

?>

</body>
</html>
