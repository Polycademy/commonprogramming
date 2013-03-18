<?php

class Rest_model extends CI_Model{

	public $dummy_data;

	public function __construct(){
	
		parent::__construct();
		
		//here's some dummy data, that would in the database...
		$this->dummy_data = array(
		
			array(
				'id'	=> '2',
				'name'	=> 'Rofdger',
				'power'	=> 'OVER 10,000!!!!',
			),
			array(
				'id'	=> '3',
				'name'	=> 'Rfdgdfoger',
				'power'	=> 'OVER 10,000!!!!',
			),		
			array(
				'id'	=> '4',
				'name'	=> 'Roger',
				'power'	=> 'OVER 10,000!!!!',
			),		
			array(
				'id'	=> '5',
				'name'	=> 'Rogfger',
				'power'	=> 'fg',
			),		
			array(
				'id'	=> '6',
				'name'	=> 'Rogfger',
				'power'	=> 'OVER 10,000!!!!',
			),
		
		);
	
	}
	
	public function read_all($limit = false, $offset = false){
	
		//YOU WOULD NEED do db->select everything, then find out how many there is, and then iterate through it, and return it....
		foreach($this->dummy_data as $row){
		
			$data[] = array(
				'id'	=> $row['id'],
				'name'	=> $row['name'],
				'power'	=> $row['power'],
			);
		
		}
		
		return $data;
	
	}

}