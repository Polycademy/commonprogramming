<?php

namespace PolyAuth\Accounts;

//for database
use PDO;
use PDOException;

//for logger
use Psr\Log\LoggerInterface;

//for options
use PolyAuth\Options;

//for languages
use PolyAuth\Language;

//for security
use PolyAuth\BcryptFallback;

//for registration
use PolyAuth\Emailer;

//for RBAC (to CRUD roles and permissions)
use PolyAuth\UserAccount;
use RBAC\Permission;
use RBAC\Role\Role;
use RBAC\Manager\RoleManager;

class AccountsManager{

	protected $db;
	protected $options;
	protected $lang;
	protected $logger;
	protected $role_manager;
	protected $emailer;
	protected $bcrypt_fallback = false;
	
	protected $errors = array();
	
	//expects PDO connection (potentially using $this->db->conn_id)
	//SessionInterface is a copy of the PHP5.4.0 SessionHandlerInterface, this allows backwards compatibility
	public function __construct(PDO $db, Options $options, LoggerInterface $logger = null){
	
		$this->options = $options;
		$this->lang = new Language;
		
		$this->db = $db;
		$this->logger = $logger;
		$this->role_manager  = new RoleManager($db, $logger);
		$this->emailer = new Emailer($db, $options, $logger);
		
		//if you use bcrypt fallback, you must always use bcrypt fallback, you cannot switch servers!
		if($this->options['hash_fallback']){
			$this->bcrypt_fallback = new BcryptFallback($this->options['hash_rounds']);
		}
		
	}
	
	//let's provide some registration
	//we'll accept some parameters, add them to the database, assign them any default roles
	//this doesn't do validation
	public function register($data){
		
		//login_data should have username, password or email
		if(empty($data[$this->options['login_identity']]) OR empty($data['password'])){
			$this->errors[] = $this->lang['account_creation_invalid'];
			return false;
		}
		
		if($this->options['email']){
			if(empty($data['email'])){
				$this->errors[] = $this->lang['account_creation_email_invalid'];
				return false;
			}
		}
		
		//check for duplicates based on identity
		if(!$this->identity_check($data[$this->options['login_identity']])){
			return false;
		}
		
		$data['ipAddress'] = $this->prepare_ip($_SERVER['REMOTE_ADDR']);
		$data['password'] = $this->hash_password($data['password'], $this->options['hash_method'], $this->options['hash_rounds']);
		
		$data += array(
		    'createdOn'	=> date('Y-m-d H:i:s'),
		    'lastLogin'	=> date('Y-m-d H:i:s'),
		    'active'	=> ($this->options['reg_activation'] === false ? 1 : 0),
		);
		
		//inserting activation code into the users table, if the reg_activation is by email
		if($this->options['reg_activation'] == 'email'){
			$data['activationCode'] = $this->generate_activation_code(); 
		}
		
		$column_string = implode(',', array_keys($data));
		$value_string = implode(',', array_fill(0, count($data), '?'));
		
		$query = "INSERT INTO {$this->options['table_users']} ({$column_string}) VALUES ({$value_string})";
		$sth = $this->db->prepare($query);
		
		try {
		
			$sth->execute(array_values($data));
			$last_insert_id = $sth->lastInsertId();
			
		}catch(PDOException $db_err){

			if($this->logger){
				$this->logger->error('Failed to execute query to register a new user and assign permissions.', ['exception' => $db_err]);
			}
			$this->errors[] = $this->lang['account_creation_unsuccessful'];
			return false;
			
		}
		
		//automatically send the activation email
		if($this->options['reg_activation'] == 'email' AND $this->options['email']){
			$this->emailer->send_activation_email($last_insert_id);
		}
		
		$registered_user = new UserAccount($last_insert_id);
		unset($data['password']);
		$registered_user->set_user_data($data);
		
		//now we've got to add the default roles and permissions
		if(!$registered_user = $this->register_roles($registered_user, array($this->options['role_default']))){
			return false;
		}
		
		return $registered_user;
		
	}
	
	//takes a user id and role object, and adds it to the user and saves it, the role object should have a list of permissions
	public function register_roles(UserAccount $user, array $role_names){
		
		foreach($role_names as $role_name){
		
			$role = $this->role_manager->roleFetchByName($role_name);
			
			if(!$this->role_manager->roleAddSubject($role, $user)){
				$this->errors[] = $this->lang['account_creation_assign_role'];
				return false;
			}
			
		}
		
		return $user;
	
	}
	
	protected function prepare_ip($ip_address) {
	
		$platform = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
		
		if($platform == 'pgsql' || $platform == 'sqlsrv' || $platform == 'mssql'){
			return $ip_address;
		}else{
			return inet_pton($ip_address);
		}
		
	}
	
	public function identity_check($identity){
		
		$query = "SELECT id FROM {$this->options['table_users']} WHERE {$this->options['login_identity']} = :identity";
		$sth = $this->db->prepare($query);
		$sth->bindParam(':identity', $identity, PDO::PARAM_STR);
		
		try {
		
			//there basically should be nothing returned, if something is returned then identity check fails
			$sth->execute();
			if($sth->fetch(PDO::FETCH_NUM) > 0){
				$this->errors[] = $this->lang["account_creation_duplicate_{$this->options['login_identity']}"];
				return false;
			}
			return true;
			
		}catch(PDOException $db_err){

			if($this->logger){
				$this->logger->error('Failed to execute query to check duplicate login identities.', ['exception' => $db_err]);
			}
			$this->errors[] = $this->lang['account_creation_unsuccessful'];
			return false;
			
		}
	
	}
	
	public function generate_activation_code(){
	
		return sha1(md5(microtime()));
	
	}
	
	//given the activation code and user id?
	public function activate($user_id, $activation_code = false){
	
		if(!$activation_code){
			//force activate (if the activation code doesn't exist)
			return $this->force_activate($user_id);
		}
	
		//if the activation code matches with the user_id's activation code, then update the row to make it active!
		$query = "SELECT id from {$this->options['table_users']} WHERE id = :id AND activationCode = :activation_code";
		$sth = $this->db->prepare($query);
		$sth->bindParam(':id', $user_id, PDO::PARAM_INT);
		$sth->bindParam(':activation_code', $user_id, PDO::PARAM_STR);
		
		try{
		
			//test if there are any results
			$sth->execute();
			
			if($sth->fetch(PDO::FETCH_NUM) > 0){
			
				//we got a match, let's activate them!
				return $this->force_activate($user_id);
				
			}else{
			
				//no match, no activation
				$this->errors[] = $this->lang['activate_unsuccessful'];
				return false;
				
			}
		
		}catch(PDOException $db_err){
		
			if($this->logger){
				$this->logger->error('Failed to execute query to grab the user with the relevant id and activation code.', ['exception' => $db_err]);
			}
			$this->errors[] = $this->lang['activate_unsuccessful'];
			return false;
		
		}
	
	}
	
	protected function force_activate($user_id){
	
		$query = "UPDATE {$this->options['table_users']} SET active = 1, activationCode = '' WHERE id = :id";
		$sth = $this->db->prepare($query);
		$sth->bindParam(':id', $user_id, PDO::PARAM_INT);
		
		try{
		
			$sth->execute();
			return true;
		
		}catch(PDOException $db_err){
		
			if($this->logger){
				$this->logger->error("Failed to execute query to activate user $user_id.", ['exception' => $db_err]);
			}
			$this->errors[] = $this->lang['activate_unsuccessful'];
			return false;
		
		}
		
	}
	
	//deactivates based on a user_id
	public function deactivate($user_id){
	
		//generate new activation code and return it if it was successful
		$activation_code = generate_activation_code();
		$query = "UPDATE {$this->options['table_users']} SET active = 0, activationCode = :activation_code WHERE id = :id";
		$sth = $this->db->prepare($query);
		$sth->bindParam(':activation_code', $activation_code, PDO::PARAM_STR);
		$sth->bindParam(':id', $user_id, PDO::PARAM_INT);
		
		try{
		
			$sth->execute();
			return $activation_code;
		
		}catch(PDOException $db_err){
		
			if($this->logger){
				$this->logger->error("Failed to execute query to deactivate user $user_id.", ['exception' => $db_err]);
			}
			$this->errors[] = $this->lang['deactivate_unsuccessful'];
			return false;
		
		}
		
	
	}
	
	//forgot identity or password
	public function forgotten_identity(){
	
	}
	
	public function forgotten_password(){
	
	}
	
	//checks if the OTP is correct and within the time limit
	public function forgotten_check(){
	
	}
	
	public function forgotten_complete(){
	
	}
	
	public function clear_forgotten(){
	
	}
	
	public function reset_password(){
	
	}
	
	public function change_password(){
	
	}
	
	public function get_user(){
	
		//return the RBAC user object, which you can test for permissions or grab its user data or session data
		
	}
	
	//get all users based on roles
	public function get_user_by_role(){
	
		//returns an array of RBAC user objects
	
	}
	
	//get all the user by the permission
	public function get_user_by_permission(){
	
		//returns an array of RBAC user objects
	
	}
	
	public function hash_password($password, $method, $cost){
	
		if(!$this->bcrypt_fallback){
			$hash = password_hash($password, $method, ['cost' => $cost]);
		}else{
			$hash = $this->bcrypt_fallback->hash($password);
		}
		return $hash;
		
	}
	
	public function hash_password_verify($password, $hash){
	
		if(!$this->bcrypt_fallback){
			if(password_verify($password, $hash)){
				return true;
			} else {
				return false;
			}
		}else{
			if($this->bcrypt_fallback->verify($password, $hash)){
				return true;
			}else{
				return false;
			}
		}
		
	}
	
	public function get_errors(){
		if(!empty($this->errors)){
			return $this->errors;
		}else{
			return false;
		}
	}

}