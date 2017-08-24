<?php

if ('forms.php' == basename($_SERVER['SCRIPT_FILENAME']))
	die ('<h2>Direct File Access Prohibited</h2>');

function make_autocomplete($disp, $fill_val, $name, $id, $fillfunc, $suggestions, $autolist)
{
	echo "<div>\n";
	echo $disp . "\n";
	echo '<input class="fields" type="text" autocomplete="off" value="';
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
	echo '<form method="POST" action="payments.php" enctype="multipart/form-data">' . "\n";
	echo "	<input type=\"hidden\" name=\"action\" value=\"apply\">\n";
	if ($id != 0)
		echo '<b>Payment ID: </b>Id number: <input class="fields" type="text" value="' . $id . '" name="pay_id" readonly>' . "<br >\n";
	echo '	<b>Payment by: </b>';
	
	make_autocomplete('<b>Contact Name:</b> ', $payer_id, "name_payer",
		"id_payer", "fillNames", "payer_suggest", "payer_list");
	echo '	<b>Payment to: </b>' . "\n";
	make_autocomplete('<b>Contact Name:</b> ', $payee_id, "name_payee",
		"id_payee", "fillNames", "payee_suggest", "payee_list");
	
	echo '	<b>Amount of Payment: </b>Dollar amount: $<input class="fields" type="text" value="' . $amount . '" name="amount_paid" id="amount_paid" ><br >' . "\n";
	echo '	<b>Date Earned: </b>Date (YYYY-MM-DD): <input class="fields" type="text" value="' . $earned . '" name="date_earned" id="date_earned" ><br >' . "\n";
	echo '	<b>Date Paid: </b>Date (YYYY-MM-DD): <input class="fields" type="text" value="' . $paid . '" name="date_paid" id="date_paid" ><br >' . "\n";
	echo '	<b>Comments: </b> <input class="fields" type="text" value="' . $comments . '" name="comments" id="comments" size=127 ><br >' . "\n";
	echo '	<b>Category: </b> <select name="categori" class="fields">' . "<br>\n";

	$query = "SELECT * FROM `transaction_categories` ORDER BY `name`";
	$categories = $mysql_db->query($query);
	while($row = $categories->fetch_array(MYSQLI_BOTH))
	{
		if ($category == $row['name'])
		{
		echo '		<option selected class="fields" value="' . $row['name'] .
			'">' . $row['name'] . "</option>\n";
		
		}
		else
		{
		echo '		<option class="fields" value="' . $row['name'] .
			'">' . $row['name'] . "</option>\n";
		}
	}	

	echo '	</select>' . "\n<br>";
	echo '	<b>Invoice: </b>' . "\n";
	echo "	<input class=\"buttons\" type=\"checkbox\" name=\"upload_invoice\" ";
	echo "onclick=\"cb_hide_show(this, $('#upload_invoice'));\" />Upload an invoice<br >\n";
	echo "	<div id=\"upload_invoice\" style=\"display: none;\">\n";
	echo '	<input class="fields" type="file" name="new_invoice">' . "\n";
	echo "	</div>\n";
	echo "<br>\n";

	echo '	<input class="buttons" type="submit" value="Update"/>' . "\n";
	echo '</form>' . "\n";
	echo '<form method="POST" action="payments.php">' . "\n";
	echo '	<input type="hidden" name="action" value="edit">' . "\n";
	echo '	<input type="hidden" name="id" value=' . $id . ">\n";
	echo '	<input class="buttons" type="submit" value="Refresh">' . "\n";
	echo '</form>' . "\n";
	echo '<form>' . "\n";
	echo '	<input class="buttons" type="button" value="Cancel" onclick="history.go(-1)">' . "\n";
	echo '</form>' . "\n";
	echo "</div>\n";
}

?>