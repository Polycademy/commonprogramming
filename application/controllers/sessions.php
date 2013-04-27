<?php

use Polycademy\Validation\Validator;

//in this class, $ids refer to the user ids, not the session ids!
class Sessions extends CI_Controller{

	private $validator;

	public function __construct(){
		
		parent::__construct();
		
		$this->load->library('ion_auth');
		$this->load->library('encrypt');
		$this->load->driver('session');
		$this->validator = new Validator;
	
	}
	
	//give back information about all the user's session (if you're admin)
	public function index(){
	
		if($this->ion_auth->is_admin()){
		
			//show all current sessions (not all current users)	 (must be using sessions tables)
			$query = $this->db->get($this->config->item('sess_table_name'));
			
			if($query->num_rows() > 0){
			
				foreach($query->result() as $row){
				
					//have to unserialise the custom data
					$custom_data = $this->unserialize($row->user_data);
					
					$user_sessions[] = array(
						'type'			=> (!empty($row->user_data)) ? 'member' : 'guest',
						'session_id'	=> $row->session_id,
						'ip_address'	=> $row->ip_address, //ip_address from sessions
						'user_agent'	=> $row->user_agent,
						'last_activity'	=> $row->last_activity,
						'user_data'		=> $custom_data,
					);
					
				}
				
				$output = array(
					'content'	=> $user_sessions,
					'code'		=> 'success',
				);
				
			}else{
			
				$this->output->set_status_header('404');
				$output = array(
					'content'	=> 'No one is currently logged in.',
					'code'		=> 'error',
				);
			
			}
		
		}else{
			
			//unauthorised permission
			$this->output->set_status_header(403);
			
			$output = array(
				'content'	=> 'You don\'t have the authorisation to see all the sessions!',
				'code'		=> 'error',
			);
		
		}
		
		Template::compose(false, $output, 'json');
		
	}
	
	private function unserialize($data){
	
		$data = @unserialize(trim($data));
		if (is_array($data)){
			array_walk_recursive($data, array(&$this, 'unescape_slashes'));
			return $data;
		}

		return is_string($data) ? str_replace('{{slash}}', '\\', $data) : $data;
		
	}
	
	private function unescape_slashes(&$val, $key){
	
		if (is_string($val)){
	 		$val = str_replace('{{slash}}', '\\', $val);
		}
	
	}
	
	//show the the session data relating to user id
	//can only show current person's session, $id is just for REST
	//this is the function that will be utilised at startup!
	public function show($id){
	
		if($id == 0){
		
			//if $id is 0, just grab the current session
			$output = array(
				'content'	=> $this->session->all_userdata(),
				'code'		=> 'success',
			);
			
			$user_data = $this->ion_auth->user()->row();
			
			if(!empty($user_data)){
				$user_id = $user_data->id;
				//we want to store the userId in a different place, as this could overwrite the session id
				$output['content']['userId'] = $user_id;
			}
		
		}else{
		
			//grab a specified session, either the person must own it, or the person is an admin
			//not yet implemented
		
		}
		
		Template::compose(false, $output, 'json');
		
	}
	
	//create a session! used for login
	public function create(){
	
		//only create a new session, if the person is not logged in
		if(!$this->ion_auth->logged_in()){
			
			//check if data is validated
			$data = $this->input->json(false, true);
			
			//THIS depends on the fact that you set the username as the identity field in the config
			$this->validator->setup_rules(array(
				'username'		=> array(
					'set_label:Username',
					'NotEmpty',
					'AlphaNumericSpace',
					'MinLength:4',
					'MaxLength:100',
				),
				'password'		=> array(
					'set_label:Password',
					'NotEmpty',
					'AlphaSlug',
					'MinLength:8',
					'MaxLength:80'
				),
				'rememberMe'	=> array( //<- does not correspond with table column's name
					'set_label:Remember Me',
					'MaxLength:1',
				),
			));
			
			if(!$this->validator->is_valid($data)){
			
				$this->output->set_status_header(400);
				
				$output = array(
					'content'	=> $this->validator->get_errors(),
					'code'		=> 'validation_error',
				);
			
			}else{
			
				//validator passed
				//check if data is authenticated
				
				$remember_me = (isset($data['rememberMe'])) ? (bool) $data['rememberMe'] : false;
				
				if($this->ion_auth->login($data['username'], $data['password'], $remember_me)){
					
					$current_user = $this->ion_auth->user()->row();
					
					//logged in
					$this->output->set_status_header(201);
					
					$output = array(
						'content'	=> $current_user->id,
						'code'		=> 'success',
					);
					
				}else{
				
					//not logged in
					$this->output->set_status_header(400); //fudged, make it a 400 code, cant use 401, and cant use 403 due to redirection possibility
					
					$output = array(
						'content'	=> $this->ion_auth->errors_array(),
						'code'		=> 'validation_error',
					);
				
				}
				
			}
			
		}else{
		
			//if the person is already logged in, then no need to do it
			//return the resource ID of the current user
			$current_user = $this->ion_auth->user()->row();
			
			$this->output->set_status_header(200);
			
			$output = array(
				'content'	=> $current_user->id,
				'code'		=> 'success',
			);
		
		}
		
		Template::compose(false, $output, 'json');
	
	}
	
	//not implemented yet (possibly for shopping cart)
	//$id should be the user id, session id is encrypted
	public function update($id){
		return false;
	}
	
	//used to delete a session
	//logout only works for the person who is logged in, you cannot log somebody else out!
	//$id is only for REST currently
	public function delete($id){
	
		//only delete if the person is logged in
		if($this->ion_auth->logged_in()){
			
			$current_user = $this->ion_auth->user()->row();
			
			$this->ion_auth->logout();
			
			$output = array(
				'content'	=> $current_user->id,
				'code'		=> 'success',
			);
			
			//this function should check for 0, to logout the current person, if not 0, logout a particular person...
		
		}else{
			
			//no resource to delete
			$this->output->set_status_header(200);
			
			$output = array(
				'content'	=> 'You cannot log out when you are not logged in.',
				'code'		=> 'error',
			);
		
		}
		
		Template::compose(false, $output, 'json');
		
	}
	
	//group id is by default 0, if it is 0, then anybody can access
	public function cli_check_ws_session($group_id = 0){

		//grab the data from stdin
		//decrypt the cookie data
		//grab the session id
		//check if the session id exists
		//reference the session id to the user id the userId is stored on the user_data field in the database, just need to unserialise it!
		//check if the user is part of the group		
		
		//json encoded data coming in as payload in STDIN (assume it's an array of cookies)
		$cookies_data = $this->input->stdin(false, true, 'json');
		
		//now we need to get the correct cookie data
		$cookie_name = $this->config->item('cookie_prefix') . $this->config->item('sess_cookie_name');
		
		$session = $cookies_data[$cookie_name];
		
		//if no cookie, echo false
		if($session === NULL){
			//echo 'There was no session cookie found!';
			exit('false'); //exit will display it
		}
		
		$len = strlen($session) - 40;
		if($len < 0){
			//echo 'Cookie is not right!';
			exit('false');
		}		
		
		// Check cookie authentication
		$hmac	 = substr($session, $len);
		$session = substr($session, 0, $len);
		$encryption_key = $this->config->item('encryption_key');
		
		if($hmac !== hash_hmac('sha1', $session, $encryption_key)){
			//echo 'Cookie has been tampered with!';
			exit('false');
		}		
		
		if($this->config->item('sess_encrypt_cookie')){
			$session = $this->encrypt->decode($session);
		}		
		
		//yay we got the session!
		$session = $this->unserialize($session);		
		
		//there's a number of other checks on the session, but I think it is unnecessary... (check Session_cookie.php for more)
		
		//now this will rely on us using the database! or else it will not work what so ever
		if(!$this->config->item('sess_use_database')){
			//echo 'You need to use a database for sessions to do this!';
			exit('false');
		}
			
		//just check if the session id exists (there's a number of extra checks, but unnecessary atm)
		$this->db->where('session_id', $session['session_id']);
		
		$db_cache = $this->db->cache_on;
		$this->db->cache_off();
		
		$query = $this->db->limit(1)->get($this->config->item('sess_table_name'));
		
		if ($db_cache){
			// Turn it back on
			$this->db->cache_on();
		}		
		
		// no session was recognised!
		if(empty($query) OR $query->num_rows() === 0){
			//echo 'No session was found to correspond with the session id!';
			exit('false');
		}
		
		$session_row = $query->row();
		
		//if user data exists (this person is not a guest!)
		if(!empty($session_row->user_data)){
			
			//check against the group id
			$custom_data = $this->unserialize($session_row->user_data);
			$user_id = $custom_data['userId'];
			
			//at this point, if the group_id is 0, we don't care what the group is, just send it through
			if($group_id == 0){
				$output = (array) $session_row;
				$output += $custom_data;
				$output = json_encode($output);
				exit($output);
			}
			
			$query = $this->db->get_where('users_groups', array('userId' => $user_id));
			
			foreach($query->result() as $row){
			
				//if we can find one of the groupId to match $group_id, exit true	
				if($row->groupId == $group_id){
					//echo "YAY WE GOT IT!\n";
					$output = (array) $session_row;
					$output += $custom_data;
					$output = json_encode($output);
					exit($output);
				}
				
			}
			
			//echo "Session was found in the database, and there was corresponding user data, however the user was not part of the group!\n";
			exit('false');
			
		}elseif($group_id == 0){
		
			$output = (array) $session_row;
			$output = json_encode($output);
			exit($output);
			
		}
		
		exit('false');
	
	}

}