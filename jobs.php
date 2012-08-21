<?php
include("global.php");
start_my_session();
header('Content-type: text/html; charset=utf-8');

require("forms.php");

$contact = $_GET["contact"];
if (is_numeric($contact) == FALSE)
	$contact = 0;

$job = $_GET["job"];
if (is_numeric($job) == FALSE)
	$job = 0;

$start_page = $_GET["page"];
if (is_numeric($start_page) == FALSE)
	$start_page = 0;

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
		$cust1 = $_POST["cust1_id"];
		if (is_numeric($cust1) == FALSE)
			$cust1 = 0;
		$cust2 = $_POST["cust2_id"];
		if (is_numeric($cust2) == FALSE)
			$cust2 = 0;
		$comments = $mysql_db->real_escape_string($_POST["comments"]);
		$query = "INSERT INTO jobs (" .
			"`id` ," . "`cust_billing` , " . "`cust_shipping` , " .
			"`comments` " . ") VALUES (" .
			"NULL , '" . $cust1 . "', '" . $cust2 . "', '" . $comments . "');";
		if ($mysql_db->query($query) == TRUE)
		{
			echo "Successfully inserted new job<br >\n";
		}
		
		$job = $mysql_db->insert_id;
		
		$_POST["action"] = "";	//transition to listing the newly created job
	}
	else if ($_POST["action"] == "modjob")
	{
		$query = "UPDATE jobs SET ";
		$needs_comma = 0;
		$do_anything = 0;
		if ($_POST['mod_phone1'] == "on")
		{
			$do_anything = 1;
			if ($needs_comma == 0)
				$needs_comma = 1;
			else
				$query .= ", ";
			$query .= "phone_notify_id=" . $mysql_db->real_escape_string($_POST['phone1']);
		}
		
		if ($_POST['mod_comments1'] == "on")
		{
			$do_anything = 1;
			if ($needs_comma == 0)
				$needs_comma = 1;
			else
				$query .= ", ";
			$query .= "comments=\"" . $mysql_db->real_escape_string($_POST['comments']) . "\"";
		}
		
		$query .= " WHERE id=" . $mysql_db->real_escape_string($_POST['id']) . ";";
		if ($do_anything == 1)
		{
			if ($mysql_db->query($query) == TRUE)
			{
				echo "Successfully updated new job<br >\n";
			}
		}
		else
		{
			echo "The job was unchanged<br >\n";
		}
		
		$job = $mysql_db->real_escape_string($_POST['id']);
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
	if (($_POST["action"] == "edit") || ($job != 0))
	{
		if ($job != 0)
		{	//do this when listing information for a specific job
			$query = "SELECT * FROM jobs WHERE id = " . $job . " LIMIT 1;";
			$result = $mysql_db->query($query);
			if($row = $result->fetch_array(MYSQLI_BOTH))
			{
				echo "<a href=\"" . rootPageURL() . "/jobs.php\"> " . " Back to all jobs</a><br >\n";
				
				echo "<form action=\"" . rootPageURL() . "/jobs.php\" method=\"post\">\n" .
					 "	<input type=\"hidden\" name=\"action\" value=\"modjob\">\n" .
					 "	<input type=\"hidden\" name=\"id\" value=\"" . $job . "\">\n";
				
				echo "	<b>Billing name:</b> ";
				echo print_contact($row['cust_billing']);
				echo "	<br >\n<b>Shipping name:</b> ";
				echo print_contact($row['cust_shipping']);
				echo "	<br >\n";

				//load number information				
				$phone_results = get_phone_options($row['cust_billing'], $row['cust_shipping']);
				
				if ($row['phone_notify_id'] != null)
				{
					echo "	<b>Contact by phone:</b> \n" .
						 $phone_results[$row['phone_notify_id']]['name'] . ": " .
						 $phone_results[$row['phone_notify_id']]['number'] . "\n";
				}
				else
				{
					echo "	No one will be contacted by phone for this job.\n";
				}
				
				$select_radio = $row['phone_notify_id'];
				if (is_numeric($select_radio) == FALSE)
					$select_radio = 6;

				echo "	<input type=\"checkbox\" name=\"mod_phone1\" ";
				echo "onchange=\"cb_hide_show(this, $('#mod_phone1'));\" />Change this phone number<br >\n";
				echo "	<div id=\"mod_phone1\" style=\"display: none;\">\n";
				for ($i = 0; $i < 6; $i++)
				{
					if ($phone_results[$i]['number'] != null)
					{
						echo "	<input type=\"radio\" name=\"phone1\" " .
							 "value=\"" . $i . "\" ";
						if ($select_radio == $i)
							echo "checked ";
						echo ">" . $phone_results[$i]['name'] . 
							 ": " . $phone_results[$i]['number'] . "<br >\n";
					}
				}
				echo "	<input type=\"radio\" name=\"phone1\" " .
					 "value=\"6\" ";
				if ($select_radio == 6)
					echo "checked ";
				echo ">None<br >\n";
				echo "	</div>\n";
				
				echo "	<b>Comments:</b> " . $row['comments'] . "\n";
				echo "	<input type=\"checkbox\" name=\"mod_comments1\" ";
				echo "onchange=\"cb_hide_show(this, $('#mod_comments'));\" />Change the comments<br >\n";
				echo "	<div id=\"mod_comments\" style=\"display: none;\">\n";
				echo '	<textarea name="comments" id="comments" rows=4 cols=75 >' .
					$mysql_db->real_escape_string($row['comments']) .
					'</textarea><br >' . "\n";
				echo "	</div>\n";
				
				echo "	<input type=\"submit\" value=\"Apply Changes\"/>\n" .
					 "</form>";
			}
			else
			{
				echo "Invalid job specified<br >\n";
			}
			$result->close();			
		}
		else
		{	//do this when creating a new job
			echo "<a href=\"" . rootPageURL() . "/jobs.php\"> " . " Back to all jobs</a><br >\n<h3>Creating new job:</h3>\n";
			job_form();
		}
	}
	else	//if (($_POST["action"] == "")
	{	//do this when listing all jobs
		if ($contact != 0)
		{
			$query = "SELECT * FROM jobs WHERE cust_billing = " . $contact . " OR " .
				"cust_shipping = " . $contact . " ORDER BY id DESC LIMIT " . 
				($start_page*30) . ", " . ($start_page*30+30);
		}
		else
		{
			$query = "SELECT * FROM jobs ORDER BY id DESC LIMIT " . 
				($start_page*30) . ", " . ($start_page*30+30);
		}
		$contact_results = $mysql_db->query($query);
		
		if ($contact_results->num_rows > 30)
		{
			$next_page = 1;
		}
		else
		{
			$next_page = 0;
		}
	
		echo "<table border=\"1\">\n";
		echo "	<tr>\n";
		echo "		<th>Options</th>\n";
		echo "		<th>Billing</th>\n";
		echo "		<th>Shipping</th>\n";
		echo "		<th>Comments</th>\n";
		echo "	</tr>\n";
	
		while($row = $contact_results->fetch_array(MYSQLI_BOTH))
		{
			echo "	<tr>\n";
	
			echo "		<td>\n";
						
			echo "			<a href=\"". rootPageURL() . "/jobs.php?job=" . $row['id'] . "\">View</a>\n";
			echo "		</td>\n";
	
			echo "		<td>";
			echo print_contact($mysql_db->real_escape_string($row['cust_billing']));
			echo "</td>\n";
			echo "		<td>";
			echo print_contact($mysql_db->real_escape_string($row['cust_shipping']));
			echo "</td>\n";
			echo "		<td>" . $mysql_db->real_escape_string($row['comments']) . "</td>\n";
	
			echo "	</tr>\n";
		}
	
		echo "</table><br>\n";
	
		if ($start_page > 0)
			echo '<a href="' . rootPageURL() . '/jobs.php?page=' . ($start_page-1) . '">Previous page</a>  ';
		if ($next_page == 1)
			echo '<a href="' . rootPageURL() . '/jobs.php?page=' . ($start_page+1) . '">Next page</a>' . "<br >\n";
		$contact_results->close();
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
