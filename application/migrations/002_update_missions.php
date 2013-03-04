<?php defined('BASEPATH') OR exit('No direct script access allowed');
 
//notice that the name of the class will be Migration_add_missions whereas the file name is 001_add_missions
class Migration_update_missions extends CI_Migration {
 
	public function up(){
 
		$fields = array(
			'description' => array(
				'name' => 'content',
				'type' => 'VARCHAR(30)',
			),
		);
		
		$this->dbforge->modify_column('missions', $fields);		
 
	}
 
	public function down(){
 
		$fields = array(
			'content' => array(
				'name' => 'description',
				'type' => 'TEXT',
			),
		);
		
		$this->dbforge->modify_column('missions', $fields);
		
	}
 
}

