package ItemTrack;

//log4j-*.*.**.jar for these imports
import org.apache.log4j.Logger;

public class Equipment
{
	private static Logger _log = Logger.getLogger(Equipment.class);
	
	private String display_name;
	private String description;
	private int quantity;
	private String time;
	private int id_num;
	private int location;
	
	public int get_id()
	{
		return id_num;
	}
	
	public Equipment(String display, int id)
	{
		id_num = id;
		display_name = display;
	}
	
	public Equipment(String display, String desc, int quant, int id, int loc, String when)
	{
		display_name = display;
		description = desc;
		quantity = quant;
		id_num = id;
		location = loc;
		time = when;
	}
	
	@Override public String toString()
	{
		return display_name;	
	}
}