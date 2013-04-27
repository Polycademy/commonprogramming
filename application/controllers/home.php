<?php

class Home extends CI_Controller{

	public $view_data = array();

	public function __construct(){
	
		parent::__construct();
		
		$this->view_data += $this->config->item('view_data');
	
	}
	
	public function index(){
		
		Template::compose('index', $this->view_data);
	
	}

}