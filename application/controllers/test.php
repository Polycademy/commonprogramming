<?php

class Test extends CI_Controller{

	protected $accounts_manager;
	protected $user_sessions;
	
	public function __construct(){
	
		parent::__construct();

		$ioc = $this->config->item('ioc');

		$this->accounts_manager = $ioc['PolyAuth\Accounts\AccountsManager'];
		$this->user_sessions = $ioc['PolyAuth\Sessions\UserSessions'];
		
	}
	
	public function index(){

	}

}