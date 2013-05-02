<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_polyauth extends CI_Migration {

	public function up(){
	
		//this migration will setup the tables for user accounts and login attempts
		//it will not setup migrations for the sessions table, that is up to the end user, and to pass a session save handler object
		
		//modify these migrations to reflect extra fields that you want
		
		// Table structure for table 'user_accounts'
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'MEDIUMINT',
				'constraint' => '8',
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
			'ipAddress' => array(
				'type' => 'VARBINARY',
				'constraint' => '16'
			),
			'username' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
			),
			'password' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
			),
			'email' => array(
				'type' => 'VARCHAR',
				'constraint' => '100'
			),
			'activationCode' => array(
				'type' => 'VARCHAR',
				'constraint' => '40',
				'null' => TRUE
			),
			'forgottenPasswordCode' => array(
				'type' => 'VARCHAR',
				'constraint' => '40',
				'null' => TRUE
			),
			'forgottenPasswordTime' => array(
				'type' => 'INT',
				'constraint' => '11',
				'unsigned' => TRUE,
				'null' => TRUE
			),
			'rememberCode' => array(
				'type' => 'VARCHAR',
				'constraint' => '40',
				'null' => TRUE
			),
			'createdOn' => array(
				'type' => 'DATETIME',
			),
			'lastLogin' => array(
				'type' => 'DATETIME',
			),
			'active' => array(
				'type' => 'TINYINT',
				'constraint' => '1',
				'unsigned' => TRUE,
				'null' => TRUE
			),
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('user_accounts', true);
		
		// Dumping data for table 'users'
		$data = array(
			'id'					=> '1',
			'ipAddress'				=> inet_pton('127.0.0.1'),
			'username'				=> 'administrator',
			'password'				=> '$2y$10$EiqipvSt3lnD//nchj4u9OgOTL9R3J4AbZ5bUVVrh.Tq/gmc5xIvS',
			'email'					=> 'admin@admin.com',
			'activationCode'		=> '',
			'forgottenPasswordCode'	=> NULL,
			'createdOn'				=> date('Y-m-d H:i:s'),
			'lastLogin'				=> date('Y-m-d H:i:s'),
			'active'				=> '1',
		);
		$this->db->insert('user_accounts', $data);
		
		// Table structure for table 'login_attempts'
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'MEDIUMINT',
				'constraint' => '8',
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
			'ipAddress' => array(
				'type' => 'VARBINARY',
				'constraint' => '16'
			),
			'login' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'null', TRUE
			),
			'time' => array(
				'type' => 'DATETIME',
			)
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('login_attempts', true);
		
	}

	public function down(){
	
	
	
	
	}
}