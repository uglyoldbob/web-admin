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
<title>Thermal Specialists Jobss List</title>
</head>

<body>

<script type="text/javascript" src="jquery-1.2.1.pack.js"></script>
<script type="text/javascript">
	function lookupLastName(textId, callId, suggestionBox, suggestionList, formName, formId, formSuggest) 
	{	//operates the autocomplete for a textbox
		if(textId.length == 0) 
		{
			// Hide the suggestion box.
			suggestionBox.hide();
		}
		else 
		{
			$.post("lastNameLookup.php", {queryString: ""+textId+"",
					call: ""+callId+"",	
					formName: ""+formName+"",
					formId: ""+formId+"", 
					formSuggest: ""+formSuggest+""}, 
				function(data)
			{
				if(data.length >0)
				{
				suggestionBox.show();
				suggestionList.html(data);
				}
			});
		}
	} // lookup
	
	function fillNames(thisValue, thatValue, formName, id, suggest) 
	{	//fills in the value when an autocomplete value is selected
			
		//$('#name_payer').val(thisValue);
		formName.val(thisValue);
		//$('#id_payer').val(thatValue);
		id.val(thatValue);
		//setTimeout("$('#payer_suggestions').hide();", 200);
		suggest.hide().delay(200);
	}
	
</script>

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
	{
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
		$_POST["action"] = "";		
	}
	
	if ($_POST["action"] != "edit")
	{
		echo "<form action=\"" . rootPageURL() . "/jobs.php\" method=\"post\">\n" .
			 "	<input type=\"hidden\" name=\"action\" value=\"edit\">\n" .
			 "	<input type=\"hidden\" name=\"id\" value=\"0\">\n" .
			 "	<input type=\"submit\" value=\"Create new job\"/>\n" .
			 "</form>";
	}	
	if (($_POST["action"] == "edit") || ($job != 0))
	{
		if ($job != 0)
		{
			echo "Cannot display an existing job<br >\n";
		}
		else
		{
			echo "<a href=\"" . rootPageURL() . "/jobs.php\"> " . " Back to all jobs</a><br >\n<h3>Creating new job:</h3>\n";
			job_form();
		}
	}
	else	//if (($_POST["action"] == "")
	{
		echo "Cannot show list of jobs<br >\n";
	}
	
	//bcmul, bcadd,
	//
}

closeDatabase();

?>

</body>
</html>
