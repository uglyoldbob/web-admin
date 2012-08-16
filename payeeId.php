<?php

	// PHP5 Implementation - uses MySQLi.
	// mysqli('localhost', 'yourUsername', 'yourPassword', 'yourDatabase');
		
	$db = new mysqli('localhost', 'thermal', 'y5E2hSLgR8m9', 'thermal');
	
	if(!$db) 
	{
		// Show error if we cannot connect.
		echo 'ERROR: Could not connect to the database.';
	}
	else 
	{
		// Is there a posted query string?
		if(isset($_POST['queryString'])) 
		{
			$queryString = $db->real_escape_string($_POST['queryString']);
			
			// Is the string length greater than 0?
			
			if(strlen($queryString) >0) 
			{
				// Run the query: We use LIKE '$queryString%'
				// The percentage sign is a wild-card, in my example of countries it works like this...
				// $queryString = 'Uni';
				// Returned data = 'United States, United Kindom';
				
				// YOU NEED TO ALTER THE QUERY TO MATCH YOUR DATABASE.
				// eg: SELECT yourColumnName FROM yourTable WHERE yourColumnName LIKE '$queryString%' LIMIT 10
				
				$query = $db->query("SELECT * FROM contacts WHERE last_name LIKE '%$queryString%' LIMIT 10");
				if($query) 
				{
					while ($result = $query ->fetch_object()) 
					{
	         			echo '<li onClick="fillPayee(\'' . 
	         				$result->last_name . ', ' . $result->first_name . 
	         				'\',\'' . $result->emp_id . '\');">' . 
	         				'[' . $result->emp_id . '] ' . $result->last_name . ', ' . $result->first_name . 
	         				'</li>';
	         		}
				}
				else 
				{
					echo 'ERROR: There was a problem with the query.';
				}
			}
			else 
			{
				// Dont do anything.
			} // There is a queryString.
		}
		else 
		{
			echo 'There should be no direct access to this script!';
		}
	}
?>