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
use PolyAuth\Accounts\PasswordComplexity;

//for RBAC (to CRUD roles and permissions)
use PolyAuth\UserAccount;
use RBAC\Permission;
use RBAC\Role\Role;
use RBAC\Manager\RoleManager;

//for registration
use PolyAuth\Emailer;

class AccountsManager{

	protected $db;
	protected $options;
	protected $lang;
	protected $logger;
	protected $password_manager;
	protected $role_manager;
	protected $emailer;
	protected $bcrypt_fallback = false;
	
	protected $errors = array();
	
	//expects PDO connection (potentially using $this->db->conn_id)
	//SessionInterface is a copy of the PHP5.4.0 SessionHandlerInterface, this allows backwards compatibility
	public function __construct(PDO $db, Options $options, Language $language, LoggerInterface $logger = null){
	
		$this->options = $options;
		$this->lang = $language;
		
		$this->db = $db;
		$this->logger = $logger;
		$this->password_manager = new PasswordComplexity($options, $language);
		$this->role_manager  = new RoleManager($db, $logger);
		$this->emailer = new Emailer($db, $options, $language, $logger);
		
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
	 * Checks for duplicate identity, returns false if the identity already exists, returns true if identity doesn't exist
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
	 * Either resends the activation email, or it can be used to manually begin sending the activation email.
	 * It regenerates the activation code as well.
	 *
	 * @param $user object
	 * @return boolean
	 */
	public function reactivate(UserAccount $user){
	
		if($this->deactivate($user)){
		
			//we don't need to check what the reg_activation is, give options to the end user
			if($this->options['email'] AND $user->email){
				//$user will contain the new activation code
				return $this->emailer->send_activation($user);
			}
		
		}
		
		return false;
	
	}
	
	/**
	 * Activates the new user given the activation code, this is used after the activation email has been sent and received
	 * Can also be used to manually activate
	 *
	 * @param $user object
	 * @param $activation_code string - this is optional so you can manually activate a user without checking the activation code
	 * @return boolean
	 */
	public function activate(UserAccount $user, $activation_code = false){
	
		if(!$activation_code){
			//force activate (if the activation code doesn't exist)
			return $this->force_activate($user);
		}
		
		//$user will already contain the activationCode and id
		if($user->activationCode == $activation_code){
			return $this->force_activate($user);
		}
		
		$this->errors[] = $this->lang['activate_unsuccessful'];
		return false;
	
	}
	
	protected function force_activate($user){
	
		$query = "UPDATE {$this->options['table_users']} SET active = 1, activationCode = NULL WHERE id = :id";
		$sth = $this->db->prepare($query);
		$sth->bindParam(':id', $user->id, PDO::PARAM_INT);
		
		try{
		
			$sth->execute();
			$user->active = 1;
			$user->activationCode = null;
			return true;
		
		}catch(PDOException $db_err){
		
			if($this->logger){
				$this->logger->error("Failed to execute query to activate user {$user->id}.", ['exception' => $db_err]);
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
			$user->active = 0;
			$user->activationCode = $activation_code;
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
	
	/**
	 * Checks if the forgotten code is valid and that it has been used within the time limit
	 *
	 * @param $user object
	 * @param $forgotten_code string
	 * @return boolean
	 */
	public function forgotten_check(UserAccount $user, $forgotten_code){
	
		//check if there is such thing as a forgottenCode and forgottenTime
		if(!empty($user->forgottenCode) AND $user->forgottenCode == $forgotten_code){
		
			$allowed_duration = $this->options['login_forgot_expiration'];
			
			if($allowed_duration != 0){
		
				$forgotten_time = strtotime($user->forgottenTime);
				//add the allowed duration the forgotten time
				$forgotten_time_duration = strtotime("+ $allowed_duration seconds", $forgotten_time);
				//compare with the current time
				$current_time = strtotime(date('Y-m-d H:i:s'));
				
				if($current_time > $forgotten_time_duration){
				
					//we have exceeded the time, so we need to clear the forgotten so that it defaults back to normal
					//or else there'd be no way of resolving this issue
					$this->forgotten_clear($user);
					$this->errors[] = $this->lang['forgot_check_unsuccessful'];
					return false;
				
				}
			
			}
			
			//at this point everything should be good to go
			return true;
		
		}

		//if the forgottenCode doesn't exist or the code doesn't match, then we just return false, no need to clear
		$this->errors[] = $this->lang['forgot_check_unsuccessful'];
		return false;
	
	}
	
	/**
	 * Finishes the forgotten cycle, clears the forgotten code and updates the user with the new password
	 *
	 * @param $user object
	 * @param $forgotten_code string
	 * @return boolean
	 */
	public function forgotten_complete(UserAccount $user, $new_password){
	
		//clear the forgotten first and update with new password
		if($this->forgotten_clear($user) AND $this->change_password($user, $new_password)){
			return true;
		}
		return false;
	
	}
	
	/**
	 * Clears the forgotten code and forgotten time when we have completed the cycle or if the time limit was exceeded
	 *
	 * @param $user object
	 * @return boolean
	 */
	public function forgotten_clear(UserAccount $user){
	
		$query = "UPDATE {$this->options['table_users']} SET forgottenCode = NULL, forgottenTime = NULL WHERE id = :user_id";
		$sth = $this->db->prepare($query);
		$sth->bindParam('user_id', $user->id, PDO::PARAM_INT);
		
		try{
		
			$sth->execute();
			if($sth->rowCount < 1){
				//no one was updated
				$this->errors[] = $this->lang['forgot_unsuccessful'];
				return false;
			}
			
			$user->forgottenCode = null;
			$user->forgottenTime = null;
			
			return true;
		
		}catch(PDOException $db_err){
		
			if($this->logger){
				$this->logger->error("Failed to execute query to clear the forgotten code and forgotten time.", ['exception' => $db_err]);
			}
			$this->errors[] = $this->lang['forgot_unsuccessful'];
			return false;
		
		}
	
	}
	
	//allow old_password to be optional, this forces a new password regardless of password checks
	public function change_password($user, $new_password, $old_password = false){
	
		//check old password if it exists
		//password complexity check (depends on old password do the double password check)
		//hash new password
		//update with new password
		//return with boolean
	
	}
	
	//not used in this library, but this will randomise the password and return the actual password (not the hash)
	public function reset_password($user){
	
		//randomise the password
		//pass the password complexity tests
		//update
		//pass back actual password
	
	}
	
	//THIS IS WHAT YOU USE ALWAYS TO GET A USER
	public function get_user($user_id){
	
		//return the RBAC user object, which you can test for permissions or grab its user data or session data
		
		//return false if user does not exist!
		
	}
	
	public function get_users(){
	
		//return all users as RBAC objects
	
	}
	
	//get all users based on array of roles
	public function get_users_by_role(array $roles){
	
		//returns an array of RBAC user objects
	
	}
	
	//get all the users based on array of permissions
	public function get_users_by_permission(array $permissions){
	
		//returns an array of RBAC user objects
	
	}
	
	//show all the permissions of a role
	//accepts an array of roles, and returns an array of roles to description and roles to permission to description
	public function get_roles_permissions(array $requested_roles){
	
		/*
		$array = array(
			'admin'	=> array(
				'desc' => 'Administrators Role',
				'perms'	=> array(
					'perm' => 'Description of the Perm'
				),
			),
		);
		*/
	
	}
	
	//uses a similar array to the above!
	//creates the permissions, assigns them to the roles, saves them
	public function register_roles_permissions(){
	
		//if a role doesn't exist, create it
		//if a role does exist, update it (this means updating the list of permissions, not necessarily adding to it)
		//example:
		
		//role: admin - admin_desc
		//permission: admin_view - desc
		//permission: admin_edit - desc
		
		//if role doesn't exist, create it
		//if admin does exist
		//and the passed in update is
		//role: admin - different_desc
		//permission: admin_edit - new_desc
		//then the role will be updated to only contain that permission and the other associated updates
	
	}
	
	//same kind of thing, specify roles to delete
	//or specify roles to permissions to delete
	public function delete_roles_permissions(array $roles_permissions){
	
		//expects array: ('role' => array('perm', 'perm');)
		//OR array: ('role1', 'role2')
		//OR array: ('role1', 'role2' => array('perm'));
		//it either deletes a role completely OR deletes a permission as part of a role, but keeps the role
	
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