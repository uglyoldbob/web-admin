<?php
namespace webAdmin;
class finance
{
	public static function table_of_transaction_categories()
	{
		global $mysql_db;
		if ($_POST["action"] == "create_transaction_category")
		{
			$query = "INSERT INTO transaction_categories (" .
				"`name`) VALUES ('" .
				$mysql_db->real_escape_string($_POST["new_transaction_category"]) . "');";
			$mysql_db->query($query);
		}
		$query = "SELECT code, name FROM transaction_categories;";
		$result = $mysql_db->query($query);
		
		if ($result && $result->num_rows > 0)
		{
			while ($row = $result->fetch_array(MYSQLI_BOTH))
			{
				echo $mysql_db->real_escape_string($row['code']);
				echo ", " . $mysql_db->real_escape_string($row['name']) . "<br>\n";
			}
		}

		echo "   <form method=\"POST\">\n";
		echo "	<input type=\"hidden\" name=\"action\" value=\"create_transaction_category\">\n";
		echo "	<input type=\"checkbox\" name=\"create_transaction_category\" ";
		echo "onclick=\"cb_hide_show(this, $('#add_transaction_category'));\" />Add a transaction category<br >\n";
		echo "	<div id=\"add_transaction_category\" style=\"display: none;\">\n";
		echo '	<textarea name="new_transaction_category" id="add_job_status" rows=4 cols=75 > </textarea><br >' . "\n";
		echo "	<input class=\"buttons\" type=\"submit\" value=\"Create\">";
		echo "	</div>\n";
		echo "   </form>\n";
	}
}

?>