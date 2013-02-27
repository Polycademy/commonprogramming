<?php

class Houston extends CI_Controller{

	//always executed method
	public function __construct(){
		parent::__construct();
		$this->load->model('Blog_model');
	}

	//default method
	public function index(){
		echo 'I\'m the index page!';
	}
	
	//other method
	public function ball(){
		$ball = $this->Blog_model->read_all();
		var_dump($ball);
	}
	
	//create a new ball!
	public function make_a_new_ball(){
		$result = $this->Blog_model->create('Blue');
		var_dump($result);
	}
	
	public function get_me_a_specific_ball($id){
	
		$result = $this->Blog_model->read($id);
		var_dump($result);
	
	}
	
	public function transform_ball($id, $title){
	
		$result = $this->Blog_model->update($id, $title);
		var_dump($result);
		
	}

}