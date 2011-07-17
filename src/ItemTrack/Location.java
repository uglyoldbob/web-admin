package ItemTrack;

//log4j-*.*.**.jar for these imports
import org.apache.log4j.Logger;

public class Location
{
	private static Logger _log = Logger.getLogger(Location.class);
	
	private String display_name;
	private int id_num;
	
	public int get_id()
	{
		return id_num;
	}
	
	public Location (String display, int id)
	{
		id_num = id;
		display_name = display;
	}
	
	@Override public String toString()
	{
		return display_name;	
	}
}