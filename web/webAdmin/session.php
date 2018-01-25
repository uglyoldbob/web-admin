<?php
namespace webAdmin;
class session
{
	private $config;
	private $db;
	private $table_name;
	
	/* Table data for sessions
	 * id varchar(32) not null primary key
	 * access int(10) unsigned default null
	 * data text
	 */
	
	public function __construct($config, $db, $table_name)
	{
		$this->config = $config;
		$this->table_name = $table_name;
		$this->db = $db;
		
		//Set handler to overide SESSION
		session_set_save_handler(
			array($this, "_open"),
			array($this, "_close"),
			array($this, "_read"),
			array($this, "_write"),
			array($this, "_destroy"),
			array($this, "_gc")
		);
	}
	
	/**
	 * Open, called with session_start
	 */
	public function _open()
	{
		//database opening is handled outside the scope of this class
		return true;
	}
	
	/**
	 * Close
	 */
	public function _close()
	{
		//database closing is handled outside the scope of this class
		return true;
	}
	
	/**
	 * Read session data
	 */
	public function _read($id)
	{
		$statement = $this->db->prepare('select data from ' . $this->table_name . ' where id=?');
		$statement->bind_param('s', $id);
		$statement->execute();
		$statement->store_result();
		if ($statement->num_rows > 0)
		{
			$statement->bind_result($data);
			$statement->fetch();
			return $data;
		}
		else
		{
			return '';
		}
	}
	
	/**
	 * save and close a session
	 * called after the output stream is closed, no output will appear in a browser
	 */
	public function _write($id, $data)
	{
		$access = time();
		$statement = $this->db->prepare('REPLACE INTO ' . $this->table_name . ' VALUES (?, ?, ?)');
		$statement->bind_param('sis', $id, $access, $data); 
		$statement->execute();
		return true;
	}
	
	/**
	 * Destroy a session
	 */
	public function _destroy($id)
	{
		$statement = $this->db->prepare('DELETE FROM ' . $this->table_name . ' WHERE id = ?');
		$statement->bind_param('s', $id);
		$statement->execute();
		return true;
	}
	
	/**
	 * Garbage Collection to purge old sessions
	 */
	public function _gc($max)
	{
		// Calculate what is to be deemed old
		$old = time() - $max;
		$statement = $this->db->prepare('DELETE * FROM ' . $this->table_name . ' WHERE access < ?');
		$statement->bind_param('i', $old);
		$statement->execute();
		return true;
	}
}
?>