<?php
/**
* Simple autoloader, so we don't need Composer just for this.
*/
class Autoloader
{
    public static function register()
    {
        spl_autoload_register(function ($class) 
		{
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
			$file = str_replace('_', DIRECTORY_SEPARATOR, $file);
            if (file_exists($file)) 
			{
                require $file;
                return true;
            }
            return false;
        });
    }
}
Autoloader::register();

require_once("webAdmin/exceptions.php");
require_once("global.php");

if (!headers_sent())
{
	header('Content-type: text/html; charset=utf-8');
}

?>
<!DOCTYPE HTML>
<html>
<head>
<?php

try
{
	$config = parse_ini_file("config.ini");
	test_config($config);

	global $mysql_db;
	$mysql_db = openDatabase($config);
	
	$cust_session = new \webAdmin\session($config, $mysql_db, "sessions");
	start_my_session();	//start php session

?>
	<title>Payment Details: <?php sitename($config)?></title>
	<?php do_css($config) ?>
	</head>

	<body>

	<script type="text/javascript" src="jscript.js"></script>
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
	$currentUser = new \webAdmin\user($config, $mysql_db, "users");
	$currentUser->certificate_tables("root_ca", "intermediate_ca", "user_certs");

	$currentUser->show_register_certificate_button();
	$currentUser->require_login_or_registered_certificate();

	if (!(array_key_exists("contact", $_GET)))
	{
		$_GET["contact"] = "0";
	}
	$contact = $_GET["contact"];

	if (is_numeric($contact) == FALSE)
	{
		$contact = 0;
	}

	if (!(array_key_exists("page", $_GET)))
	{
		$_GET["page"] = "0";
	}
	$start_page = $_GET["page"];
	if (is_numeric($start_page) == FALSE)
	{
		$start_page = 0;
	}
	
	do_top_menu(1, $config);
	if ($_POST["action"] == "apply")
	{	//apply the stuff
		$error = 0;
	
		if (isset($_POST["pay_id"]))
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
		if (isset($_POST["categori"]))
			$categori = $mysql_db->real_escape_string($_POST["categori"]); 
			//mis-spelling allows listing of all categories after
			//a payment is created or updated
		if ($error == 1)
		{
			throw new Exception ("Invalid arguments<br >\n");
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
					throw new Exception("Error: " . $mysql_db->error . "<br >\n");
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
					throw new Exception("Error: " . $mysql_db->error . "<br >\n");
				}
				if (isset($_POST["upload_invoice"]))
				{
					$imageFileType = pathinfo($_FILES["new_invoice"]["name"],PATHINFO_EXTENSION);
					$target_file = "invoices/" . $pay_id . "." . $imageFileType;
					echo "An upload was selected: " . $target_file . "<br>\n";
					echo $_FILES["new_invoice"]["name"] . "<br>\n";
					echo $imageFileType . "<br>\n";
					if (move_uploaded_file($_FILES["new_invoice"]["tmp_name"], $target_file)) {
					}
					else
					{
						echo "Error uploading invoice<br>\n";
					}
					$query = 'UPDATE `payments` SET `invoice` = \'' . $target_file .
						'\' WHERE `payment_id` = \'' . $pay_id . '\';';
					if ($mysql_db->query($query) != TRUE)
					{
						throw new Exception("Error: " . $mysql_db->error . "<br >\n");
					}
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
	
	if ($_POST["action"] == "copy")
	{
		$_SESSION['payment_reference'] = $mysql_db->real_escape_string($_POST["id"]);
		$_POST["action"] = "apply";
	}
	if ($_POST["action"] == "unselect")
	{
		unset($_SESSION['payment_reference']);
		$_POST["action"] = "apply";
	}
	
	if (isset($_SESSION['payment_reference']))
	{
		echo "Payment id " . $_SESSION['payment_reference'] . " is selected<br>\n";
		echo "    <form action=\"" . rootPageURL($config) . 
			"/payments.php\" method=\"post\">\n" .
			"		<input type=\"hidden\" name=" . 
			"\"action\" value=\"unselect\">\n" .
			"		<input class=\"buttons\" type=\"submit\" value=" . 
			"\"Unselect\"/>\n" .
			"	</form>\n";
	}

	if ($_POST["action"] == "edit")
	{
		$pay_id = $_POST["id"];
		if (is_numeric($pay_id) == FALSE)
		{
			$pay_id = 0;
			$error = 1;
		}
		if (isset($pay_id))
		{
			$id_num = $pay_id;
		}
		else
		{
			$id_num = 0;
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
			if (isset($_POST["payee"]))
				$payee = $_POST["payee"];
			else
				$payee = "";
			if (is_numeric($payee) == FALSE)
			{
				$payee = 0;
			}
			if (isset($_POST["payer"]))
				$payer = $_POST["payer"];
			else
				$payer = "";
			if (is_numeric($payer) == FALSE)
			{
				$payer = 0;
			}
			\webAdmin\finance::payment_form(0, $payee, $payer, '', '', '', '', '');
		}
		else
		{
			$payment_results = $mysql_db->query($query);
			
			if($row = $payment_results->fetch_array(MYSQLI_BOTH))
			{	//function payment_form($id, $payee_id, $payer_id, 
				//$amount, $earned, $paid, $comments, $category)
				\webAdmin\finance::payment_form($row['payment_id'], $row['pay_to'],
					$row['paid_by'], $row['amount_earned'],
					$row['date_earned'], $row['date_paid'], 
					$row['comments'], $row['category']);
			}
			else
			{
				echo "Error retrieving payment details.<br >\n";
				echo '<form method="POST" action="' . rootPageURL($config) .
					'/payments.php" >' . "\n";
				echo '	<input class="buttons" type="submit" value="Cancel"/>' . "\n";
				echo '</form>' . "\n";
			}
		}
			
		if ($contact != 0)
		{
			echo "Paid balance: " . $balance . "<br>\n";
			echo "Unpaid balance: " . $due_balance . "<br>\n";
		}
	}

	echo "<div style=\"display:block;\">\n";

	if ($contact != 0)
	{
		echo "<h3>Payment Details for: ";
		echo print_contact($contact);
		echo "</h3>\n";
	
		echo "<form action=\"" . rootPageURL($config) . "/contacts.php?contact=" . 
			$contact . "\" method=\"post\">\n" .
			"	<input type=\"hidden\" name=\"action\"" . 
			" value=\"view\">\n" .
			"	<input class=\"buttons\" type=\"submit\" value=\"View  ";
		echo print_contact($contact);
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
			echo "<form action=\"" . rootPageURL($config) . 
				"/payments.php\" method=\"get\">\n" .
				'	<input type="hidden" name="contact"' .
				' value="' . $contact . "\">\n" .
				'	<input type="hidden" name="category"' .
				' value="' . $row['category'] . '">' . "\n" .
				'	<input class="buttons" type="submit" value=' . 
				'"View Category: ' . $row['category']  . " " .
				get_category_sum($contact, $row['category']) .
				'"/>' . "\n</form>\n";
			}
		}
		$balance = 0;
		$due_balance = 0;
		$category = $mysql_db->real_escape_string($_GET["category"]);
	}
	else
	{
		$category = "";
	}

	if (($_POST["action"] == "") || ($_POST["action"] == "apply"))
	{
		echo "<table style=\"width: 100%\" border=\"1\" >\n";
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

		if ($payment_results && ($payment_results->num_rows > 0))
		{
			while($row = $payment_results->fetch_array(MYSQLI_BOTH))
			{
				echo "	<tr>\n";
			
				echo "		<td>" . $row['payment_id'] . " ";
				echo "	<form action=\"" . rootPageURL($config) . 
					"/payments.php\" method=\"post\">\n" .
					"		<input type=\"hidden\" name=" . 
					"\"action\" value=\"copy\">\n" .
					"		<input type=\"hidden\" name=" . 
					"\"id\" value=\"" . $row['payment_id'] . "\">\n" .
					"		<input class=\"buttons\" type=\"submit\" value=" . 
					"\"Copy\"/>\n" .
					"	</form>\n";
				echo "	<form action=\"" . rootPageURL($config) . 
					"/payments.php\" method=\"post\">\n" .
					"		<input type=\"hidden\" name=" . 
					"\"action\" value=\"edit\">\n" .
					"		<input type=\"hidden\" name=" . 
					"\"id\" value=\"" . $row['payment_id'] . "\">\n" .
					"		<input class=\"buttons\" type=\"submit\" value=" . 
					"\"Edit\"/>\n" .
					"	</form>\n";
				echo "</td>\n";
			
				echo "		<td>";
				echo "<a href=\"" . rootPageURL($config) . 
					"/payments.php?contact=" . $row['pay_to'] . "\"> ";
				echo print_contact($row['pay_to']);
				echo "</a>";
				echo "</td>\n";
			
				echo "		<td>";
				echo "<a href=\"" . rootPageURL($config) . 
					"/payments.php?contact=" . $row['paid_by'] . "\"> ";
				echo print_contact($row['paid_by']);
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
			
				echo "		<td>" . blank_check($row['date_earned']) . "</td>\n";
				echo "		<td>" . blank_check($row['date_paid']) . "</td>\n";
				echo "		<td>" . blank_check($row['comments']) . "</td>\n";
				echo "		<td>" . blank_check($row['category']) . "</td>\n";
			
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
					echo "		<td>" . '<a href="' . rootPageURL($config) .
						'/' . $row['invoice'] . 
						'" target="_blank">Download</a></td>' . "\n";
				}
				echo "	</tr>\n";
			}
		}
	
		echo "</table><br>\n";
	
		if ($payment_results && ($payment_results->num_rows > 30))
		{
			$next_page = 1;
		}
		else
		{
			$next_page = 0;
		}
	
		if ($contact != 0)
		{
			if ($start_page > 0)
			{
				echo '<a href="' . rootPageURL($config) . 
					'/payments.php?contact=' . $contact . 
					'&page=' . ($start_page-1) . 
					'">Previous page</a>  ';
			}
			if ($next_page == 1)
			{
				echo '<a href="' . rootPageURL($config) . '/payments.php?contact=' .
					$contact . '&page=' . ($start_page+1) . 
					'">Next page</a>' . "<br >\n";
			}
		
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
				echo '<a href="' . rootPageURL($config) . 
					'/payments.php?page=' . ($start_page-1) . 
					'">Previous page</a>  ';
			if ($next_page == 1)
			{
				echo '<a href="' . rootPageURL($config) . '/payments.php?page=' . 
					($start_page+1) . '">Next page</a>' . "<br >\n";
			}

			echo "	<form action=\"" . rootPageURL($config) . 
				"/payments.php\" method=\"post\">\n" .
				"		<input type=\"hidden\" name=" . 
				"\"action\" value=\"edit\">\n" .
				"		<input type=\"hidden\" name=" . 
				"\"id\" value=\"0\">\n" .
				"		<input class=\"buttons\" type=\"submit\" value=" .
				"\"Insert new payment\"/>\n" .
				"	</form>\n";	
		}
	}

	echo "</div>\n";
}
catch (\webAdmin\ConfigurationMissingException $e)
{
	?>
	<title>Site Configuration Error</title>
	<?php do_css($config) ?>
	</head>
	<body>
	<h1>Site configuration error</h1>
	<?php
	if (isset($_GET['debug']) || ($config['debug']==1))
	{
		echo "Details: " . (string)$e . "<br />\n";
	}
}
catch (\webAdmin\DatabaseConnectionFailedException $e)
{
	?>
	<title>Site Configuration Error</title>
	<?php do_css($config) ?>
	</head>
	<body>
	<h1>Site configuration error</h1>
	<?php
	if (isset($_GET['debug']) || ($config['debug']==1))
	{
		echo "Details: " . (string)$e . "<br />\n";
	}
}
catch (\webAdmin\PermissionDeniedException $e)
{
	?>
	<title>Permission Denied</title>
	<?php do_css($config) ?>
	</head>
	<body>
	<h1>Permission Denied</h1>
	<?php
	if (isset($_GET['debug']) || ($config['debug']==1))
	{
		echo "Details: " . (string)$e . "<br />\n";
	}
}
catch (\webAdmin\InvalidUsernameOrPasswordException $e)
{
	echo "<h3>Invalid username or password</h3>\n";
	echo "<b>Please login</b>\n" .
		"<form action=\"" . curPageURL($config) . "\" method=\"post\" autocomplete=\"on\" >\n" .
		"	<input type=\"hidden\" name=\"action\" value=\"login\">\n" .
		"	<label for=\"username\"> Username: <input type=\"text\" name=\"username\" autocomplete=\"on\" ><br>\n" .
		"	<label for=\"password\"> Password: <input type=\"password\" name=\"password\" autocomplete=\"on\" ><br>\n" .
		"	<input class=\"buttons\" type=\"submit\" name=\"do_login\" value=\"Login\">\n" .
		"</form>\n";
	if ($config['allow_user_create'] == 1)
	{
		echo "<form action=\"" . curPageURL($config) . "\" method=\"post\">\n" .
			 "	<input type=\"hidden\" name=\"action\" value=\"register\">\n" .
			 "	<input class=\"buttons\" type=\"submit\" value=\"Register\">\n" .
			 "</form>\n";
	}
}
catch (\webAdmin\NotLoggedInException $e)
{
	echo "<b>Please login</b>\n" .
		"<form action=\"" . curPageURL($config) . "\" method=\"post\" autocomplete=\"on\" >\n" .
		"	<input type=\"hidden\" name=\"action\" value=\"login\">\n" .
		"	<label for=\"username\"> Username: <input type=\"text\" name=\"username\" autocomplete=\"on\" ><br>\n" .
		"	<label for=\"password\"> Password: <input type=\"password\" name=\"password\" autocomplete=\"on\" ><br>\n" .
		"	<input class=\"buttons\" type=\"submit\" name=\"do_login\" value=\"Login\">\n" .
		"</form>\n";
	if ($config['allow_user_create'] == 1)
	{
		echo "<form action=\"" . curPageURL($config) . "\" method=\"post\">\n" .
			 "	<input type=\"hidden\" name=\"action\" value=\"register\">\n" .
			 "	<input class=\"buttons\" type=\"submit\" value=\"Register\">\n" .
			 "</form>\n";
	}
}
catch (\webAdmin\CertificateException $e)
{
	echo "<b>A certificate is required to access this page</b><br />\n";
	if (isset($_GET['debug']) || ($config['debug']==1))
	{
		echo "Details: " . (string)$e . "<br />\n";
	}
}
catch (Exception $e)
{
	?>
	<title>Error</title>
	<?php do_css($config) ?>
	</head>
	<body>
	<h1>Error</h1>
	<?php
	if (isset($_GET['debug']) || ($config['debug']==1))
	{
		echo "Details: " . (string)$e . "<br />\n";
	}
}


?>

</body>
</html>
		
