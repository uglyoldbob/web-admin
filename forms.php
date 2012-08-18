<?php

if ('forms.php' == basename($_SERVER['SCRIPT_FILENAME']))
	die ('<h2>Direct File Access Prohibited</h2>');

function job_form()
{
	echo "<div>\n";
	echo '<form method="POST" action="jobs.php">' . "\n";
	echo "	<input type=\"hidden\" name=\"action\" value=\"apply\">\n";
	
	echo '	<b>Customer Name: </b> <input type="text" name="customer" id="customer" size=75 ><br >' . "\n";
	echo '	<b>Comments: </b> <input type="text" name="comments" id="comments" size=500 ><br >' . "\n";
	
	echo "	<input type=\"submit\" value=\"Create this job\"/>\n";
	echo '</form>' . "\n";
	echo "</div>\n";
}

function payment_form($id, $payee_id, $payer_id, $amount, $earned, $paid, $comments, $category)
{
	global $mysql_db;
	echo "<div>\n";
	echo '<form method="POST" action="payments.php">' . "\n";
	echo "	<input type=\"hidden\" name=\"action\" value=\"apply\">\n";
	if ($id != 0)
		echo '<b>Payment ID: </b>Id number: <input type="text" value="' . $id . '"name=pay_id readonly=\"readonly\">' . "<br >\n";
	echo '<b>Payment by: </b>Contact Name: <input type="text" autocomplete="off" value="';
	if ($payee_id != 0)
	{
		print_contact($payee_id);
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
		print_contact($payer_id);
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
	echo '	<b>Comments: </b> <input type="text" value="' . $comments . '" name="comments" id="comments" size=127 ><br >' . "\n";
	echo '	<b>Category: </b> <select name="categori">' . "\n";

	$query = "SELECT COUNT( *  ) AS `Rows` , `category`" .
		"FROM `payments` GROUP BY `category` ORDER BY `category`";
	$categories = $mysql_db->query($query);
	while($row = $categories->fetch_array(MYSQLI_BOTH))
	{
		if ($category == $row['category'])
		{
		echo '		<option selected value="' . $row['category'] .
			'">' . $row['category'] . "</option>\n";
		
		}
		else
		{
		echo '		<option value="' . $row['category'] .
			'">' . $row['category'] . "</option>\n";
		}
	}	

	echo '	</select>' . "\n<br>";
	echo '	<input type="submit" value="Update"/>' . "\n";
	echo '</form>' . "\n";
	echo '<form method="POST" action="payments.php">' . "\n";
	echo '	<input type="hidden" name="action" value="edit">' . "\n";
	echo '	<input type="hidden" name="id" value=' . $id . ">\n";
	echo '	<input type="submit" value="Refresh"/>' . "\n";
	echo '</form>' . "\n";
	echo '<form method="POST" action="payments.php" >' . "\n";
	echo '	<input type="submit" value="Cancel"/>' . "\n";
	echo '</form>' . "\n";
	echo "TODO: Cancel button should take you to the previous page (it will not use javascript)<br>\n";
	echo "</div>\n";
}

function contact_form($id, $last_name, $first_name, $classify, $eligible, $ssn, $mobile, $home, $other,
	$website, $email, $street, $city, $state, $zip)
{	//TODO: implement drop down box with a yes/no
	if ($_POST["action"] != "edit")
	{
		echo "	<form action=\"contacts.php?contact=" . $id . "\" method=\"post\">\n" .
			 "		<input type=\"hidden\" name=\"action\" value=\"edit\">\n" .
			 "		<input type=\"submit\" value=\"Edit\"/>\n" .
			 "	</form>\n";
		
		echo "<form action=\"contacts.php?contact=" . $id . "\" method=\"post\">\n" .
			 "	<input type=\"hidden\" name=\"action\" value=\"update\"><br>\n";
				
		echo "	<table border=\"1\" width=\"50%\">\n";
	}
	else
	{
		echo "<form action=\"contacts.php?contact=" . $id . "\" method=\"post\">\n" .
			 "	<input type=\"hidden\" name=\"action\" value=\"update\"><br>\n";
				
		echo "	<table border=\"1\" width=\"50%\">\n";
		if ($id != 0)
		{
			echo "	<input type=\"submit\" value=\"Update\"/>\n" .
				 "</form>\n";
		}
		else
		{
			echo "	<input type=\"submit\" value=\"Insert\"/>\n" .
				 "</form>\n";
		}
	}
		
	if ($id != 0)
	{	
		echo "		<tr>\n";
		echo "			<td>Id number</td>\n";
		echo "			<td>";
		echo "<input type=\"text\" name=\"id\" value=\"" . $id . "\" size=\"70\" readonly=\"readonly\" >";
		echo "</td>\n";
		echo "		</tr>\n";
	}
				
	echo "		<tr>\n";
	echo "			<td>Last Name</td>\n";
	echo "			<td>";
	echo "<input type=\"text\" name=\"last_name\" value=\"" . $last_name . "\" size=\"70\"";
	if ($_POST["action"] != "edit")
		echo "readonly=\"readonly\"";
	echo " >";
	echo "</td>\n";
	echo "		</tr>\n";
				
	echo "		<tr>\n";
	echo "			<td>First Name</td>\n";
	echo "			<td>";
	echo "<input type=\"text\" name=\"first_name\" value=\"" . $first_name . "\" size=\"70\"";
	if ($_POST["action"] != "edit")
		echo "readonly=\"readonly\"";
	echo " >";
	echo "</td>\n";
	echo "		</tr>\n";
				
	echo "		<tr>\n";
	echo "			<td>Classification</td>\n";
	echo "			<td>";
	echo "<input type=\"text\" name=\"classify\" value=\"" . $classify . "\" size=\"70\"";
	if ($_POST["action"] != "edit")
		echo "readonly=\"readonly\"";
	echo " >";
	echo "</td>\n";
	echo "		</tr>\n";
				
	echo "		<tr>\n";
	echo "			<td>Eligible for payment</td>\n";
	echo "			<td>";
	echo "<input type=\"text\" name=\"eligible\" value=\"" . $eligible . "\" size=\"70\"";
	if ($_POST["action"] != "edit")
		echo "readonly=\"readonly\"";
	echo " >";
	echo "</td>\n";
	echo "		</tr>\n";
				
	echo "		<tr>\n";
	echo "			<td>SSN</td>\n";
	echo "			<td>";
	echo "<input type=\"text\" name=\"ssn\" value=\"" . $ssn . "\" size=\"70\"";
	if ($_POST["action"] != "edit")
		echo "readonly=\"readonly\"";
	echo " >";
	echo "</td>\n";
	echo "		</tr>\n";
				
	echo "		<tr>\n";
	echo "			<td>Phone(mobile)</td>\n";
	echo "			<td>";
	echo "<input type=\"text\" name=\"mobile\" value=\"" . $mobile . "\" size=\"70\"";
	if ($_POST["action"] != "edit")
		echo "readonly=\"readonly\"";
	echo " >";
	echo "</td>\n";
	echo "		</tr>\n";
				
	echo "		<tr>\n";
	echo "			<td>Phone(home)</td>\n";
	echo "			<td>";
	echo "<input type=\"text\" name=\"home\" value=\"" . $home . "\" size=\"70\"";
	if ($_POST["action"] != "edit")
		echo "readonly=\"readonly\"";
	echo " >";
	echo "</td>\n";
	echo "		</tr>\n";
				
	echo "		<tr>\n";
	echo "			<td>Phone(other)</td>\n";
	echo "			<td>";
	echo "<input type=\"text\" name=\"other\" value=\"" . $other . "\" size=\"70\"";
	if ($_POST["action"] != "edit")
		echo "readonly=\"readonly\"";
	echo " >";
	echo "</td>\n";
	echo "		</tr>\n";
				
	echo "		<tr>\n";
	echo "			<td>Website</td>\n";
	echo "			<td>";
	echo "<input type=\"text\" name=\"website\" value=\"" . $website . "\" size=\"70\"";
	if ($_POST["action"] != "edit")
		echo "readonly=\"readonly\"";
	echo " >";
	echo "</td>\n";
	echo "		</tr>\n";
				
	echo "		<tr>\n";
	echo "			<td>E-mail address</td>\n";
	echo "			<td>";
	echo "<input type=\"text\" name=\"email\" value=\"" . $email . "\" size=\"70\"";
	if ($_POST["action"] != "edit")
		echo "readonly=\"readonly\"";
	echo " >";
	echo "</td>\n";
	echo "		</tr>\n";
				
	echo "		<tr>\n";
	echo "			<td>Street Address</td>\n";
	echo "			<td>";
	echo "<input type=\"text\" name=\"street\" value=\"" . $street . "\" size=\"70\"";
	if ($_POST["action"] != "edit")
		echo "readonly=\"readonly\"";
	echo " >";
	echo "</td>\n";
	echo "		</tr>\n";
				
	echo "		<tr>\n";
	echo "			<td>City</td>\n";
	echo "			<td>";
	echo "<input type=\"text\" name=\"city\" value=\"" . $city . "\" size=\"70\"";
	if ($_POST["action"] != "edit")
		echo "readonly=\"readonly\"";
	echo " >";
	echo "</td>\n";
	echo "		</tr>\n";
				
	echo "		<tr>\n";
	echo "			<td>State</td>\n";
	echo "			<td>";
	echo "<input type=\"text\" name=\"state\" value=\"" . $state . "\" size=\"70\"";
	if ($_POST["action"] != "edit")
		echo "readonly=\"readonly\"";
	echo " >";
	echo "</td>\n";
	echo "		</tr>\n";
				
	echo "		<tr>\n";
	echo "			<td>Zipcode</td>\n";
	echo "			<td>";
	echo "<input type=\"text\" name=\"zip\" value=\"" . $zip . "\" size=\"70\"";
	if ($_POST["action"] != "edit")
		echo "readonly=\"readonly\"";
	echo " >";
	echo "</td>\n";
	echo "		</tr>\n";
	echo "	</table>\n";

	if ($_POST["action"] != "edit")
	{
		echo "	<form action=\"contacts.php?contact=" . $id . "\" method=\"post\">\n" .
			 "		<input type=\"hidden\" name=\"action\" value=\"edit\">\n" .
			 "		<input type=\"submit\" value=\"Edit\"/>\n" .
			 "	</form>\n";
	}
	else
	{
		if ($id != 0)
		{
			echo "	<input type=\"submit\" value=\"Update\"/>\n" .
				 "</form>\n";
		}
		else
		{
			echo "	<input type=\"submit\" value=\"Insert\"/>\n" .
				 "</form>\n";
		}
	}
}

function print_inspections($contact, $start_page)
{	//prints inspections done by a specific contact id number
		//check for filtering 
	global $mysql_db;
	if ($contact == 0)
	{
		$query = "SELECT * FROM inspections";
		if (getPeriodComparison("datetime") != "")
		{
			$query = $query . " AND" . getPeriodComparison("datetime");
		}
		$query = $query . " ORDER BY id DESC LIMIT " . ($start_page*30) . ", " . ($start_page*30+30);
	}
	else
	{
		echo "<h3>Inspections done by ";
		print_contact($contact);
		echo "</h3><br >\n";
		echo '<a href="' . rootPageURL() . '/inspections.php">Go to all inspections</a>' . "<br >\n";
		$query = "SELECT * FROM inspections WHERE inspector=" . $contact;
		if (getPeriodComparison("datetime") != "")
		{
			$query = $query . " AND" . getPeriodComparison("datetime");
		}
		$query = $query . " ORDER BY id DESC LIMIT " . ($start_page*30) . ", " . ($start_page*30+30);
	}
			
	$payment_results = $mysql_db->_query($query);
	
	echo "<table border=\"1\">\n";
	echo "	<tr>\n";
	echo "		<th>ID#</th>\n";
	echo "		<th>Location</th>\n";
	echo "		<th>Inspection type</th>\n";
	echo "		<th>Inspector</th>\n";
	echo "		<th>Date Time Group</th>\n";
	echo "		<th>Price</th>\n";
	echo "		<th>Paid by</th>\n";
	echo "		<th>Comments</th>\n";
	echo "		<th>Report</th>\n";
	echo "	</tr>\n";
	
	while($row = $payment_results->fetch_array(MYSQLI_BOTH))
	{
		echo "	<tr>\n";
		
		echo "		<td>";// . $row['payment_id'];
		echo "	<form action=\"" . curPageURL() . "\" method=\"post\">\n" .
			 "		<input type=\"hidden\" name=\"action\" value=\"edit\">\n" .
			 "		<input type=\"hidden\" name=\"id\" value=\"" . $row['id'] . "\">\n" .
			 "		<input type=\"submit\" value=\"Edit\"/>\n" .
			 "	</form>\n";
		echo "</td>\n";
		
		echo "		<td>";
		print_prop($row['prop_id']);
		echo "</td>\n";
		
		echo "		<td>" . $row['type'] . "</td>\n";
		
		echo "		<td>";
		echo "<a href=\"" . rootPageURL() . "/inspections.php?contact=" . $row['inspector'] . "\"> ";
		print_contact($row['inspector']);
		echo "</a>";
		echo "</td>\n";
		
		echo "		<td>" . $row['datetime'] . "</td>\n";
		echo "		<td>$" . $row['price'] . "</td>\n";
		
		echo "		<td>";
		echo "<a href=\"" . rootPageURL() . "/payments.php?contact=" . $row['paid_by'] . "\"> ";
		print_contact($row['paid_by']);
		echo "</a>";
		echo "</td>\n";
		
		echo "		<td>" . $row['comments'] . "</td>\n";
		
		if ($row['report'] == '')
		{
			echo "	<td>Not available</td>\n";
		}
		else
		{
			echo "		<td>" . '<a href="' . rootPageURL() . '/reports/' . $row['report'] . '" target="_blank">Download</a></td>' . "\n";
		}
		
		echo "	</tr>\n";	
	}
	
	echo "</table><br>\n";
	
	if ($start_page > 0)
		echo '<a href="' . rootPageURL() . '/inspections.php?page=' . ($start_page-1) . '">Previous page</a>  ';
	echo '<a href="' . rootPageURL() . '/inspections.php?page=' . ($start_page+1) . '">Next page</a>' . "<br >\n";
	
	echo "	<form action=\"" . curPageURL() . "\" method=\"post\">\n" .
		 "		<input type=\"hidden\" name=\"action\" value=\"edit\">\n" .
		 "		<input type=\"hidden\" name=\"id\" value=\"0\">\n" .
		 "		<input type=\"submit\" value=\"Insert new inspection\"/>\n" .
		 "	</form>\n";
}

?>
