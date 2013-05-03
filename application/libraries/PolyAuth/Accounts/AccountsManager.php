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
use PolyAuth\Accounts\BcryptFallback;

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
	
	/**
	 * Register a new user. It adds some default data and role/permissions. It also handles the activation emails.
	 *
	 * @param $data array - $data parameter corresponds to user columns or properties. Make sure the identity and password and any other insertable properties are part of it.
	 * @return $registered_user object - This is a fully loaded user object containing its roles and user data.
	 */
	public function register(array $data){
		
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
			$data['activationCode'] = $this->generate_code(); 
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
		
		$registered_user = new UserAccount($last_insert_id);
		unset($data['password']);
		$registered_user->set_user_data($data);
		
		//now we've got to add the default roles and permissions
		if(!$registered_user = $this->register_roles($registered_user, array($this->options['role_default']))){
			return false;
		}
		
		//automatically send the activation email
		if($this->options['reg_activation'] == 'email' AND $this->options['email'] AND $registered_user->email){
			$this->emailer->send_activation($registered_user);
		}
		
		return $registered_user;
		
	}
	
	/**
	 * Removes a user
	 *
	 * @param $user object
	 * @return boolean
	 */
	public function deregister(UserAccount $user){
	
		$query = "DELETE FROM {$this->options['table_users']} WHERE id = :user_id";
		$sth = $this->db->prepare($query);
		$sth->bindParam(':user_id', $user->id, PDO::PARAM_INT);
		
		try{
		
			$sth->execute();
			
			if($sth->rowCount >= 1){
				return true;
			}
			
			$this->errors[] = $this->lang['delete_already'];
			return false;
			
		}catch(PDOException $db_err){
		
			if($this->logger){
				$this->logger->error('Failed to execute query to delete a user.', ['exception' => $db_err]);
			}
			$this->errors[] = $this->lang['delete_unsuccessful'];
			return false;
		
		}
	
	}
	
	/**
	 * Checks for duplicate identity
	 *
	 * @param $identity string - depends on the options
	 * @return boolean
	 */
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
	
	protected function prepare_ip($ip_address) {
	
		$platform = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
		
		if($platform == 'pgsql' || $platform == 'sqlsrv' || $platform == 'mssql'){
			return $ip_address;
		}else{
			return inet_pton($ip_address);
		}
		
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
	
	public function generate_code(){
	
		return sha1(md5(microtime()));
	
	}
	
	/**
	 * Activates the new user
	 *
	 * @param $user object
	 * @param $activation_code string - this is optional so you can manually activate a user without checking the activation code
	 * @return boolean
	 */
	public function activate(UserAccount $user, $activation_code = false){
	
		if(!$activation_code){
			//force activate (if the activation code doesn't exist)
			return $this->force_activate($user->id);
		}
	
		//if the activation code matches with the user_id's activation code, then update the row to make it active!
		$query = "SELECT id from {$this->options['table_users']} WHERE id = :id AND activationCode = :activation_code";
		$sth = $this->db->prepare($query);
		$sth->bindParam(':id', $user->id, PDO::PARAM_INT);
		$sth->bindParam(':activation_code', $activation_code, PDO::PARAM_STR);
		
		try{
		
			//test if there are any results
			$sth->execute();
			
			if($sth->fetch(PDO::FETCH_NUM) > 0){
			
				//we got a match, let's activate them!
				return $this->force_activate($user->id);
				
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
	
	//use this function when someone needs to be resent the activation emails, or be reactivated
	public function reactivate(){
	
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
	
	/**
	 * Deactivates user
	 *
	 * @param $user object
	 * @return boolean
	 */
	public function deactivate(UserAccount $user){
	
		//generate new activation code and return it if it was successful
		$activation_code = generate_code();
		$query = "UPDATE {$this->options['table_users']} SET active = 0, activationCode = :activation_code WHERE id = :id";
		$sth = $this->db->prepare($query);
		$sth->bindParam(':activation_code', $activation_code, PDO::PARAM_STR);
		$sth->bindParam(':id', $user->id, PDO::PARAM_INT);
		
		try{
		
			$sth->execute();
			return $activation_code;
		
		}catch(PDOException $db_err){
		
			if($this->logger){
				$this->logger->error("Failed to execute query to deactivate user {$user->id}.", ['exception' => $db_err]);
			}
			$this->errors[] = $this->lang['deactivate_unsuccessful'];
			return false;
		
		}
		
	
	}
	
	/**
	 * Forgotten identity, run this after you have done some identity validation such as security questions.
	 * This sends the identity to the user's email.
	 *
	 * @param $user object
	 * @return boolean
	 */
	public function forgotten_identity(UserAccount $user){
	
		return $this->emailer->send_forgotten_identity($user);
	
	}
	
	/**
	 * Forgotten password, run this after you have done some identity validation such as security questions.
	 * Generates a forgotten code and forgotten time
	 *
	 * @param $user object
	 * @return boolean
	 */
	public function forgotten_password(UserAccount $user){
	
		$user->forgottenCode = $this->generate_code();
		$user->forgottenDate = date('Y-m-d H:i:s');
		
		$query = "UPDATE {$this->options['table_users']} SET forgottenCode = :forgotten_code, forgottenDate = :forgotten_date WHERE id = :user_id";
		$sth = $this->db->prepare($query);
		$sth->bindParam('forgotten_code', $user->forgottenCode PDO::PARAM_STR);
		$sth->bindParam('forgotten_date', $user->forgottenDate, PDO::PARAM_STR);
		$sth->bindParam('user_id', $user->id, PDO::PARAM_INT);
		
		try{
		
			$sth->execute();
			if($sth->rowCount < 1){
				//no one was updated
				$this->errors[] = $this->lang['forgot_unsuccessful'];
				return false;
			}
			
			//continue to send email
			return $this->emailer->send_forgot_password($user);
		
		}catch(PDOException $db_err){
		
			if($this->logger){
				$this->logger->error("Failed to execute query to update user with forgotten code and date", ['exception' => $db_err]);
			}
			$this->errors[] = $this->lang['deactivate_unsuccessful'];
			return false;
		
		}
	
	}
	
	//checks if the OTP is correct and within the time limit
	//make sure to see if time limit is 0, otherwise, the time limit is forever!
	public function forgotten_check(UserAccount $user, $activation_code){
	
	}
	
	//if the forgotten check goes through. Updates with a new password and clear the forgotten
	public function forgotten_complete(UserAccount $user, $new_password){
	
		//hashing and whateva
	
	}
	
	//on finish of forgotten complete or when the forgotten time has exceeded its time limit
	public function clear_forgotten(UserAccount $user){
	
	}
	
	public function reset_password(){
	
	}
	
	public function change_password(){
	
	}
	
	public function get_users(){
	
		//return all users as RBAC objects
	
	}
	
	public function get_user($user_id){
	
		//return the RBAC user object, which you can test for permissions or grab its user data or session data
		
	}
	
	//get all users based on array of roles
	public function get_user_by_role(array $roles){
	
		//returns an array of RBAC user objects
	
	}
	
	//get all the users based on array of permissions
	public function get_user_by_permission(array $permissions){
	
		//returns an array of RBAC user objects
	
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
	
	public function get_errors(){
		if(!empty($this->errors)){
			return $this->errors;
		}else{
			return false;
		}
	}

}