<?php
include("global.php");
start_my_session();
header('Content-type: text/html; charset=utf-8');

require("forms.php");

$contact = $_GET["contact"];
openDatabase();

if (is_numeric($contact) == FALSE)
	$contact = 0;
	
$start_page = $_GET["page"];
if (is_numeric($start_page) == FALSE)
	$start_page = 0;

?>

<!DOCTYPE HTML>
<html>
<head>
<title>Thermal Specialists Payment Details</title>
<link rel="stylesheet" type="text/css" href="css/payments.css" />
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
	
	function updateName(nameId, callId, suggestionBox, suggestionList, formName,formId, formSuggest)
	{	//fills out the contact name when the contact id is changed
		if(nameId.length == 0) 
		{
			// Hide the suggestion box.
			suggestionBox.hide();
		}
		else 
		{
			$.post("getnamePayer.php", 
				{queryString: ""+textId+"",
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
	}
	
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
	{	//apply the stuff
		$error = 0;
		
		$pay_id = $_POST["pay_id"];
		if (is_numeric($pay_id) == FALSE)
		{
			$pay_id = 0;
	//		$error = 1;
		}
		$payer_id = $_POST["id_payer"];
		if (is_numeric($payer_id) == FALSE)
		{
			$payer_id = 0;
	//		$error = 1;
		}
		$payee_id = $_POST["id_payee"];
		if (is_numeric($payee_id) == FALSE)
		{
			$payee_id = 0;
	//		$error = 1;
		}
		$amount_paid = $_POST["amount_paid"];
		if (is_numeric($amount_paid) == FALSE)
		{
			$amount_paid = 0;
	//		$error = 1;
		}
		
		
		$date_earned = $mysql_db->real_escape_string($_POST["date_earned"]);
		$date_paid = $mysql_db->real_escape_string($_POST["date_paid"]);
		$comments = $mysql_db->real_escape_string($_POST["comments"]);
		$categori = $mysql_db->real_escape_string($_POST["categori"]); 
			//mis-spelling allows listing of all categories after
			//a payment is created or updated
		if ($error == 1)
		{
			die ("Invalid arguments<br >\n");
		}
		else
		{
			if ($pay_id == 0)
			{
				$query = 'INSERT INTO `payments`' . 
					' (`pay_to`, `paid_by`, `amount_earned`, ' .
					'`date_earned`, `comments`, `category`,' . 
					'`date_paid`) VALUES ' . 
					"(" . $payee_id . ", " . $payer_id . ", " .
					$amount_paid . ", " .
					"'" . $date_earned . "', '" . $comments .
					"', '" . $categori . "', '" . $date_paid .
					"');";
				
				if ($mysql_db->query($query) != TRUE)
				{
					echo "Error: " . $mysql_db->error() . "<br >\n";
					exit(1);
					//die('Error: ' . $mysql_db->error());
				}
				echo "Payment entry created.<br >\n";
			}
			else
			{
				$query = 'UPDATE `payments` SET `pay_to` = ' .
					$payee_id . ', `paid_by` = ' . $payer_id . 
					', `amount_earned` = ' . $amount_paid . 
					', `date_earned` = \'' . $date_earned . 
					'\', `comments` = \'' . $comments . 
					'\', `category` = \'' . $categori .
					'\', `date_paid` = \'' . $date_paid . '\'' . 
					' WHERE `payment_id` = ' . $pay_id . ';';
				if ($mysql_db->query($query) != TRUE)
				{
					echo "Error: " . $mysql_db->error() . "<br >\n";
					exit(1);
					//die('Error: ' . $mysql_db->error());
				}
				echo "Payment entry updated.<br >\n";
			}
		}	
	//	echo $_POST["pay_id"] . "<br >\n";
	//	echo $_POST["name_payer"] . "<br >\n";
	//	echo $_POST["id_payer"] . "<br >\n";
	//	echo $_POST["name_payee"] . "<br >\n";
	//	echo $_POST["id_payee"] . "<br >\n";
	//	echo $_POST["amount_paid"] . "<br >\n";
	//	echo $_POST["date_earned"] . "<br >\n";
	//	echo $_POST["date_paid"] . "<br >\n";
	//	echo $_POST["comments"] . "<br >\n";
	}
	
	if ($_POST["action"] == "edit")
	{
		echo '<a href="' . rootPageURL() . 
			'/payments.php">Return to all payments</a>' . "<br >\n";
		
		$pay_id = $_POST["id"];
		if (is_numeric($pay_id) == FALSE)
		{
			$pay_id = 0;
			$error = 1;
		}
		if (($id_num == 0) && ($pay_id != 0))
		{
			$id_num = $pay_id;
		}
		
		if ($id_num != 0)
		{
			$query = "SELECT * FROM payments WHERE payment_id = " .
				$id_num;
			if (getPeriodComparison("date_earned") != "")
			{
				$query = $query . " AND" . getPeriodComparison("date_earned");
			}
		}
		else
		{
			$query = "SELECT * FROM payments WHERE payment_id = 0";
		}
		
		if ($id_num == 0)
		{
			$payee = $_POST["payee"];
			if (is_numeric($payee) == FALSE)
			{
				$payee = 0;
			}
			$payer = $_POST["payer"];
			if (is_numeric($payer) == FALSE)
			{
				$payer = 0;
			}
			payment_form(0, $payee, $payer, '', '', '', '', '');
		}
		else
		{
			$payment_results = $mysql_db->query($query);
				
			if($row = $payment_results->fetch_array(MYSQLI_BOTH))
			{	//function payment_form($id, $payee_id, $payer_id, 
				//$amount, $earned, $paid, $comments, $category)
				payment_form($row['payment_id'], $row['paid_by'],
					$row['pay_to'], $row['amount_earned'],
					$row['date_earned'], $row['date_paid'], 
					$row['comments'], $row['category']);
			}
			else
			{
				echo "Error retrieving payment details.<br >\n";
				echo '<form method="POST" action="' . rootPageURL() .
					'/payments.php" >' . "\n";
				echo '	<input type="submit" value="Cancel"/>' . "\n";
				echo '</form>' . "\n";
			}
		}
				
		if ($contact != 0)
		{
			echo "Paid balance: " . $balance . "<br>\n";
			echo "Unpaid balance: " . $due_balance . "<br>\n";
		}
	}
	
	echo "<div>\n";
	
	if ($contact != 0)
	{
		echo "<h3>Payment Details for: ";
		print_contact($contact);
		echo "</h3>\n<a href=\"" . rootPageURL() . "/payments.php\"> " . 
			" Back to all payments</a><br >\n\n";
		
		echo "<form action=\"" . rootPageURL() . "/contacts.php?contact=" . 
			$contact . "\" method=\"post\">\n" .
			"	<input type=\"hidden\" name=\"action\"" . 
			" value=\"view\">\n" .
			"	<input type=\"submit\" value=\"View  ";
		print_contact($contact);
		echo "'s Information\"/>\n" . "</form>\n";
	
		$query = "SELECT COUNT( *  ) AS `Rows` , `category`" .
			"FROM `payments` WHERE (paid_by = " . $contact .
			" OR pay_to = " . $contact . ")";
		if (getPeriodComparison("date_earned") != "")
		{
			$query = $query . " AND" . getPeriodComparison("date_earned");
		}
		$query = $query . " GROUP BY `category` ORDER BY `category`";
		$categories = $mysql_db->query($query);
		while($row = $categories->fetch_array(MYSQLI_BOTH))
		{
			if ($row['Rows'] != 0)
			{
			echo "<form action=\"" . rootPageURL() . 
				"/payments.php\" method=\"get\">\n" .
				'	<input type="hidden" name="contact"' .
				' value="' . $contact . "\">\n" .
				'	<input type="hidden" name="category"' .
				' value="' . $row['category'] . '">' . "\n" .
				'	<input type="submit" value=' . 
				'"View Category: ' . $row['category']  . " " .
				get_category_sum($contact, $row['category']) .
				'"/>' . "\n</form>\n";
			}
		}
		$balance = 0;
		$due_balance = 0;
		$category = $mysql_db->real_escape_string($_GET["category"]);
	}
	
	if (($_POST["action"] == "") || ($_POST["action"] == "apply"))
	{
		echo "<table border=\"1\">\n";
		echo "	<tr>\n";
		echo "		<th>ID#</th>\n";
		echo "		<th>Payment to</th>\n";
		echo "		<th>Paid by</th>\n";
		echo "		<th>Amount paid</th>\n";
		echo "		<th>Date earned</th>\n";
		echo "		<th>Date paid</th>\n";
		echo "		<th>Comments</th>\n";
		echo "		<th>Category</th>\n";
		echo "		<th>Invoice</th>\n";
		echo "	</tr>\n";
		
		if ($contact != 0)
		{
			$query = "SELECT * FROM payments WHERE (paid_by = " . $contact .
				" OR pay_to = " . $contact . ")";
			if ($category != "")
			{
				$query = $query . " AND `category` = '" .
					$category . "'";
			}
			if (getPeriodComparison("date_earned") != "")
			{
				$query = $query . " AND" . getPeriodComparison("date_earned");
			}
			$query = $query . " ORDER BY date_paid DESC LIMIT " . 
				($start_page*30) . ", " . ($start_page*30+30);
		}
		else
		{
			$query = "SELECT * FROM payments";
			if ($category != "")
			{
				$query = $query . " WHERE `category` = '" .
					$category . "'";
			}
			if (getPeriodComparison("date_earned") != "")
			{
				if ($category == "")
				{
					$query = $query . " WHERE" . 
						getPeriodComparison("date_earned");
				}
				else
				{
					$query = $query . " AND" . 
						getPeriodComparison("date_earned");
				}
			}
			$query = $query . " ORDER BY date_paid DESC LIMIT " . 
				($start_page*30) . ", " . ($start_page*30+30);
		}
		$payment_results = $mysql_db->query($query);
	
		$assets = 0.0;
		$liable = 0.0;
		$o_assets = 0.0;
		$o_liable = 0.0;	
	
		while($row = $payment_results->fetch_array(MYSQLI_BOTH))
		{
			echo "	<tr>\n";
			
			echo "		<td>";// . $row['payment_id'];
			echo "	<form action=\"" . rootPageURL() . 
				"/payments.php\" method=\"post\">\n" .
				"		<input type=\"hidden\" name=" . 
				"\"action\" value=\"edit\">\n" .
				"		<input type=\"hidden\" name=" . 
				"\"id\" value=\"" . $row['payment_id'] . "\">\n" .
				"		<input type=\"submit\" value=" . 
				"\"Edit\"/>\n" .
				"	</form>\n";
			echo "</td>\n";
			
			echo "		<td>";
			echo "<a href=\"" . rootPageURL() . 
				"/payments.php?contact=" . $row['pay_to'] . "\"> ";
			print_contact($row['pay_to']);
			echo "</a>";
			echo "</td>\n";
			
			echo "		<td>";
			echo "<a href=\"" . rootPageURL() . 
				"/payments.php?contact=" . $row['paid_by'] . "\"> ";
			print_contact($row['paid_by']);
			echo "</a>";
			echo "</td>\n";
			
			echo "		<td>";
			if ($contact != 0)
			{
				if ($row['date_paid'] != "0000-00-00")
				{
					if ($row['pay_to'] == $contact)
					{
						echo "+";
					}
					if ($row['paid_by'] == $contact)
					{
						echo "-";
					}
				}
				else
				{
					if ($row['pay_to'] == $contact)
					{
						echo "+";
					}
					else
					{
						echo "-";
					}
				}
			}
			echo "$" . $row['amount_earned'] . "</td>\n";
			
			echo "		<td>" . $row['date_earned'] . "</td>\n";
			echo "		<td>" . $row['date_paid'] . "</td>\n";
			echo "		<td>" . $row['comments'] . "</td>\n";
			echo "		<td>" . $row['category'] . "</td>\n";
			
			if ($contact != 0)
			{
				if ($row['date_paid'] != "0000-00-00")
				{
					if ($row['pay_to'] == $contact)
					{
						$assets += $row['amount_earned'];
					}
					if ($row['paid_by'] == $contact)
					{
						$liable += $row['amount_earned'];
					}
				}
				else
				{
					if ($row['pay_to'] == $contact)
					{
						$o_assets += $row['amount_earned'];
					}
					else
					{
						$o_liable += $row['amount_earned'];
					}
				}
			}
	
			if ($row['invoice'] == "")
			{
				echo "		<td>Not available</td>\n";
			}
			else
			{
				echo "		<td>" . '<a href="' . rootPageURL() .
					'/invoices/' . $row['invoice'] . 
					'" target="_blank">Download</a></td>' . "\n";
			}
			echo "	</tr>\n";
		}
		
		echo "</table><br>\n";
		
		if ($contact != 0)
		{
			if ($start_page > 0)
				echo '<a href="' . rootPageURL() . 
					'/payments.php?contact=' . $contact . 
					'&page=' . ($start_page-1) . 
					'">Previous page</a>  ';
			echo '<a href="' . rootPageURL() . '/payments.php?contact=' .
				$contact . '&page=' . ($start_page+1) . 
				'">Next page</a>' . "<br >\n";
			
			echo "Assets: $" . $assets . "<br>\n";
			echo "Outstanding assets: $" . $o_assets . "<br>\n";
			echo "Liabilities: $" . $liable . "<br>\n";
			echo "Outstanding liabilities: $" . $o_liable . "<br>\n";
			echo "Net Worth: $" . ($assets - $liable) . " ($";
			echo ($assets + $o_assets - $liable - $o_liable). ")<br>\n";
			
		}
		else
		{
			if ($start_page > 0)
				echo '<a href="' . rootPageURL() . 
					'/payments.php?page=' . ($start_page-1) . 
					'">Previous page</a>  ';
			echo '<a href="' . rootPageURL() . '/payments.php?page=' . 
				($start_page+1) . '">Next page</a>' . "<br >\n";

			echo "	<form action=\"" . rootPageURL() . 
				"/payments.php\" method=\"post\">\n" .
				"		<input type=\"hidden\" name=" . 
				"\"action\" value=\"edit\">\n" .
				"		<input type=\"hidden\" name=" . 
				"\"id\" value=\"0\">\n" .
				"		<input type=\"submit\" value=" .
				"\"Insert new payment\"/>\n" .
				"	</form>\n";	
		}
	}
	
	echo "</div>\n";
	//bcmul, bcadd,
	//
}

closeDatabase();

?>

</body>
</html>
