<?php
namespace webAdmin;
function basic_mysqli_table($result)
{
	echo "<table border=1>\n";
	echo " <tr>\n";
	while ($field = $result->fetch_field())
	{
		echo "	<th>" . $field->name . "</th>\n";
	}
	echo " </tr>\n";
	if ($result->num_rows > 0)
	{
		while ($row = $result->fetch_row())
		{
			echo " <tr>\n";
			foreach ($row as $thing)
				echo "	<td>" . $thing . "</td>\n";
			echo " </tr>\n";
		}
	}
	echo "</table>\n";
}

function basic_table($thing)
{
	echo "<table border=1>\n";

	foreach (array_keys($thing) as $key)
	{
		echo " <tr>\n";
		echo "  <th>$key</th>\n";
		echo "  <td>" . $thing[$key] . "</td>\n";
		echo " </tr>\n";
	}

	echo "</table>\n";
	
}

function make_double_array_mysqli($result)
{
	$rv = [];
	while ($field = $result->fetch_field())
	{
		$rv[] = [$field->name];
	}
	if ($result->num_rows > 0)
	{
		while ($row = $result->fetch_row())
		{
			foreach (array_keys($rv) as $key)
			{
				$rv[$key][] = $row[$key];
			}
		}
	}
	return $rv;
}

function double_array_table($thing)
{
	echo "<table border=1>\n";

	echo " <tr>\n";
	$num_rows = 0;
	foreach (array_keys($thing) as $key)
	{
		$num_rows = max(count($thing[$key]), $num_rows);
		echo "  <th>" . $thing[$key][0] . "</th>\n";
	}
	echo " </tr>\n";
	for ($c = 1; $c < $num_rows; $c++)
	{
		echo " <tr>\n";
		foreach (array_keys($thing) as $key)
		{
			echo "  <td>" . $thing[$key][$c] . "</td>\n";
		}
		echo " </tr>\n";
	}

	echo "</table>\n";
}

function double_array_csv($thing)
{
	$num_rows = 0;
	$header = [];
	foreach (array_keys($thing) as $key)
	{
		$num_rows = max(count($thing[$key]), $num_rows);
		$header[] = $thing[$key][0];
	}
	echo implode(",",$header) . "\n";
	for ($c = 1; $c < $num_rows; $c++)
	{
		$row = [];
		foreach (array_keys($thing) as $key)
		{
			$row[] = $thing[$key][$c];
		}
		echo implode(",",$row) . "\n";
	}
}

?>
