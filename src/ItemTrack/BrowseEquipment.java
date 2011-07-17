package ItemTrack;

import javax.swing.JPanel;

//log4j-*.*.**.jar for these imports
import org.apache.log4j.Logger;

import ItemTrack.EquipmentList;
import ItemTrack.LocationTree;

public class BrowseEquipment extends JPanel
{
	private static Logger _log = Logger.getLogger(BrowseEquipment.class);
	
	private LocationTree ltree;
	private EquipmentList elist;
	
	public BrowseEquipment()
	{
		elist = new EquipmentList();
		ltree = new LocationTree(elist);
		
		add(ltree);
		add(elist);
	}
	
}