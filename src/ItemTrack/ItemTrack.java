package ItemTrack;

import javax.swing.*;
import java.awt.*;
import java.awt.event.ActionListener;
import java.awt.event.ActionEvent;
import java.awt.event.InputEvent;
import java.awt.event.KeyEvent;

import javax.swing.JTree;
import javax.swing.tree.DefaultMutableTreeNode;
import javax.swing.tree.TreeSelectionModel;
import javax.swing.event.TreeSelectionEvent;
import javax.swing.event.TreeSelectionListener;
import javax.swing.tree.DefaultTreeCellRenderer;
import javax.swing.ToolTipManager;
import javax.swing.ImageIcon;
import javax.swing.Icon;

import ItemTrack.Location;

public class ItemTrack implements Runnable
{
	private void createNodes(DefaultMutableTreeNode top)
	{
		DefaultMutableTreeNode category = null;
		DefaultMutableTreeNode book = null;
		//TODO: replace with code that retrieves listings from the mysql database
		category = new DefaultMutableTreeNode("Books for Java Programmers");
		top.add(category);
		book = new DefaultMutableTreeNode("The Java Tutorial: A Short Course on the Basics");
		category.add(book);
		book = new DefaultMutableTreeNode("The Java Tutorial Continued: The Rest of the JDK");
		category.add(book);
		book = new DefaultMutableTreeNode("The JFC Swing Tutorial: A Guide to Constructing GUIs");
		category.add(book);

		category = new DefaultMutableTreeNode("Books for Java Implementers");
		top.add(category);
		book = new DefaultMutableTreeNode("The Java Virtual Machine Specification");
		category.add(book);
		book = new DefaultMutableTreeNode("The Java Language Specification");
		category.add(book);
	}
	
	public void run()
	{
		//Create and set up the window.
		JFrame frame = new JFrame("Item Management System");
		frame.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);

		JTabbedPane tabbedPane = new JTabbedPane();
		DefaultMutableTreeNode location_top = new DefaultMutableTreeNode("All Locations");
		createNodes(location_top);
		JTree location_tree = new JTree(location_top);
		JScrollPane location_tree_view = new JScrollPane(location_tree);

		tabbedPane.addTab("1) Locations",
							null,
							location_tree_view,
							"Where things are");
		tabbedPane.setMnemonicAt(0, KeyEvent.VK_1);

		tabbedPane.addTab("2) Equipment",
							null,
							null,
							"Examine equipment");
		tabbedPane.setMnemonicAt(1, KeyEvent.VK_2);

		frame.getContentPane().add(tabbedPane);

		//Display the window.
		frame.pack();
		frame.setVisible(true);
	}
}