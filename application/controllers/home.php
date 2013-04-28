<?php

class Home extends CI_Controller{

	public function __construct(){
	
		parent::__construct();
	
	}
	
	public function index(){
		
		//when we're in production we can cache the main page for 48 hrs, this requires the cache to be writable, or else this won't work!
		if(ENVIRONMENT != 'development'){
			$this->output->cache(2880);
		}
		
		//due to single page app, we're just going with a default layout, no need for server side templating libraries
		$this->load->view('layouts/default_layout', $this->config->item('sitemeta'));
	
	}

}