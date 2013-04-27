<?php defined('BASEPATH') OR exit('No direct script access allowed');

//this migration can only be ran when you have switched off session use tables, then after migrating, switch it back on!
class Migration_add_sessions extends CI_Migration {

	public function up(){
	
		$this->dbforge->add_field(array(
			'session_id'	=> array(
				'type'			=> 'VARCHAR',
				'constraint'	=> '40',
				'default'		=> '0',
			),
			'ip_address'	=> array(
				'type'			=> 'VARCHAR',
				'constraint'	=> '45',
				'default'		=> '0',
			),
			'user_agent'	=> array(
				'type'			=> 'VARCHAR',
				'constraint'	=> '120',
			),
			'last_activity'	=> array(
				'type'			=> 'INT',
				'constraint'	=> '10',
				'default'		=> '0',
				'unsigned'		=> true,
			),
			'user_data'		=> array(
				'type'			=> 'TEXT',
			),
		));
		
		//make session_id the primary key
		$this->dbforge->add_key('session_id', true);
		
		$this->dbforge->create_table($this->config->item('sess_table_name'));
		
		$this->db->query('ALTER TABLE `' . $this->config->item('sess_table_name') . '` ADD KEY `last_activity_idx` (`last_activity`)');

	}

	public function down(){
	
		$this->dbforge->drop_table($this->config->item('sess_table_name'));
	
	}
}