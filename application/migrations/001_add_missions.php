<?php defined('BASEPATH') OR exit('No direct script access allowed');
 
//notice that the name of the class will be Migration_add_missions whereas the file name is 001_add_missions
class Migration_add_missions extends CI_Migration {
 
	public function up(){
 
		//dbforge is already loaded when extending from CI_Migration
 
		//dbforge will notice you're adding a field called id, it will automatically be assigned as an INT(9) auto_incrementing Primary Key.
		$this->dbforge->add_field('id');
 
		$this->dbforge->add_field(
			array(
				'title' => array(
					'type' => 'VARCHAR',
					'constraint' => '100',
				),
				'description' => array(
					'type' => 'TEXT',
				),
				'parameters' => array(
					'type' => 'TEXT',
				),
			)
		);
 
		$this->dbforge->create_table('missions');
 
		//you can also use active records and standard queries to insert raw data (though it's inadvisable except for dummy data like the first login account). You may have to autoload the database library to have it ready
 
	}
 
	public function down(){
 
		//when rolling back all we need to is remove the missions table
		$this->dbforge->drop_table('missions');
 
	}
 
}

