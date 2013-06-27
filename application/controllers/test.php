<?php

class Test extends CI_Controller{

	protected $logger;
	
	public function __construct(){
	
		parent::__construct();
		$this->logger = $this->config->item('ioc')['Logger'];
		
	}
	
	public function index(){


	}

}