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
require_once("webAdmin/global.php");

\webAdmin\runSite();

function website($mysql_db, $config, $cust_session)
{
	if (!headers_sent())
	{
		header('Content-type: text/html; charset=utf-8');
	}
	echo "<!DOCTYPE HTML>\n";
	echo "<html>\n";

	try
	{
		$currentUser = new \webAdmin\user($config, $mysql_db, "users");
		$currentUser->certificate_tables("root_ca", "intermediate_ca", "user_certs");
		$currentUser->require_login_or_registered_certificate();
		
		echo "<head>\n";
		echo "	<title>Maintenance: ";
		\webAdmin\sitename($config);
		echo "</title>\n";
		\webAdmin\do_css($config);
		echo "</head>\n";
		echo "<body>\n";
		echo "	<script type=\"text/javascript\" src=\"jquery-1.2.1.pack.js\"></script>\n";
		echo "	<script type=\"text/javascript\" src=\"jscript.js\"></script>\n";
		
		if (isset($_GET["id"]))
		{
			if (is_numeric($_GET["id"]) == FALSE)
			{
				$id = 0;
				$id_valid = 0;
			}
			else
			{
				$id = $_GET["id"];
				$id_valid = 1;
			}
		}
		else
		{
			$id = 0;
			$id_valid = 0;
		}

		\webAdmin\do_top_menu(5, $config);

		if (isset($_POST["action"]))
		{
			if (($_POST["action"]=="add_maintenance") && ($id_valid==1))
			{
				$mod_query = "INSERT INTO maintenance (equipment_id, notes_done) VALUES (" .
					"'" . $id . "','" . $mysql_db->real_escape_string($_POST["notes"]) . "');";
				
				if ($mysql_db->query($mod_query) == TRUE)
				{
					echo "Successfully inserted new maintenance item<br >\n";
				}
				else
				{
					echo $mysql_db->error . "<br>\n";
					echo $mod_query . "<br>\n";
				}
			}
			if (($_POST["action"]=="add_expense") && ($id_valid==1))
			{
				$expense_query = "INSERT INTO maintenance_expenses (equipment_id, payment_id) VALUES (" .
					$id . ", " . $mysql_db->real_escape_string($_SESSION['payment_reference']) . ");";
				$mysql_db->query($expense_query);
			}
			else if ($_POST["action"] == "remove_expense")
			{
				$expense_query = "DELETE from maintenance_expenses WHERE equipment_id=" .
					$id . " AND payment_id=" . $mysql_db->real_escape_string($_POST['id']) . " LIMIT 1;";
				$mysql_db->query($expense_query);
				$_POST["action"] = "";	//transition to listing the newly modified job
			}
		}

		if ($id_valid == 1)
		{
			$equip_query = "SELECT * FROM equipment WHERE id=" . $id;
			$equip_results = $mysql_db->query($equip_query);
			if($equip_row = $equip_results->fetch_array(MYSQLI_BOTH))
			{
				echo "<h1>" . $equip_row["name"] . "<br>" . $equip_row['description'] . "<br></h1>\n";
			}

			echo "<a href=\"locations.php?id=" . $equip_row['location'] . "\">Locate this equipment</a><br>\n";

			$query = "SELECT * FROM maintenance WHERE equipment_id=" . $id;
		}
		else
		{
			echo "Listing RECENT maintenance for all equipment.<br>\n";
			$query = "SELECT maintenance.*, equipment.* FROM maintenance INNER JOIN equipment ON equipment.id = maintenance.equipment_id";
		}
		$results = $mysql_db->query($query);
		if ($results && $results->num_rows > 0)
		{
			while($row = $results->fetch_array(MYSQLI_BOTH))
			{
				if ($id_valid == 1)
				{
					echo "<b>" . $row["when_done"] . "</b> :" . $row['notes_done'] . "<br>\n";
				}
				else
				{
					echo "<a href=\"maintenance.php?id=" . $row['id'] . "\">\n";
					echo "<b>" . $row["name"] . ", " . $row['description'] . "</b></a>\n";
					echo " <i>" . $row["when_done"] . "</i> :" . $row['notes_done'] . "<br>\n";
				}
			}
		}

		if ($id_valid == 1)
		{
			$query = "SELECT payments.* FROM payments INNER JOIN maintenance_expenses ON payments.payment_id=maintenance_expenses.payment_id WHERE maintenance_expenses.equipment_id=" . $id;
			$expense_found = 0;
			$expense_result = $mysql_db->query($query);
			echo "	<b>Maintenance expense history</b>:<br >\n";
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
			$assets = 0;
			$liable = 0;
			$o_assets = 0;
			$o_liable = 0;
			if ($expense_result)
			{
				while ($expenserow = $expense_result->fetch_array(MYSQLI_BOTH))
				{
					$expenserow['expense'] = 1;
					$expense_found = 1;
					echo "	<tr>\n";				
					echo "		<td>" . $expenserow['payment_id'] . " ";
					echo "	<form action=\"" . \webAdmin\rootPageURL($config) . 
						"/payments.php\" method=\"post\">\n" .
						"		<input type=\"hidden\" name=" . 
						"\"action\" value=\"edit\">\n" .
						"		<input type=\"hidden\" name=" . 
						"\"id\" value=\"" . $expenserow['payment_id'] . "\">\n" .
						"		<input class=\"buttons\" type=\"submit\" value=" . 
						"\"Edit\"/>\n" .
						"	</form>\n";
					echo "	<form action=\"" . \webAdmin\rootPageURL($config) . 
							"/maintenance.php?id=" . $id . "\" method=\"post\">\n" .
							"		<input type=\"hidden\" name=" . 
							"\"action\" value=\"remove_expense\">\n" .
							"		<input type=\"hidden\" name=" . 
							"\"id\" value=\"" . $expenserow['payment_id'] . "\">\n" .
							"		<input class=\"buttons\" type=\"submit\" value=" . 
							"\"Remove\"/>\n" .
							"	</form>\n";
					echo "</td>\n";
				
					echo "		<td>";
					echo "<a href=\"" . \webAdmin\rootPageURL($config) . 
						"/payments.php?contact=" . $expenserow['pay_to'] . "\"> ";
					echo print_contact($expenserow['pay_to']);
					echo "</a>";
					echo "</td>\n";
				
					echo "		<td>";
					echo "<a href=\"" . \webAdmin\rootPageURL($config) . 
						"/payments.php?contact=" . $expenserow['paid_by'] . "\"> ";
					echo print_contact($expenserow['paid_by']);
					echo "</a>";
					echo "</td>\n";
				
					echo "		<td>";
					if (isset($contact))
					{
						if ($contact != 0)
						{
							if ($expenserow['expense'] == 0)
							{
								echo "+";
							}
							else
							{
								echo "-";
							}
						}
					}
					echo "$" . $expenserow['amount_earned'] . "</td>\n";
					//blank_check function on next 4 lines
					echo "		<td>" . ($expenserow['date_earned']) . "</td>\n";
					echo "		<td>" . ($expenserow['date_paid']) . "</td>\n";
					echo "		<td>" . ($expenserow['comments']) . "</td>\n";
					echo "		<td>" . ($expenserow['category']) . "</td>\n";
				
					if ($expenserow['date_paid'] != "0000-00-00")
					{
						if ($expenserow['expense'] == 0)
						{
							$assets += $expenserow['amount_earned'];
						}
						else
						{
							$liable += $expenserow['amount_earned'];
						}
					}
					else
					{
						if ($expenserow['expense'] == 0)
						{
							$o_assets += $expenserow['amount_earned'];
						}
						else
						{
							$o_liable += $expenserow['amount_earned'];
						}
					}

					if ($expenserow['invoice'] == "")
					{
						echo "		<td>Not available</td>\n";
					}
					else
					{
						echo "		<td>" . '<a href="' . \webAdmin\rootPageURL($config) .
							'/' . $expenserow['invoice'] . 
							'" target="_blank">Download</a></td>' . "\n";
					}
					echo "	</tr>\n";

				}
			}
			else
			{
				$o_liable = 0;
				$o_assets = 0;
				$liable = 0;
			}
			if ($expense_found == 0)
			{
				echo "	<tr><td>No expenses found</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>\n";
			}
			echo "</table><br>\n";
			echo "Outstanding expenses: " . $o_liable . "<br>\n";
			echo "Outstanding income: " . $o_assets . "<br>\n";
			echo "Expenses: " . $liable . "<br>\n";
			
			echo "   <form method=\"POST\">\n";
			echo "	<input type=\"hidden\" name=\"action\" value=\"add_maintenance\">\n";
			echo "	<input class=\"buttons\" type=\"checkbox\" name=\"add_maintenance\" ";
			echo "onclick=\"cb_hide_show(this, $('#add_maintenance'));\" />Add a maintenance item<br >\n";
			echo "	<div id=\"add_maintenance\" style=\"display: none;\">\n";
			echo '	<textarea name="notes" id="notes" rows=4 cols=75 ></textarea><br >' . "\n";
			echo "	<input class=\"buttons\" type=\"submit\" value=\"Create\">";
			echo "	</div>\n";
			echo "   </form>\n";
			
			if (isset($_SESSION['payment_reference']))
			{
				echo "    <form action=\"" . \webAdmin\rootPageURL($config) . 
					"/maintenance.php?id=" . $id . "\" method=\"post\">\n" .
					"		<input type=\"hidden\" name=" . 
					"\"action\" value=\"add_expense\">\n" .
					"		<input class=\"buttons\" type=\"submit\" value=" . 
					"\"Add selected payment\"/>\n" .
					"	</form>\n";
			}
		}
	}
	catch (\webAdmin\PermissionDeniedException $e)
	{
		echo "<head>\n";
		echo "	<title>Permission Denied</title>\n";
		\webAdmin\do_css($config);
		echo "</head>\n";
		echo "<body>\n";
		echo "	<h1>Permission Denied</h1>\n";
		if (isset($_GET['debug']) || ($config['debug']==1))
		{
			echo "Details: " . (string)$e . "<br />\n";
		}
	}
	catch (\webAdmin\InvalidUsernameOrPasswordException $e)
	{
		echo "<head>\n";
		echo "	<title>Permission Denied</title>\n";
		\webAdmin\do_css($config);
		echo "</head>\n";
		echo "<body>\n";
		echo "	<h3>Invalid username or password</h3>\n";
		$currentUser->login_form();
	}
	catch (\webAdmin\NotLoggedInException $e)
	{
		echo "<head>\n";
		echo "	<title>Permission Denied</title>\n";
		\webAdmin\do_css($config);
		echo "</head>\n";
		echo "<body>\n";
		$currentUser->login_form();
	}
	catch (\webAdmin\CertificateException $e)
	{
		echo "<head>\n";
		echo "	<title>Permission Denied</title>\n";
		\webAdmin\do_css($config);
		echo "</head>\n";
		echo "<body>\n";
		echo "	<b>A certificate is required to access this page</b><br />\n";
		if (isset($_GET['debug']) || ($config['debug']==1))
		{
			echo "Details: " . (string)$e . "<br />\n";
		}
	}
}

?>

</body>
</html>
													
