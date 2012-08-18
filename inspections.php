<?php
include("global.php");
start_my_session();
header('Content-type: text/html; charset=utf-8');

require("forms.php");

$contact = $_GET["contact"];
if (is_numeric($contact) == FALSE)
	$contact = 0;

$start_page = $_GET["page"];
if (is_numeric($start_page) == FALSE)
	$start_page = 0;

openDatabase();

?>

<!DOCTYPE HTML>
<html>
<head>
<title>Thermal Specialists Inspections List</title>
</head>

<body>

<script type="text/javascript" src="jquery-1.2.1.pack.js"></script>
<script type="text/javascript">
	function lookupPayer(textId) 
	{	//operates the autocomplete for a textbox
		if(textId.length == 0) 
		{
			// Hide the suggestion box.
			$('#payer_suggestions').hide();
		}
		else 
		{
			$.post("payerId.php", {queryString: ""+textId+""}, function(data)
			{
				if(data.length >0) 
				{
					$('#payer_suggestions').show();
					$('#payer_autoSuggestionsList').html(data);
				}
			});
		}
	} // lookup
	
	function lookupPayee(textId) 
	{	//operates the autocomplete for a textbox
		if(textId.length == 0) 
		{
			// Hide the suggestion box.
			$('#payee_suggestions').hide();
		}
		else 
		{
			$.post("payeeId.php", {queryString: ""+textId+""}, function(data)
			{
				if(data.length >0) 
				{
					$('#payee_suggestions').show();
					$('#payee_autoSuggestionsList').html(data);
				}
			});
		}
	} // lookup
	
	function updateNamePayer(nameId)
	{	//fills out the contact name when the contact id is changed
		if(nameId.length == 0) 
		{
			// Hide the suggestion box.
			$('#payer_suggestions').hide();
		}
		else 
		{
			$.post("getnamePayer.php", {queryString: ""+nameId+""}, function(data)
			{
				if(data.length >0) 
				{
					$('#payer_suggestions').show();
					$('#payer_autoSuggestionsList').html(data);
				}
			});
		}
	}
	
	function updateNamePayee(nameId)
	{	//fills out the contact name when the contact id is changed
		if(nameId.length == 0) 
		{
			// Hide the suggestion box.
			$('#payee_suggestions').hide();
		}
		else 
		{
			$.post("getnamePayee.php", {queryString: ""+nameId+""}, function(data)
			{
				if(data.length >0) 
				{
					$('#payee_suggestions').show();
					$('#payee_autoSuggestionsList').html(data);
				}
			});
		}
	}
	
	function fillPayer(thisValue, thatValue) 
	{	//fills in the value when an autocomplete value is selected
		$('#name_payer').val(thisValue);
		$('#id_payer').val(thatValue);
		setTimeout("$('#payer_suggestions').hide();", 200);
	}
	
	function fillPayee(thisValue, thatValue) 
	{	//fills in the value when an autocomplete value is selected
		$('#name_payee').val(thisValue);
		$('#id_payee').val(thatValue);
		setTimeout("$('#payee_suggestions').hide();", 200);
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
	
	if (($_POST["action"] == "edit"))
	{
		echo "Cannot edit inspection listings currently<br >\n";
	}
	else	//if (($_POST["action"] == "")
	{
		print_inspections($contact, $start_page);
	}
	
	//bcmul, bcadd,
	//
}

closeDatabase();

?>

</body>
</html>
