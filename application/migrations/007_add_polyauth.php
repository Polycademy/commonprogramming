<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_polyauth extends CI_Migration {

	public function up(){
	
		//this migration will setup the tables for user accounts and login attempts
		//it will not setup migrations for the sessions table, that is up to the end user, and to pass a session save handler object
		
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
			'salt' => array(
				'type' => 'VARCHAR',
				'constraint' => '40'
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
				'type' => 'INT',
				'constraint' => '11',
				'unsigned' => TRUE,
			),
			'lastLogin' => array(
				'type' => 'INT',
				'constraint' => '11',
				'unsigned' => TRUE,
				'null' => TRUE
			),
			'active' => array(
				'type' => 'TINYINT',
				'constraint' => '1',
				'unsigned' => TRUE,
				'null' => TRUE
			),
			'firstName' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'null' => TRUE
			),
			'lastName' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'null' => TRUE
			)
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('user_accounts', true);
		
		// Dumping data for table 'users'
		$data = array(
			'id' => '1',
			'ipAddress' => inet_pton('127.0.0.1'),
			'username' => 'administrator',
			'password' => '$2a$07$SeBknntpZror9uyftVopmu61qg0ms8Qv1yV6FG.kQOSM.9QhmTo36',
			'salt' => '9462e8eee0',
			'email' => 'admin@admin.com',
			'activationCode' => '',
			'forgottenPasswordCode' => NULL,
			'createdOn' => '1268889823',
			'lastLogin' => '1268889823',
			'active' => '1',
			'firstName' => 'Admin',
			'lastName' => 'istrator',
			'company' => 'ADMIN',
			'phone' => '0',
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
				'type' => 'INT',
				'constraint' => '11',
				'unsigned' => TRUE,
				'null' => TRUE
			)
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('login_attempts', true);
		
	}

	public function down(){
	
	
	
	
	}
}