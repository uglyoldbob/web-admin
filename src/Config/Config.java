package Config;

import java.io.File;
import java.io.FileInputStream;
import java.io.InputStream;
import java.util.Calendar;
import java.util.Properties;
import java.util.logging.Level;

//log4j-*.*.**.jar for these imports
import org.apache.log4j.Logger;

//this class manages configuration information for the server
//to retrieve a configuration, use the following syntax
//Config.g().get("port");
//Config.g().set("port", 5);

public class Config
{
	public String DB_DRIVER;
	public String DB_URL;
	public String DB_USERNAME;
	public String DB_PASSWORD;
	
	private final String SERVER_CONFIG_FILE = "./config/config.ini";
	
	private static Logger _log = Logger.getLogger(Config.class.getName());
	
	private Config() 
	{
	}
	static private Config _instance;
	//g() is used instead of getInstance() because it is shorter, and will make calling code appear less messy
	static public Config g() 
	{
		if (_instance == null) 
			_instance = new Config();
		return _instance;
	}
	
//private int idCounter;
//initialize idCounter to 0 in the constructor
//synchronized public int getUniqueID()
//{ 
//	idCounter++;
//	return idCounter; 
//}
		
	public void load() throws Exception
	{	//loads the server configuration
		try 
		{
			Properties serverSettings = new Properties();
			InputStream is = new FileInputStream(new File(SERVER_CONFIG_FILE));
			serverSettings.load(is);
			is.close();
//			GAME_SERVER_HOST_NAME = serverSettings.getProperty(
//					"GameserverHostname", "*");
//			GAME_SERVER_PORT = Integer.parseInt(serverSettings.getProperty(
//					"GameserverPort", "2000"));
			DB_DRIVER = serverSettings.getProperty("Driver", "com.mysql.jdbc.Driver");
			DB_URL = serverSettings.getProperty("URL", "jdbc:mysql://localhost/equipment");
			DB_USERNAME = serverSettings.getProperty("Username", "root");
			DB_PASSWORD = serverSettings.getProperty("Password", "");
			_log.info("Server configuration loaded");
		}
		catch (Exception e) 
		{
			_log.fatal(e.getLocalizedMessage());
			throw new Error("Failed to Load " + SERVER_CONFIG_FILE + " File.");
		}
	}
}