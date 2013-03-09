<?php

if ('forms.php' == basename($_SERVER['SCRIPT_FILENAME']))
	die ('<h2>Direct File Access Prohibited</h2>');

function make_autocomplete($disp, $fill_val, $name, $id, $fillfunc, $suggestions, $autolist)
{
	echo "<div>\n";
	echo $disp . "\n";
	echo '<input type="text" autocomplete="off" value="';
	if ($fill_val != 0)
	{
		echo print_contact($fill_val);
	}
	else
	{
		$fill_val = '';
	}
	echo '" name="' . $name . '" id="' . $name . '" 
		onkeyup="lookupLastName(this.value, \'' . $fillfunc . '\', 
			$(\'#' . $suggestions . '\'), 
			$(\'#' . $autolist . '\'),
			&quot;$(\'#' . $name . '\')&quot;,
			&quot;$(\'#' . $id . '\')&quot;,
			&quot;$(\'#' . $suggestions . '\')&quot;);"
		 >' . "\n";
		 //onblur="&quot;$(\'#' . $suggestions . '\')&quot;.hide().delay(500);"
		 //TODO when the onblur is added, autocomplete fails to insert data
	echo '	<div id="' . $suggestions . '" style="display: none;">' . "\n";
	echo '		<div id="' . $autolist . '">' . "\n";
	echo '			&nbsp;' . "\n";
	echo '		</div>' . "\n";
	echo '	</div><br >' . "\n";
	echo '	 <input type="hidden" value="' . $fill_val . '" name="' . $id . '" id="' . $id . '">' . "\n";
	echo "</div>\n";
}

function payment_form($id, $payee_id, $payer_id, $amount, $earned, $paid, $comments, $category)
{
	global $mysql_db;
	echo "<div>\n";
	echo '<form method="POST" action="payments.php">' . "\n";
	echo "	<input type=\"hidden\" name=\"action\" value=\"apply\">\n";
	if ($id != 0)
		echo '<b>Payment ID: </b>Id number: <input type="text" value="' . $id . '" name="pay_id" readonly>' . "<br >\n";
	echo '	<b>Payment by: </b>';
	
	make_autocomplete('<b>Contact Name:</b> ', $payer_id, "name_payer",
		"id_payer", "fillNames", "payer_suggest", "payer_list");
	echo '	<b>Payment to: </b>' . "\n";
	make_autocomplete('<b>Contact Name:</b> ', $payee_id, "name_payee",
		"id_payee", "fillNames", "payee_suggest", "payee_list");
	
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
	echo '	<input type="submit" value="Refresh">' . "\n";
	echo '</form>' . "\n";
	echo '<form>' . "\n";
	echo '	<input type="button" value="Cancel" onclick="history.go(-1)">' . "\n";
	echo '</form>' . "\n";
	echo "</div>\n";
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
		echo print_contact($contact);
		echo "</h3><br >\n";
		echo '<a href="' . rootPageURL() . '/inspections.php">Go to all inspections</a>' . "<br >\n";
		$query = "SELECT * FROM inspections WHERE inspector=" . $contact;
		if (getPeriodComparison("datetime") != "")
		{
			$query = $query . " AND" . getPeriodComparison("datetime");
		}
		$query = $query . " ORDER BY id DESC LIMIT " . ($start_page*30) . ", " . ($start_page*30+30);
	}
			
	$payment_results = $mysql_db->query($query);
	
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
		echo print_prop($row['prop_id']);
		echo "</td>\n";
		
		echo "		<td>" . $row['type'] . "</td>\n";
		
		echo "		<td>";
		echo "<a href=\"" . rootPageURL() . "/inspections.php?contact=" . $row['inspector'] . "\"> ";
		echo print_contact($row['inspector']);
		echo "</a>";
		echo "</td>\n";
		
		echo "		<td>" . $row['datetime'] . "</td>\n";
		echo "		<td>$" . $row['price'] . "</td>\n";
		
		echo "		<td>";
		echo "<a href=\"" . rootPageURL() . "/payments.php?contact=" . $row['paid_by'] . "\"> ";
		echo print_contact($row['paid_by']);
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
	
	if ($payment_results->num_rows > 30)
	{
		$next_page = 1;
	}
	else
	{
		$next_page = 0;
	}
	
	
	if ($start_page > 0)
		echo '<a href="' . rootPageURL() . '/inspections.php?page=' . ($start_page-1) . '">Previous page</a>  ';
	if ($next_page == 1)
		echo '<a href="' . rootPageURL() . '/inspections.php?page=' . ($start_page+1) . '">Next page</a>' . "<br >\n";
	
	echo "	<form action=\"" . curPageURL() . "\" method=\"post\">\n" .
		 "		<input type=\"hidden\" name=\"action\" value=\"edit\">\n" .
		 "		<input type=\"hidden\" name=\"id\" value=\"0\">\n" .
		 "		<input type=\"submit\" value=\"Insert new inspection\"/>\n" .
		 "	</form>\n";
}

?>
