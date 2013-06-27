<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_chat extends CI_Migration {

	public function up(){
	
		$this->dbforge->add_field('id');
		
		$this->dbforge->add_field(
			array(
				'timestamp' => array( //time of message
					'type' => 'TIMESTAMP',
				),
				'usersId' => array( //user who types the message
					'type' => 'INT',
					'constraint' => '9',
				),
				'roomId' => array(
					'type' => 'INT',
					'constraint' => '9',
				),
				'message' => array(
					'type' => 'varchar',
					'constraint' => '1000'	//max 1000 characters - about 200 words
				),
			)
		);
		
		$this->dbforge->create_table('chat');
		
	}

	public function down(){
	
		$this->dbforge->drop_table('chat');
	
	}
}