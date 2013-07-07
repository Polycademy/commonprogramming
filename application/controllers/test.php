<?php

use PolyAuth\Exceptions\PolyAuthException;

class Test extends CI_Controller{

	protected $accounts_manager;
	protected $user_sessions;
	
	public function __construct(){
	
		parent::__construct();

		$ioc = $this->config->item('ioc');

		$this->accounts_manager = $ioc['PolyAuth\Accounts\AccountsManager'];
		$this->user_sessions = $ioc['PolyAuth\Sessions\UserSessions'];

		try{
			$this->user_sessions->start();
		}catch(PolyAuthException $e){
			var_dump($e->get_errors());
		}
		
	}
	
	public function first(){

		$this->user_sessions->set_property('Blah1fg', 'Prior value');

		//login, change some properties
		try{

			$this->user_sessions->login([
				'identity'	=> 'administrator',
				'password'	=> 'password',
				'autologin'	=> true
			]);

		}catch(PolyAuthException $e){
			var_dump($e->get_errors());
		}

		if($this->user_sessions->authorized()){
			var_dump('YOU ARE LOGGED IN!');
		}else{
			var_dump('YOU ARE NOT LOGGED IN?');
		}

		$this->user_sessions->set_property('Blah1', 'Value!');

		//testing if authorisation works with various constaints
		if($this->user_sessions->authorized(['admin_read', 'public_read'])){
			echo "I have admin view permissions and public read permissions\n";
		}else{
			echo "I do not have admin view permissions and public read permissions\n";
		}

		//testing if authorisation works with various constaints
		if($this->user_sessions->authorized(false, 'admin')){
			echo "I am an admin\n";
		}else{
			echo "I am not an admin\n";
		}

		if($this->user_sessions->authorized(false, false, ['administrator', 'anobody'])){
			echo "I am the administrator\n";
		}else{
			echo "I am not the administrator\n";
		}

		//who's the current user?
		if($this->user_sessions->get_user() == $this->accounts_manager->get_user(1)){
			echo "The current user is definitely the first user!\n";
		}else{
			echo "The current user is not the first user!\n";
		}

	}

	public function second(){

		//show these properties
		if($this->user_sessions->authorized()){
			var_dump('YOU ARE LOGGED IN!');
		}else{
			var_dump('YOU ARE NOT LOGGED IN?');
		}

		var_dump($this->user_sessions->get_properties());
		var_dump($this->user_sessions->get_user());

	}

	public function third(){

		//logout
		$this->user_sessions->set_property('unknown', 'this should not be seen!');
		$this->user_sessions->logout();
		$this->user_sessions->set_property('blah2', 'this should be seen!');
		var_dump($this->user_sessions->get_properties());
		if($this->user_sessions->authorized()){
			var_dump('YOU ARE LOGGED IN!');
		}else{
			var_dump('YOU ARE NOT LOGGED IN?');
		}

	}

	public function fourth(){

		//observe changes!
		var_dump($this->user_sessions->get_properties());
		var_dump($this->user_sessions->get_user());

	}

}