package ItemTrack;

import java.awt.event.ActionListener;
import java.awt.event.ActionEvent;
import java.awt.event.InputEvent;
import java.awt.event.KeyEvent;
import javax.swing.tree.DefaultMutableTreeNode;
import javax.swing.tree.DefaultTreeModel;
import javax.swing.JFrame;
import javax.swing.JPanel;
import javax.swing.JTabbedPane;

import Database.DatabaseConnection;
import ItemTrack.BrowseEquipment;
import ItemTrack.Location;

//log4j-*.*.**.jar for these imports
import org.apache.log4j.Logger;

public class ItemTrack implements Runnable
{
	private static Logger _log = Logger.getLogger(ItemTrack.class);
	
	private DefaultMutableTreeNode location_tree;
	private DefaultTreeModel location_tree_model;
	
	private JFrame main_frame;
	private BrowseEquipment tab_1;
	private JPanel tab_2;

	public void run()
	{
		//Create and set up the window.
		main_frame = new JFrame("Item Management System");
		main_frame.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);

		//create the panel for the first tab
		tab_1 = new BrowseEquipment();
		JTabbedPane tabbedPane = new JTabbedPane();
		tabbedPane.addTab("1) Browse All Equipment",
							null,
							tab_1,
							"");
		tabbedPane.setMnemonicAt(0, KeyEvent.VK_1);
		
		tab_2 = new JPanel();
		tabbedPane.addTab("2) Equipment",
							null,
							tab_2,
							"Examine equipment");
		tabbedPane.setMnemonicAt(1, KeyEvent.VK_2);

		main_frame.getContentPane().add(tabbedPane);

		//Display the window.
		main_frame.pack();
		main_frame.setVisible(true);
	}
}
