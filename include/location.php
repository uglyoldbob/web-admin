<?php

if ('location.php' == basename($_SERVER['SCRIPT_FILENAME']))
	die ('<h2>Direct File Access Prohibited</h2>');


class locations
{
	function __construct()
	{
	}	

	public static function create_user_first_location($uid)
	{
		global $mysql_db, $config;
		$query = "INSERT INTO `locations` (owner)" .
							" VALUES ('" . $uid . "');";
		$results = $mysql_db->query($query);
		
		$query = "SELECT * FROM locations WHERE owner='" . $uid . "' LIMIT 1;";
		$results = $mysql_db->query($query);
		if ($results)
		{
			if ($results->num_rows != 0)
			{
				$row = $results->fetch_array(MYSQLI_BOTH);
				return $row['id'];	//username exists
			}
		}
		return null;
	}
}

?>
