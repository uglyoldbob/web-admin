package ItemTrack;

import java.util.logging.Level;

//log4j-*.*.**.jar for these imports
import org.apache.log4j.Logger;

public class Test 
{
	protected static Logger _log = Logger.getLogger(Test.class.getName());
	
	public Test() 
	{	//stores the packet data and skips over the opcode
		_log.info("Test class loaded");		
	}
	
}
