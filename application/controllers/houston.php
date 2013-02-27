<?php

class Houston extends CI_Controller{

	//always executed method
	public function __construct(){
		parent::__construct();
		$this->load->database();
	}

	//default method
	public function index(){
		echo 'I\'m the index page!';
	}
	
	//other method
	public function ball(){
	
		$ball = $this->Houston_model->get_ball();
		
		$this->load->view('houston_view', array('ball' => $ball));
	
	}

}