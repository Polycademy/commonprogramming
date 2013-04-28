<?php

class Home extends CI_Controller{

	public function __construct(){
	
		parent::__construct();
	
	}
	
	public function index(){
		
		//due to single page app, we're just going with a default layout, no need for server side templating libraries
		$this->load->view('layouts/default_layout', $this->config->item('sitemeta'));
	
	}

}