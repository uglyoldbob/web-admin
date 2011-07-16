package Database;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.sql.Statement;
import java.sql.ResultSet;

import Config.Config;

//log4j-*.*.**.jar for these imports
import org.apache.log4j.Logger;

public class DatabaseConnection
{
	protected static Logger _log = Logger.getLogger(DatabaseConnection.class.getName());
	Connection _conn = null;
	Statement _stmt = null;
	ResultSet _rs = null;

	
	public DatabaseConnection()
	{	//creates a connection when an object of this class is created
		
		try 
		{
			_conn =
			   DriverManager.getConnection(Config.g().DB_URL + 
			   								"?user=" + Config.g().DB_USERNAME +
			   								"&password=" + Config.g().DB_PASSWORD);
		}
		catch (SQLException ex) 
		{
			// handle any errors
			_log.warn("SQLException: " + ex.getMessage());
			_log.warn("SQLState: " + ex.getSQLState());
			_log.warn("VendorError: " + ex.getErrorCode());
		}
	}
	
	public ResultSet issueCommand(String statement)
	{
		_log.info("Command: " + statement);
		try 
		{
			_stmt = _conn.createStatement();
	
		
			if (_stmt.execute(statement)) 
			{
				_rs = _stmt.getResultSet();
			}
		
			// Now do something with the ResultSet ....
		} 
		catch (SQLException ex)
		{
			// handle any errors
			_log.warn("SQLException: " + ex.getMessage());
			_log.warn("SQLState: " + ex.getSQLState());
			_log.warn("VendorError: " + ex.getErrorCode());
		}
		return _rs;
	}

	public void close()
	{
		if (_rs != null) 
		{
			try 
			{
				_rs.close();
			} 
			catch (SQLException sqlEx) 
			{ 
				_log.warn("Error closing ResultSet:" + sqlEx.getLocalizedMessage());
			}
			_rs = null;
		}

		if (_stmt != null) 
		{
			try 
			{
				_stmt.close();
			}
			catch (SQLException sqlEx) 
			{
				_log.warn("Error closing Statement:" + sqlEx.getLocalizedMessage());
			}
			_stmt = null;
		}
		
		if (_conn != null)
		{
			try 
			{
				_conn.close();
			}
			catch (SQLException sqlEx) 
			{
				_log.warn("Error closing Connection: " + sqlEx.getLocalizedMessage());
			}
			_conn = null;
		}

	}
	
}

