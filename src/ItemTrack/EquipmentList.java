package ItemTrack;

import java.sql.ResultSet;
import java.sql.SQLException;
import javax.swing.JList;
import javax.swing.JPanel;
import javax.swing.JScrollPane;
import javax.swing.DefaultListModel;
import javax.swing.event.ListSelectionEvent;
import javax.swing.event.ListSelectionListener;
import javax.swing.ListSelectionModel;

//log4j-*.*.**.jar for these imports
import org.apache.log4j.Logger;

import Database.DatabaseConnection;
import ItemTrack.Equipment;

public class EquipmentList extends JPanel implements ListSelectionListener
{
		private static Logger _log = Logger.getLogger(EquipmentList.class);
		
		private JList list;
		private DefaultListModel listModel;
		private JScrollPane listScrollPane;
		
		public void update(int id_num)
		{	//directs the list to list everything in the given location
			_log.debug("Updating list of equipment");
			clear_list();
			DatabaseConnection db_con = null;
			try
			{
				db_con = new DatabaseConnection();
				ResultSet rs = db_con.issueCommand("SELECT * FROM equipment WHERE location = " + id_num);
				while (rs.next())
				{
					Equipment new_equip = 
						new Equipment(
							rs.getString("name"), 
							rs.getString("description"),
							rs.getInt("quantity"),
							rs.getInt("id"),
							rs.getInt("location"),
							rs.getString("last_known")
							);
					listModel.addElement(new_equip);
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
			_log.debug("Finished updating equipment list");
		}

		private void clear_list()
		{
			int size = listModel.getSize();
			list.setSelectedIndex(0);
			while (size != 0)
			{
				listModel.remove(0);
				size = listModel.getSize();
			
				if (size != 0) 
				{
					list.setSelectedIndex(0);
					list.ensureIndexIsVisible(0);
				}
			}
		}
		
		public EquipmentList()
		{			
			listModel = new DefaultListModel();
			
			list = new JList(listModel);
			list.setSelectionMode(ListSelectionModel.SINGLE_SELECTION);
			list.setSelectedIndex(0);
			list.addListSelectionListener(this);
			listScrollPane = new JScrollPane(list);
			
			listModel.addElement("Jane Doe");
			listModel.addElement("John Smith");
			listModel.addElement("Kathy Green");
			
			clear_list();
			
			listModel.addElement("bob");
			listModel.addElement("shoes");
			listModel.addElement(new Equipment("onion", 3));
			
			add(listScrollPane);
		}
		
		//This method is required by ListSelectionListener.
		public void valueChanged(ListSelectionEvent e) 
		{
			if (e.getValueIsAdjusting() == false) 
			{
				if (list.getSelectedIndex() == -1) 
				{
//					fireButton.setEnabled(false);
				}
				else
				{
					_log.info("Selected equipment");
//					fireButton.setEnabled(true);
				}
			}
		}
}