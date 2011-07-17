package ItemTrack;

import java.sql.ResultSet;
import java.sql.SQLException;
import javax.swing.JPanel;
import javax.swing.JScrollPane;
import javax.swing.JTree;
import javax.swing.event.TreeSelectionEvent;
import javax.swing.event.TreeSelectionListener;
import javax.swing.tree.DefaultMutableTreeNode; 
import javax.swing.tree.TreeSelectionModel;

//log4j-*.*.**.jar for these imports
import org.apache.log4j.Logger;

import Database.DatabaseConnection;
import ItemTrack.EquipmentList;
import ItemTrack.Location;

public class LocationTree extends JPanel implements TreeSelectionListener
{
	private static Logger _log = Logger.getLogger(LocationTree.class);

	private DefaultMutableTreeNode location_tree;
	private JTree location_jtree;
	private EquipmentList list;

	private void add_locations(DefaultMutableTreeNode top, int id)
	{
		DefaultMutableTreeNode add_location = null;
		DatabaseConnection db_con = null;

		try
		{
			db_con = new DatabaseConnection();
			ResultSet rs = db_con.issueCommand("SELECT * FROM locations WHERE position = " + id);
			while (rs.next())
			{
				int id_temp = rs.getInt("id");
				Location temp_loc = new Location(rs.getString("description"), id_temp);
				add_location = new DefaultMutableTreeNode(temp_loc);
				top.add(add_location);
				if (id_temp != 0)
					add_locations(add_location, id_temp);
			}
		}
		catch (SQLException e) 
		{
			_log.fatal("Fail loading locations");
			_log.fatal(e.getLocalizedMessage());
		}
		finally
		{
			db_con.close();
		}
	}

	public void valueChanged(TreeSelectionEvent e)
	{
	//Returns the last path element of the selection.
	//This method is useful only when the selection model allows a single selection.
		DefaultMutableTreeNode node = (DefaultMutableTreeNode)location_jtree.getLastSelectedPathComponent();

		if (node == null) //Nothing is selected
			return;

		Object nodeInfo = node.getUserObject();
		Location loc = (Location)nodeInfo;
		list.update(loc.get_id());
	}
	 
	private void updateTree()
	{
		location_tree.removeAllChildren();
//		location_tree_model.reload();
		add_locations(location_tree, 0);
	}

	public LocationTree(EquipmentList listy)
	{
		list = listy;
		
		//create the tree view of locations
		location_tree = new DefaultMutableTreeNode("All Locations");
		_log.info("Loading locations...");
		updateTree();
		_log.info("Finished loading locations.");
		location_jtree = new JTree(location_tree);
		JScrollPane location_tree_view = new JScrollPane(location_jtree);
		location_jtree.getSelectionModel().setSelectionMode(TreeSelectionModel.SINGLE_TREE_SELECTION);
		location_jtree.addTreeSelectionListener(this);
		add(location_tree_view);
	}
}