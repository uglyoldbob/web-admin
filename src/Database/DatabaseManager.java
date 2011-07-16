package Database;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

import Config.Config;

//log4j-*.*.**.jar for these imports
import org.apache.log4j.Logger;

public class DatabaseManager
{
	protected static Logger _log = Logger.getLogger(DatabaseManager.class.getName());

	public DatabaseManager()
	{
		try 
		{
			// The newInstance() call is a work around for some
			// broken Java implementations
			_log.info("Loading the database driver");
			Class.forName(Config.g().DB_DRIVER).newInstance();
			new DatabaseConnection().close();
		}
		catch (Exception ex) 
		{
			// handle the error
			_log.fatal("Error loading database driver: " + ex.getMessage());
		}
	}	
}

