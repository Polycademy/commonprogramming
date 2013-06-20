<?php

// use RBAC\Permission;
// use RBAC\Role\Role;
// use RBAC\Manager\RoleManager;

class Test extends CI_Controller{

	protected $logger;
	
	public function __construct(){
	
		parent::__construct();
		// $this->logger = $this->config->item('ioc')['logger'];
		
	}
	
	public function index(){	
	
		ob_start();

		session_start();
		session_write_close();

		session_start();
		session_write_close();

		session_start();
		session_write_close();

		session_start();
		session_write_close();
		
		if(SID){
			
			$headers =  array_unique(headers_list());	
			
			$cookie_strings = array();
			
			foreach($headers as $header){
				if(preg_match('/^Set-Cookie: (.+)/', $header, $matches)){
					$cookie_strings[] = $matches[1];
				}
			}
			
			header_remove('Set-Cookie');
			
			foreach($cookie_strings as $cookie){
				header('Set-Cookie: ' . $cookie, false);
			}
		
		}
		
		ob_flush();
	
	}

}