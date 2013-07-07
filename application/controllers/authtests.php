<?php

use PolyAuth\Exceptions\PolyAuthException;

class Authtests extends CI_Controller{

	protected $accounts_manager;
	protected $user_sessions;
	
	public function __construct(){
	
		parent::__construct();

		$ioc = $this->config->item('ioc');

		$this->accounts_manager = $ioc['PolyAuth\Accounts\AccountsManager'];
		$this->user_sessions = $ioc['PolyAuth\Sessions\UserSessions'];
		
	}
	
	public function index(){

		echo '<pre>';

		$this->user_sessions->start();

		//WE NEED to see if the session is kept. Simulate a proper signing in maneouver! The session id is getting refreshed too much...

		//let's test a basic login
		//you need to capture the "PolyAuthException", it's separate from DB related exceptions
		//errors can be acquired as an array from get_errors()
		try{
			$this->user_sessions->login(array(
				'identity'	=> 'administrator',
				'password'	=> 'password'
			));
			echo "I am logged in \n";
		}catch(PolyAuthException $e){
			var_dump($e->get_errors());
		}

		//try testing if the authentication works
		if($this->user_sessions->authorized()){
			echo "I am authorized!\n";
		}else{
			echo "I am not authorised!\n";
		}

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

		//lets try out the current session!
		$session = $this->user_sessions->get_session();
		var_dump($session->user_id);
		var_dump($session->anonymous);
		var_dump($session->timeout);
		$this->user_sessions->set_property('something', 'blah1');
		$this->user_sessions->delete_property('something');
		var_dump($session->something);
		//flash values, custom session data do not work with HTTP basic due to the stateless aspect
		$this->user_sessions->set_property('keep', 'blah2', true);
		var_dump($session->getFlash('keep'));
		
		echo '</pre>';

	}

}