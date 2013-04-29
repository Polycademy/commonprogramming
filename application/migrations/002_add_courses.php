<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_courses extends CI_Migration {

	public function up(){
	
		$this->dbforge->add_field('id');
		
		$this->dbforge->add_field(array(
			'name' => array(
				'type'	=> 'VARCHAR',
				'constraint'	=> '50',
			),
			'startingDate' => array(
				'type' => 'DATE',
			),
			'daysDuration'	=> array(
				'type' => 'SMALLINT',
			),
			'times'	=> array(
				'type'	=> 'VARCHAR',
				'constraint'	=> '100',
			),
			'numberOfApplications'	=> array(
				'type'	=> 'SMALLINT',
			),
			'numberOfStudents'	=> array(
				'type'	=> 'SMALLINT',
			),
		));
		
		$this->dbforge->create_table('courses');

	}

	public function down(){
	
		$this->dbforge->drop_table('courses');
	
	}
}