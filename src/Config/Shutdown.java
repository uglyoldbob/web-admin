package Config;

//log4j-*.*.**.jar for these imports
import org.apache.log4j.Logger;

public class Shutdown extends Thread {
	private static Logger _log = Logger.getLogger(Shutdown.class.getName());
	private static Shutdown _instance;

//	private int shutdownMode;

	public Shutdown() 
	{
//		secondsShut = 30;	//30 seconds to shutdown
//		shutdownMode = SIGTERM;
	}

	public static Shutdown getInstance() 
	{
		if (_instance == null) 
		{
			_instance = new Shutdown();
		}
		return _instance;
	}
	
}