<?php
include("global.php");
start_my_session();
header('Content-type: text/html; charset=utf-8');

$contact_list = $_GET["contact"];

if (is_numeric($contact_list) == FALSE)
	$contact_list = 0;
	
$start_page = $_GET["page"];
if (is_numeric($start_page) == FALSE)
	$start_page = 0;

$details = $_GET["details"];
if (is_numeric($details) == FALSE)
	$details = 0;

$database = openDatabase();
?>

<!DOCTYPE HTML SYSTEM>
<html>
<head>
<title>Thermal Specialists Inspection Location List</title>
</head>

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


<body>

<?php

//make sure the user is logged in properly
login_code();
login_button($database);

echo 'This page lists locations where inspections have been performed. A location can be listed even though a non-building inspection was performed.' . "<br >\n";

echo '<a href="' . bottomPageURL() . '">Return to main</a>' . "<br >\n";

if (($_POST["action"] == "new"))
{
	
	echo "Cannot currently create new locations<br >\n";
}
else //if (($_POST["action"] == "") || ($_POST["action"] == "apply"))
{
	if ($details == 0)
	{
		$query = "SELECT * FROM properties ORDER BY id DESC LIMIT " . ($start_page*30) . ", " . ($start_page*30+30);
	}
	else
	{
		$query = "SELECT * FROM properties WHERE id = " .$details . " LIMIT 1";
	}
		
	$payment_results = mysql_query($query, $database);
	
	if ($details == 0)
	{
		echo "<table border=\"1\">\n";
		echo "	<tr>\n";
		echo "		<th>ID#</th>\n";
		echo "		<th>Address</th>\n";
		echo "		<th>City</th>\n";
		echo "		<th>State</th>\n";
		echo "		<th>Zip Code</th>\n";
		echo "		<th>Description</th>\n";
		echo "	</tr>\n";
			
		while($row = mysql_fetch_array($payment_results))
		{
			echo "	<tr>\n";
			
			echo "		<td>";// . $row['payment_id'];
			echo "<a href=\"" . bottomPageURL() . "/properties.php?details=" . $row['id'] . "\">Details</a>";
			echo "</td>\n";
			
			echo "</td>\n";
			
			echo "		<td>" . $row['address'] . "</td>\n";
			echo "		<td>" . $row['city'] . "</td>\n";
			echo "		<td>" . $row['state'] . "</td>\n";
			echo "		<td>" . $row['zip'] . "</td>\n";
			echo "		<td>" . $row['description'] . "</td>\n";
				
			echo "	</tr>\n";
		}
		
		echo "</table><br>\n";
		
		if ($start_page > 0)
			echo '<a href="' . bottomPageURL() . '/properties.php?page=' . ($start_page-1) . '">Previous page</a>  ';
		echo '<a href="' . bottomPageURL() . '/properties.php?page=' . ($start_page+1) . '">Next page</a>' . "<br >\n";
		
		echo "	<form action=\"" . bottomPageURL() . "properties.php\" method=\"post\">\n" .
			 "		<input type=\"hidden\" name=\"action\" value=\"new\">\n" .
			 "		<input type=\"hidden\" name=\"id\" value=\"0\">\n" .
			 "		<input type=\"submit\" value=\"Insert new location\"/>\n" .
			 "	</form>\n";
	}
	else
	{	//detailed information for a single property
		//lists all property information and includes history of all inspections done
		echo '<a href="' . bottomPageURL() . '/properties.php">Return to all properties</a>' . "<br >\n";
		$row = mysql_fetch_array($payment_results);
		echo "Address: " . "<br >\n" . $row['address'] . "<br >\n" .
			 $row['city'] . ", " . $row['state'] . " " . $row['zip'] . "<br >\n" .
			 $row['description'];
		//print all inspections done here
		$query = "SELECT * FROM inspections WHERE prop_id = " .$row['id'] . " LIMIT 1";
		$payment_results = mysql_query($query, $database);

		//also located in inspections.php (move to a function later)
		//displaying the location should be optional
		echo "<table border=\"1\">\n";
		echo "	<tr>\n";
		echo "		<th>ID#</th>\n";
//		echo "		<th>Location</th>\n";
		echo "		<th>Inspection type</th>\n";
		echo "		<th>Inspector</th>\n";
		echo "		<th>Date Time Group</th>\n";
		echo "		<th>Price</th>\n";
		echo "		<th>Paid by</th>\n";
		echo "		<th>Comments</th>\n";
		echo "	</tr>\n";
		
		while($row = mysql_fetch_array($payment_results))
		{
			echo "	<tr>\n";
			
			echo "		<td>";// . $row['payment_id'];
			echo "	<form action=\"" . bottomPageURL() . "inspections.php\" method=\"post\">\n" .
				 "		<input type=\"hidden\" name=\"action\" value=\"edit\">\n" .
				 "		<input type=\"hidden\" name=\"id\" value=\"" . $row['payment_id'] . "\">\n" .
				 "		<input type=\"submit\" value=\"Edit\"/>\n" .
				 "	</form>\n";
			echo "</td>\n";
			
//			echo "		<td>";
//			print_prop($row['prop_id'],$database);
//			echo "</td>\n";
			
			echo "		<td>" . $row['type'] . "</td>\n";
			
			echo "		<td>";
			echo "<a href=\"" . bottomPageURL() .  "/contacts.php?contact=" . $row['inspector'] . "\"> ";
			print_contact($row['inspector'], $database);
			echo "</a>";
			echo "</td>\n";
			
			echo "		<td>" . $row['datetime'] . "</td>\n";
			echo "		<td>$" . $row['price'] . "</td>\n";
			
			echo "		<td>";
			echo "<a href=\"" . bottomPageURL() . "/payments.php?contact=" . $row['paid_by'] . "\"> ";
			print_contact($row['paid_by'], $database);
			echo "</a>";
			echo "</td>\n";
			
			echo "		<td>" . $row['comments'] . "</td>\n";
				
			echo "	</tr>\n";
			
			if ($contact_list != 0)
			{
				if ($row['date_paid'] != "0000-00-00")
				{
					if ($row['pay_to'] == $contact_list)
					{
						$balance += $row['amount_earned'];
					}
					if ($row['paid_by'] == $contact_list)
					{
						$balance -= $row['amount_earned'];
					}
				}
				else
				{
					if ($row['pay_to'] == $contact_list)
					{
						$due_balance += $row['amount_earned'];
					}
					else
					{
						$due_balance -= $row['amount_earned'];
					}
				}
			}
		}
	
		echo "</table><br>\n";
	
		if ($start_page > 0)
			echo '<a href="' . bottomPageURL() . '/inspections.php?page=' . ($start_page-1) . '">Previous page</a>  ';
		echo '<a href="' . bottomPageURL() . '/inspections.php?page=' . ($start_page+1) . '">Next page</a>' . "<br >\n";

	}
}

closeDatabase($database);

function location_form($id, $payee_id, $payer_id, $amount, $earned, $paid, $comments, $database)
{
	echo '<form method="POST" action="' . bottomPageURL() . '/payments.php">' . "\n";
	echo "	<input type=\"hidden\" name=\"action\" value=\"apply\">\n";
	if ($id != 0)
		echo '<b>Payment ID: </b>Id number: <input type="text" value="' . $id . '"name=pay_id readonly=\"readonly\">' . "<br >\n";
	echo '<b>Payment by: </b>Contact Name: <input type="text" autocomplete="off" value="';
	if ($payee_id != 0)
	{
		print_contact($payee_id, $database);
	}
	else
	{
		$payee_id = '';
	}
	echo '" name="name_payer" id="name_payer" onkeyup="lookupPayer(this.value);" onblur="fillPayer();" >' . "\n";
	echo '	 Contact ID #: ' . "\n";
	echo '	 <input type="text" value="' . $payee_id . '" name="id_payer" id="id_payer" onkeyup="updateNamePayer(this.value);" onblur="fillPayer();" ><br >' . "\n";
	echo '	<div id="payer_suggestions" style="display: none;">' . "\n";
	echo '		<div id="payer_autoSuggestionsList">' . "\n";
	echo '			&nbsp;' . "\n";
	echo '		</div>' . "\n";
	echo '	</div><br >' . "\n";
	echo '	<b>Payment to: </b>Contact Name: <input type="text" autocomplete="off" value="';
	if ($payer_id != 0)
	{
		print_contact($payer_id, $database);
	}
	else
	{
		$payer_id = '';
	}
	echo '" name="name_payee" id="name_payee"  onkeyup="lookupPayee(this.value);" onblur="fillPayee();" >' . "\n";
	echo '	 Contact ID #: ' . "\n";
	echo '	 <input type="text" value="' . $payer_id . '" name="id_payee" id="id_payee"  onkeyup="lookupPayee(this.value);" onblur="fillPayee();" ><br >' . "\n";
	echo '	<div id="payee_suggestions" style="display: none;">' . "\n";
	echo '		<div id="payee_autoSuggestionsList">' . "\n";
	echo '			&nbsp;' . "\n";
	echo '		</div>' . "\n";
	echo '	</div><br >' . "\n";
	echo '	<b>Amount of Payment: </b>Dollar amount: $<input type="text" value="' . $amount . '" name="amount_paid" id="amount_paid" ><br >' . "\n";
	echo '	<b>Date Earned: </b>Date (YYYY-MM-DD): <input type="text" value="' . $earned . '" name="date_earned" id="date_earned" ><br >' . "\n";
	echo '	<b>Date Paid: </b>Date (YYYY-MM-DD): <input type="text" value="' . $paid . '" name="date_paid" id="date_paid" ><br >' . "\n";
	echo '	<b>Comments: </b>Comments: <input type="text" value="' . $comments . '" name="comments" id="comments" size=127 ><br >' . "\n";
	echo '	<input type="submit" value="Update"/>' . "\n";
	echo '</form>' . "\n";
	echo '<form method="POST" action="' . bottomPageURL() . 'payments.php">' . "\n";
	echo '	<input type="hidden" name="action" value="edit">' . "\n";
	echo '	<input type="hidden" name="id" value=' . $id . ">\n";
	echo '	<input type="submit" value="Refresh"/>' . "\n";
	echo '</form>' . "\n";
	echo '<form method="POST" action="' . bottomPageURL() . 'payments.php" >' . "\n";
	echo '	<input type="submit" value="Cancel"/>' . "\n";
	echo '</form>' . "\n";
}

?>

</body>
</html>
