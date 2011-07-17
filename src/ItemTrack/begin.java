package ItemTrack;

import java.io.BufferedInputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.io.InputStream;
import java.net.ServerSocket;

import Config.Config;
import Config.Shutdown;
import Database.DatabaseManager;

//log4j-*.*.**.jar for these imports
import org.apache.log4j.Logger;
import org.apache.log4j.BasicConfigurator;

public class begin
{
	private static Logger _log = Logger.getLogger(begin.class.getName());

	private static final String LOG_PROP = "./config/log.ini";
	
	public static void main(final String[] args) throws Exception {
		BasicConfigurator.configure();	//configure the log4j logger
		
		File logFolder = new File("log");
		logFolder.mkdir();

//		try {
//			InputStream is = new BufferedInputStream(new FileInputStream(
//					LOG_PROP));
//			LogManager.getLogManager().readConfiguration(is);
//			is.close();
//		} catch (IOException e) {
//			_log.log(Level.SEVERE, "Failed to Load " + LOG_PROP + " File.", e);
//			System.exit(0);
//		}
		
		_log.info("The item manager has started");
		try {
			Config.g().load();	//creates an instance of the config class, which is globally accessible
		} catch (Exception e) {
			_log.fatal(e.getLocalizedMessage());
			System.exit(0);
		}
		_log.info("Settings loaded successfully");

		//add a hook for when the server is shutdown
		Runtime.getRuntime().addShutdownHook(Shutdown.getInstance());
		
		new DatabaseManager();	//initialize the database stuff
		
		Runnable start_me = new ItemTrack();
		javax.swing.SwingUtilities.invokeLater(start_me);
	}
}
