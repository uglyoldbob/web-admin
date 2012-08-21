<?php
include("global.php");
start_my_session();
header('Content-type: text/html; charset=utf-8');

function __autoload($class_name) {
    include 'include/' . $class_name . '.php';
}

require("include/forms.php");

$jobs = new jobs();

openDatabase();

?>

<!DOCTYPE HTML>
<html>
<head>
<title>Thermal Specialists Jobs List</title>
<link rel="stylesheet" type="text/css" href="css/global.css" />
</head>

<body>

<script type="text/javascript" src="jquery-1.2.1.pack.js"></script>
<script type="text/javascript" src="jscript.js"></script>

<?php

//make sure the user is logged in properly
$stop = 0;
echo '<div>' . "\n";
if (login_code(0) == 1)
{
	$stop = 1;
}
echo "</div>\n";
if ($stop == 0)
{
	selectTimePeriod();
	
	echo '<a href="' . rootPageURL() . '">Return to main</a>' . "<br >\n";
	
	if ($_POST["action"] == "apply")
	{	//apply job data
		$jobs->create_job($_POST);
		$_POST["action"] = "";	//transition to listing the newly created job
	}
	else if ($_POST["action"] == "modjob")
	{
		$jobs->modify_job($_POST);
		$_POST["action"] = "";	//transition to listing the newly created job
	}
	
	if ($_POST["action"] != "edit")
	{	//don't create the new job button if the create job form is going to be displayed
		//don't create the new job button if a specific job is going to be displayed
		echo "<form action=\"" . rootPageURL() . "/jobs.php\" method=\"post\">\n" .
			 "	<input type=\"hidden\" name=\"action\" value=\"edit\">\n" .
			 "	<input type=\"hidden\" name=\"id\" value=\"0\">\n" .
			 "	<input type=\"submit\" value=\"Create new job\"/>\n" .
			 "</form>";
	}
	if (($_POST["action"] == "edit") || ($jobs->job != 0))
	{
		if ($jobs->job != 0)
		{	//do this when listing information for a specific job
			$jobs->list_job();			
		}
		else
		{	//do this when creating a new job
			echo "<a href=\"" . rootPageURL() . "/jobs.php\"> " . " Back to all jobs</a><br >\n<h3>Creating new job:</h3>\n";
			job_form();
		}
	}
	else	//if (($_POST["action"] == "")
	{	//do this when listing all jobs
		$jobs->table();
	}
	
	//bcmul, bcadd,
	//
}

/*
$query = "SHOW SESSION STATUS";
$result = $mysql_db->query($query);
while ($row = $result->fetch_array(MYSQLI_BOTH))
{
	echo $row[0] . " " . $row[1];
	echo "<br >\n";
}*/

//this function is undefined
//print_r(mysqli_get_client_stats());

closeDatabase();

?>

</body>
</html>
