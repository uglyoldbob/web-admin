<?php

class jobs
{
	public $job;
	protected $contact;
	protected $start_page;
	
	function __construct()
	{
		if (!(array_key_exists("job", $_GET)))
		{
			$this->job = 0;
		}
		else
		{
			$this->job = $_GET["job"];
			if (is_numeric($this->job) == FALSE)
				$this->job = 0;
		}
		
		if (!(array_key_exists("contact", $_GET)))
		{
			$this->contact = 0;
		}
		else
		{
			$this->contact = $_GET["contact"];
			if (is_numeric($this->contact) == FALSE)
				$this->contact = 0;
		}
		
		if (!(array_key_exists("start_page", $_GET)))
		{
			$this->start_page = 0;
		}
		else
		{
			$this->start_page = $_GET["start_page"];
			if (is_numeric($this->start_page) == FALSE)
				$this->start_page = 0;
		}
	}
	
	public static function table_of_job_status()
	{
		global $mysql_db;
		if ($_POST["action"] == "create_job_status")
		{
			$query = "INSERT INTO status_codes (" .
				"`Description`) VALUES ('" .
				$mysql_db->real_escape_string($_POST["new_job_status"]) . "');";
			$mysql_db->query($query);
		}
		$query = "SELECT code, Description FROM status_codes;";
		$result = $mysql_db->query($query);
		
		while ($row = $result->fetch_array(MYSQLI_BOTH))
		{
			echo $mysql_db->real_escape_string($row['code']);
			echo ", " . $mysql_db->real_escape_string($row['Description']) . "<br>\n";
		}
		
		echo "   <form method=\"POST\">\n";
		echo "	<input type=\"hidden\" name=\"action\" value=\"create_job_status\">\n";
		echo "	<input type=\"checkbox\" name=\"create_job_status\" ";
		echo "onclick=\"cb_hide_show(this, $('#add_job_status'));\" />Add a job status<br >\n";
		echo "	<div id=\"add_job_status\" style=\"display: none;\">\n";
		echo '	<textarea name="new_job_status" id="add_job_status" rows=4 cols=75 > </textarea><br >' . "\n";
		echo "	<input class=\"buttons\" type=\"submit\" value=\"Create\">";
		echo "	</div>\n";
		echo "   </form>\n";
	}
	
	public static function make_status_dropdown($prefix, $selected, $name)
	{
		global $mysql_db;
		$output = '';
		$query = "SELECT code, Description FROM status_codes;";
		$result = $mysql_db->query($query);
		$output .= $prefix . "<select name=" .  $name . ">\n" .
			$prefix . "<option value=0";
		if ($selected == 0)
		{
			$output .= " selected";
		}
		$output .= " >Don't update</option>\n";
		
		while ($row = $result->fetch_array(MYSQLI_BOTH))
		{
			$output .= $prefix . "	<option value=" .
				$mysql_db->real_escape_string($row['code']);
			if ($selected == $mysql_db->real_escape_string($row['code']))
				$output .= " selected";
			$output .= " >" . 
				$mysql_db->real_escape_string($row['Description']) .
				"</option>\n";
		}
		$output .= $prefix . "</select>\n";		
		return $output;
	}
	
	public function create_job($data)
	{
		global $mysql_db;
		$cust1 = $data["cust1_id"];
		if (is_numeric($cust1) == FALSE)
			$cust1 = 0;
		$cust2 = $data["cust2_id"];
		if (is_numeric($cust2) == FALSE)
			$cust2 = 0;
		$comments = $mysql_db->real_escape_string($data["comments"]);
		$query = "INSERT INTO jobs (" .
			"`job_name` ," .	"`id` ," . "`cust_billing` , " . "`cust_shipping` , " .
			"`comments` " . ") VALUES ('" .
			$mysql_db->real_escape_string($data["jobname"]) .
			"', NULL , '" . $cust1 . "', '" . $cust2 . "', '" . $comments . "');";
		if ($mysql_db->query($query) == TRUE)
		{
			echo "Successfully inserted new job<br >\n";
		}
		else
		{
			echo $query . "<br>\n";
		}
		
		$this->job = $mysql_db->insert_id;
		
		if ($data['job_status'] != 0)
		{
			$query = "INSERT INTO job_status (jobid, new_status) VALUES (" .
				$this->job . ", " .
				$mysql_db->real_escape_string($data['job_status']) . ");";
			if ($mysql_db->query($query) == TRUE)
			{
				echo "Successfully created initial job status<br >\n";
			}
		}
	}
	
	public function new_job_form()
	{
		echo "<div>\n";
		echo '<form method="POST" action="jobs.php">' . "\n";
		echo "	<input type=\"hidden\" name=\"action\" value=\"apply\">\n";
	
		echo '	<b>Job name: </b><input type="text" autocomplete="off" value="" name="jobname" id="jobname">' . "\n";
		
		make_autocomplete("<b>Customer Name:</b>", '', "cust1", "cust1_id", 
			"fillNames", "cust1_suggest", "cust1_list");
		make_autocomplete("<b>Deliver to:</b>", '', "cust2", "cust2_id",
			"fillNames", "cust2_suggest", "cust2_list");
		
		echo '	<b>Comments: </b><br >' . "\n" . '<textarea name="comments" id="comments" rows=4 cols=75 ></textarea><br >' . "\n";
		
		echo "	<b>Initial job status:</b> \n";
		echo jobs::make_status_dropdown('	', 1, "job_status") . "<br >\n";
		
		echo "	<input class=\"buttons\" type=\"submit\" value=\"Create this job\"/>\n";
		echo '</form>' . "\n";
		echo "</div>\n";
	}

	
	public function modify_job($data)
	{
		global $mysql_db;
		$query = "UPDATE jobs SET ";
		$needs_comma = 0;
		$do_anything = 0;
		if (isset($data['mod_phone1']))
		{
			if ($data['mod_phone1'] == "on")
			{
				$do_anything = 1;
				if ($needs_comma == 0)
					$needs_comma = 1;
				else
					$query .= ", ";
				$query .= "phone_notify_id=" . $mysql_db->real_escape_string($data['phone1']);
			}
		}
		
		if (isset($data['mod_comments1']))
		{
			if ($data['mod_comments1'] == "on")
			{
				$do_anything = 1;
				if ($needs_comma == 0)
					$needs_comma = 1;
				else
					$query .= ", ";
				$query .= "comments=\"" . $mysql_db->real_escape_string($data['comments']) . "\"";
			}
		}

		if (isset($data['add_payment']))
		{
			if ($data['add_payment'] == "on")
			{
				$expense_query = "INSERT INTO job_expenses (job_id, payment_id) VALUES (" .
					$mysql_db->real_escape_string($data['id']) . ", " . $mysql_db->real_escape_string($data['payment_id']) . ");";
				$mysql_db->query($expense_query);
			}
		}
		
		$query .= " WHERE id=" . $mysql_db->real_escape_string($data['id']) . ";";
		if ($do_anything == 1)
		{
			if ($mysql_db->query($query) == TRUE)
			{
				echo "Successfully updated new job<br >\n";
			}
		}
		
		$this->job = $mysql_db->real_escape_string($data['id']);
		
		if ($data['job_status'] != 0)
		{
			$query = "INSERT INTO job_status (jobid, new_status, what_happened) VALUES (" .
				$this->job . ", " .
				$mysql_db->real_escape_string($data['job_status']) . ", '" .
				$mysql_db->real_escape_string($data['what_happened']) . "');";
			if ($mysql_db->query($query) == TRUE)
			{
				echo "Successfully updated job status<br >\n";
			}
		}
	}
	
	public function table()
	{
		global $mysql_db;
		if ($this->contact != 0)
		{
			$query = "SELECT * FROM jobs WHERE cust_billing = " . $this->contact . " OR " .
				"cust_shipping = " . $this->contact . " ORDER BY id DESC LIMIT " . 
				($this->start_page*30) . ", " . ($this->start_page*30+30);
		}
		else
		{
			$query = "SELECT * FROM jobs ORDER BY id DESC LIMIT " . 
				($this->start_page*30) . ", " . ($this->start_page*30+30);
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
		echo "		<th>Job name</th>\n";
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
			echo $mysql_db->real_escape_string($row['job_name']);
			echo "</td>\n";
	
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
	
		if ($this->start_page > 0)
			echo '<a href="' . rootPageURL() . '/jobs.php?page=' . ($this->start_page-1) . '">Previous page</a>  ';
		if ($next_page == 1)
			echo '<a href="' . rootPageURL() . '/jobs.php?page=' . ($this->start_page+1) . '">Next page</a>' . "<br >\n";
		$contact_results->close();
	}
	
	public function list_job()
	{
		global $mysql_db;
		$query = "SELECT * FROM jobs WHERE id = " . $this->job . " LIMIT 1;";
		$result = $mysql_db->query($query);
		if($row = $result->fetch_array(MYSQLI_BOTH))
		{
			echo "<form action=\"" . rootPageURL() . "/jobs.php\" method=\"post\">\n" .
				 "	<input type=\"hidden\" name=\"action\" value=\"modjob\">\n" .
				 "	<input type=\"hidden\" name=\"id\" value=\"" . $this->job . "\">\n";
			
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

			echo "	<input class=\"buttons\" type=\"checkbox\" name=\"mod_phone1\" ";
			echo "onclick=\"cb_hide_show(this, $('#mod_phone1'));\" />Change this phone number<br >\n";
			echo "	<div id=\"mod_phone1\" style=\"display: none;\">\n";
			for ($i = 0; $i < 6; $i++)
			{
				if ($phone_results[$i]['number'] != null)
				{
					echo "	<input class=\"buttons\" type=\"radio\" name=\"phone1\" " .
						 "value=\"" . $i . "\" ";
					if ($select_radio == $i)
						echo "checked ";
					echo ">" . $phone_results[$i]['name'] . 
						 ": " . $phone_results[$i]['number'] . "<br >\n";
				}
			}
			echo "	<input class=\"buttons\" type=\"radio\" name=\"phone1\" " .
				 "value=\"6\" ";
			if ($select_radio == 6)
				echo "checked ";
			echo ">None<br >\n";
			echo "	</div>\n";
			
			echo "	<b>Comments:</b> " . $row['comments'] . "\n";
			echo "	<input class=\"buttons\" type=\"checkbox\" name=\"mod_comments1\" ";
			echo "onclick=\"cb_hide_show(this, $('#mod_comments'));\" />Change the comments<br >\n";
			echo "	<div id=\"mod_comments\" style=\"display: none;\">\n";
			echo '	<textarea name="comments" id="comments" rows=4 cols=75 >' .
				$mysql_db->real_escape_string($row['comments']) .
				'</textarea><br >' . "\n";
			echo "	</div>\n";
			
			
			$query = "SELECT * from job_status JOIN status_codes ON " .
					 "job_status.new_status=status_codes.code WHERE " .
					 "job_status.jobid=" . $this->job . " ORDER BY " .
					 "job_status.datetime ASC;";
			$statresult = $mysql_db->query($query);
			echo "	<b>Job status history</b>:<br >\n";
			while ($statrow = $statresult->fetch_array(MYSQLI_BOTH))
			{
				echo $statrow['datetime'] . ": " . $statrow['Description'] . 
					", " . $statrow['what_happened'] . "<br >\n";  
			}

			$query = "SELECT payments.*, job_expenses.expense FROM payments INNER JOIN job_expenses ON payments.payment_id=job_expenses.payment_id WHERE job_expenses.job_id=" . $this->job;
			$expense_found = 0;
			$expense_result = $mysql_db->query($query);
			echo "	<b>Job expense history</b>:<br >\n";
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
			while ($expenserow = $expense_result->fetch_array(MYSQLI_BOTH))
			{
				$expense_found = 1;
				echo "	<tr>\n";				
				echo "		<td>" . $expenserow['payment_id'] . " ";
				echo "	<form action=\"" . rootPageURL() . 
					"/payments.php\" method=\"post\">\n" .
					"		<input type=\"hidden\" name=" . 
					"\"action\" value=\"edit\">\n" .
					"		<input type=\"hidden\" name=" . 
					"\"id\" value=\"" . $expenserow['payment_id'] . "\">\n" .
					"		<input class=\"buttons\" type=\"submit\" value=" . 
					"\"Edit\"/>\n" .
					"	</form>\n";
				echo "</td>\n";
				
				echo "		<td>";
				echo "<a href=\"" . rootPageURL() . 
					"/payments.php?contact=" . $expenserow['pay_to'] . "\"> ";
				echo print_contact($expenserow['pay_to']);
				echo "</a>";
				echo "</td>\n";
				
				echo "		<td>";
				echo "<a href=\"" . rootPageURL() . 
					"/payments.php?contact=" . $expenserow['paid_by'] . "\"> ";
				echo print_contact($expenserow['paid_by']);
				echo "</a>";
				echo "</td>\n";
				
				echo "		<td>";
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
					echo "		<td>" . '<a href="' . rootPageURL() .
						'/' . $expenserow['invoice'] . 
						'" target="_blank">Download</a></td>' . "\n";
				}
				echo "	</tr>\n";

			}
			if ($expense_found == 0)
			{
				echo "	<tr><td>No expenses found</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>\n";
			}
			echo "</table><br>\n";
			echo "Outstanding expenses: " . $o_liable . "<br>\n";
			echo "Outstanding income: " . $o_assets . "<br>\n";
			echo "Expenses: " . $liable . "<br>\n";
			echo "Income: " . $assets . "<br>\n";
			echo "	<input class=\"buttons\" type=\"checkbox\" name=\"add_payment\" ";
			echo "onclick=\"cb_hide_show(this, $('#add_payment'));\" />Add an expense<br >\n";
			echo "	<div id=\"add_payment\" style=\"display: none;\">\n";
			echo "	<input type=\"text\" name=\"payment_id\">\n";
			echo "	</div>\n";
			
			echo "	<b>Update job status:</b> \n";
			echo jobs::make_status_dropdown('	', 0, "job_status") . "<br >\n";
			echo "  <textarea name=\"what_happened\" id=\"what_happened\" rows=4 cols=75 ></textarea><br >'\n";			
			echo "	<input class=\"buttons\" type=\"submit\" value=\"Apply Changes\"/>\n" .
				 "</form>";
		}
		else
		{
			echo "Invalid job specified<br >\n";
		}
		$result->close();
	}
}

?>
