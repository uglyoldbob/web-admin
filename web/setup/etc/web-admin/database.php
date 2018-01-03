<?php

$config = parse_ini_file("./config.ini");

//open database connection
function openDatabase()
{
        global $mysql_db, $config;
        $mysql_db = new mysqli($config["database_server"],
                $config["database_username"], $config["database_password"],
                $config["database_name"], $config["database_port"]);
        if ($mysql_db->connect_errno)
        {
                echo "Failed to connect to MySQL: (" . $mysq_db->connect_errno . ") " .
                        $mysq_db->connect_error . "<br >\n";
                die("Database connection failed");
        }
        //TODO: implement calling this function
        //mysqli_set_charset()
}

//close the database connection
function closeDataBase()
{
        global $mysql_db;
        $mysql_db->close();
}

function do_stuff()
{
	global $mysql_db;
	openDatabase();

	$query = "SELECT * FROM version";
	$results = $mysql_db->query($query);
	$has_version_table = false;
	$failed = false;
	while ($row = $results->fetch_array(MYSQLI_BOTH))
	{
		$has_version_table = true;
		switch ($row['id'])
		{
			case "this":
				switch($row['num'])
				{
					case 1:
					//apply new updates here
					break;
				}
				break;
			case "contacts":
				switch ($row['num'])
				{
					case 1:
					//apply new updates here
					break;
				}
				break;
			case "contact_permission":
                                switch ($row['num'])
				{
                                        case 1:
                                        //apply new updates here
                                        break;
				}
				break;
			case "cost_estimations":
                                switch ($row['num'])
				{
                                        case 1:
                                        //apply new updates here
                                        break;
				}
				break;
			case "equipment":
                                switch ($row['num'])
				{
                                        case 1:
                                        //apply new updates here
                                        break;
				}
				break;
			case "images":
                                switch ($row['num'])
				{
                                        case 1:
                                        //apply new updates here
					break;
				}
				break;
			case "inspections":
                                switch ($row['num'])
				{
                                        case 1:
                                        //apply new updates here
                                        break;
				}
				break;
			case "jobs":
                                switch ($row['num'])
				{
                                        case 1:
                                        //apply new updates here
                                        break;
				}
				break;
			case "job_status":
                                switch ($row['num'])
				{
                                        case 1:
                                        //apply new updates here
                                        break;
				}
				break;
			case "job_tasks":
                                switch ($row['num'])
				{
                                        case 1:
                                        //apply new updates here
                                        break;
				}
				break;
			case "locations":
                                switch ($row['num'])
				{
                                        case 1:
                                        //apply new updates here
                                        break;
				}
				break;
			case "payments":
                                switch ($row['num'])
				{
                                        case 1:
                                        //apply new updates here
                                        break;
				}
				break;
			case "properties":
                                switch ($row['num'])
				{
                                        case 1:
                                        //apply new updates here
                                        break;
				}
				break;
			case "status":
                                switch ($row['num'])
				{
                                        case 1:
                                        //apply new updates here
                                        break;
				}
				break;
			case "status_codes":
                                switch ($row['num'])
				{
                                        case 1:
                                        //apply new updates here
                                        break;
				}
				break;
		}
	}

	if ($has_version_table == false)
	{
		echo "Cannot automatically update database - it is too old";
	}
	if ($failed)
	{
		echo "Updating the database failed for some reason\n";
	}
	else
	{
		echo "Database successfully updated\n";
	}

	closeDataBase();
}

do_stuff();


?>
