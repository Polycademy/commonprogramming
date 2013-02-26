<?php

class Houston extends CI_Controller{

	public function __construct(){
		parent::__construct();
		
		$this->load->model('Houston_model');
		
	}

	public function index(){
		echo 'I\'m the index page!';
	}
	
	public function ball(){
	
		$ball = $this->Houston_model->get_ball();
		
		$this->load->view('houston_view', array('ball' => $ball));
	
	}

}