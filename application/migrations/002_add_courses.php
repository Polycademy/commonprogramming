<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_courses extends CI_Migration {

	public function up(){
	
		$this->dbforge->add_field('id');
		
		$this->dbforge->add_field(array(
			'name' => array(
				'type'	=> 'VARCHAR',
				'constraint'	=> '50',
			),
			'starting_date' => array(
				'type' => 'DATE',
			),
			'days_duration'	=> array(
				'type' => 'SMALLINT',
			),
			'times'	=> array(
				'type'	=> 'VARCHAR',
				'constraint'	=> '100',
			),
			'number_of_applications'	=> array(
				'type'	=> 'SMALLINT',
			),
			'number_of_students'	=> array(
				'type'	=> 'SMALLINT',
			),
		));
		
		$this->dbforge->create_table('courses');

	}

	public function down(){
	
		$this->dbforge->drop_table('courses');
	
	}
}