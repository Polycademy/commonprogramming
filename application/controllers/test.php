<?php

class Test extends CI_Controller{

	protected $accounts_manager;
	
	public function __construct(){
	
		parent::__construct();
		$ioc = $this->config->item('ioc');
		$this->accounts_manager = $ioc['PolyAuth\Accounts\AccountsManager'];

		$user = $this->accounts_manager->get_user(1);
		var_dump($user['ipAddress']);
		
	}
	
	public function index(){

	}

}