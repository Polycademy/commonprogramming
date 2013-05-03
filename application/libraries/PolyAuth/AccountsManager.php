<?php

//RBAC SQL with Configuration
//PolyAuth SQL with Configuration
	//Username
	//Password
	//Email
	//Custom Data.. specified by SQL schema (allow any number of it)
//both are using Codeigniter's migrations?
//requires PDO
//requires Aura Session
//requires RBAC
//requires password_compat (this will be loaded automatically and will be available until 5.5)
//requires PSR's logger

namespace PolyAuth;

//for database
use PDO;
use PDOException;

//for sessions
use PolyAuth\SessionInterface;
use PolyAuth\CookieManager;
use Aura\Session\Manager as SessionManager;
use Aura\Session\SegmentFactory;
use Aura\Session\CsrfTokenFactory;

//for RBAC
use PolyAuth\UserAccount;
use RBAC\Permission;
use RBAC\Role\Role;
use RBAC\Manager\RoleManager;

//for logger
use Psr\Log\LoggerInterface;

//for security
use PolyAuth\BcryptFallback;

//for languages
use PolyAuth\Language;

class AccountsManager{

	protected $options;
	protected $lang;
	protected $db;
	protected $logger;
	protected $cookie_manager;
	protected $session_manager;
	protected $role_manager;
	protected $mailer;
	protected $bcrypt_fallback = false;
	
	protected $user; //this is used to represent the user account for the RBAC, it is only initialised when a person logs in, it is not be used for any other purposes, always must represent the currently logged in user
	
	protected $errors = array();
	
	//expects PDO connection (potentially using $this->db->conn_id)
	//SessionInterface is a copy of the PHP5.4.0 SessionHandlerInterface, this allows backwards compatibility
	public function __construct($options = false, PDO $db, SessionInterface $session_handler = null, LoggerInterface $logger = null){
	
		$this->configure($options);
		$this->lang = new Language;
		
		$this->db = $db;
		$this->logger = $logger;
		$this->set_session_handler($session_handler);
		$this->cookie_manager = new CookieManager(
			$this->options['cookie_domain'],
			$this->options['cookie_path'],
			$this->options['cookie_prefix'],
			$this->options['cookie_secure'],
			$this->options['cookie_httponly']
		);
		$this->session_manager = new SessionManager(new SegmentFactory, new CsrfTokenFactory);
		$this->role_manager  = new RoleManager($db, $logger);
		$this->mailer = new PHPMailer;
		
		//if you use bcrypt fallback, you must always use bcrypt fallback, you cannot switch servers!
		if($this->options['hash_fallback']){
			$this->bcrypt_fallback = new BcryptFallback($this->options['hash_rounds']);
		}
		
		$this->startyourengines();
		
	}
	
	public function configure($options){
		
		$this->options = array(
			//table options, see that the migration to be reflected. (RBAC options are not negotiable)
			'table_users'						=> 'user_accounts',
			'table_login_attempts'				=> 'login_attempts',
			//security options
			'hash_fallback'						=> false, //set whether to use bcrypt fallback (if you're behind 5.3.7 in PHP version, this will not seamlessly upgrade, if you switch PHP versions, make sure to rehash your passwords manually)
			'hash_method'						=> PASSWORD_DEFAULT,	//can be PASSWORD_DEFAULT or PASSWORD_BCRYPT
			'hash_rounds'						=> 10,
			//session options
			'session_encrypt'					=> true, //should the session data be encrypted? (only for the cookie)
			'session_key'						=> 'hiddenpassword', //session encryption key, any number of characters and depends on session_encrypt
			//cookie options
			'cookie_domain'						=> '',
			'cookie_path'						=> '/',
			'cookie_prefix'						=> '',
			'cookie_secure'						=> false,
			'cookie_httponly'					=> false,
			//email options (email data should be passed in as a string, end user manages their own stuff)
			'email'								=> false, //make this true to use the emails by PHPMailer, otherwise false if you want to roll your own email solution, watch out for email activation
			'email_smtp'						=> false,
			'email_host'						=> '',
			'email_auth'						=> false,
			'email_username'					=> '',
			'email_password'					=> '',
			'email_smtp_secure'					=> '', //tls or ssl or false
			'email_from'						=> 'enquiry@polycademy.com',
			'email_from_name'					=> 'Polycademy',
			'email_replyto'						=> false, //can be an email or false
			'email_replyto_name'				=> '',
			'email_cc'							=> false,
			'email_bcc'							=> false,
			'email_type'						=> 'html', //can be text or html
			'email_activation_template'			=> 'Here is your activation code: {{activation_code}} and here is your user id: {{user_id}}. Here is an example link http://example.com/?activation_code={{activation_code}}&user_id={{user_id}}',
			'email_forgotten_template'			=> 'Here is your temporary login: {{temporary_login_code}} and here is your user id: {{user_id}}. Here is an example link Here is an example link http://example.com/?temporary_login_code={{temporary_login_code}}&user_id={{user_id}}',
			//rbac options (initial roles from the migration, also who's the default role, and root access role?)
			'role_default'						=> 'members',
			//login options (this is the field used to login with, plus login attempts)
			'login_identity'					=> 'username', //can be email or username
			'login_password_minlength'			=> 8,
			'login_password_maxlength'			=> 20,
			'login_persistent'					=> true, //allowing remember me or not
			'login_expiration'					=> 86500, // How long to remember the user (seconds). Set to zero for no expiration
			'login_expiration_extend'			=> true, //allowing whether autologin extends the login_expiration
			'login_attempts'					=> 0, //if 0, then it is disabled
			'login_lockout'						=> 0, //lockout time in seconds
			'login_forgot_password_expiration'	=> 0, //how long before the temporary password expires
			//registration options
			'reg_activation'					=> false, //can be email, manual, or false
		);
		
		if($options != false){
			//this will override the default options
			$this->options = array_merge($this->options, $options);
		}
	
	}
	
	protected function set_session_handler($session_handler){
	
		if($session_handler === null){
			return;
		}
		
		//second parameter is to register the shutdown function
		//make sure this runs before sessions are started
		session_set_save_handler($session_handler, true);
	
	}
	
	protected function startyourengines(){
	
		//immediately logs the person in if they have identity, rememberCode and are not currently logged in
		if(!$this->logged_in() && $this->cookie_manager->get_cookie('identity') && $this->cookie_manager->get_cookie('rememberCode')){
			$this->login_remembered_user();
		}
		
		//... continue
		
	
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
		
		$registered_user = new UserAccount($last_insert_id);
		unset($data['password']);
		$registered_user->set_user_data($data);
		
		//now we've got to add the default roles and permissions
		if(!$registered_user = $this->register_roles($registered_user, array($this->options['role_default']))){
			return false;
		}
		
		return $registered_user;
		
	}
	
	//assume $body has {{activation_code}}
	//this can be sent multiple times, the activation code doesn't change (so the concept of resend activation email)
	public function send_activation_email($user_id, $subject = false, $body = false, $alt_body = false){
	
		if($this->options['reg_activation'] == 'email' AND $this->options['email']){
		
			$subject = (empty($subject)) ? $this->lang('email_activation_subject') : $subject;
			$body = (empty($body)) ? $this->options['email_activation_template'] : $body;
			
			//take the user_id, grab the person's email and activation_code
			$query = "SELECT email, activationCode FROM {$this->options['table_users']} WHERE id = :id";
			$sth = $this->db->prepare($query);
			$sth = $this->db->bindParam(':id', $user_id, PDO::PARAM_INT);
			
			try{
				
				$sth->execute();
				//fetch a single row
				$row = $sth->fetch(PDO::FETCH_OBJ);
				
				//use sprintf to insert activation code and user id
				$body = sprintf(str_replace('{{user_id}}','\'%1$s\'', $body), $user_id);
				$body = sprintf(str_replace('{{activation_code}}','\'%1$s\'', $body), $row->activationCode);
				
				//send email via PHPMailer
				if(!$this->send_mail($row->email, $subject, $body, $alt_body)){
					if($this->logger){
						$this->logger->error('Failed to send activation email.');
					}
					$this->errors[] = $this->lang['activation_email_unsuccessful'];
					return false;
				}
				
				return true;
				
			}catch(PDOException $db_err){
			
				if($this->logger){
					$this->logger->error('Failed to execute query to fetch email and activation code given a user id.', ['exception' => $db_err]);
				}
				$this->errors[] = $this->lang['activation_email_unsuccessful'];
				return false;
				
			}
		
		}else{
		
			return false;
		
		}
		
	}
	
	public function send_mail($email_to, $subject, $body, $alt_body = false){
	
		if($this->options['email_smtp']){
			$this->mailer->IsSMTP();
			$this->mailer->Host = $this->options['email_host'];
			if($this->options['email_auth']){
				$this->mailer->SMTPAuth = true;
				$this->mailer->Username = $this->options['email_username'];
				$this->mailer->Password = $this->options['email_password'];
			}
			if($this->options['email_smtp_secure']) $this->mailer->SMTPSecure = $this->options['email_smtp_secure'];
		}
		
		$this->mailer->From = $this->options['email_from'];
		$this->mailer->FromName = $this->options['email_from_name'];
		$this->mailer->AddAddress($email_to);
		if($this->options['email_replyto']) $this->mailer->AddReplyTo($this->options['email_replyto'], $this->options['email_replyto_name']);
		if($this->options['email_cc']) $this->mailer->AddCC($this->options['email_cc']);
		if($this->options['email_bcc']) $this->mailer->AddBCC($this->options['email_bcc']);
		if($this->options['email_html']) $this->mailer->IsHTML(true);
		
		$this->mailer->Subject = $subject;
		$this->mailer->Body = $body;
		if($alt_body) $this->mailer->AltBody = $alt_body;
		
		if(!$mail->Send()){
			return false;
		}
		
		return true;
	
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
		$sth = $this->db->bindParam(':identity', $identity, PDO::PARAM_STR);
		
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
	public function activate($user_id, $activation_code){
	
		//if the activation code matches with the user_id's activation code, then update the row to make it active!
		$query = "SELECT id from {$this->options['table_users']} WHERE id = :id AND activationCode = :activation_code";
		$sth = $this->db->prepare($query);
		$sth = $this->db->bindParam(':id', $user_id, PDO::PARAM_INT);
		$sth = $this->db->bindParam(':activation_code', $user_id, PDO::PARAM_STR);
		
		try{
		
			//test if there are any results
			$sth->execute();
			
			if($sth->fetch(PDO::FETCH_NUM) > 0){
				//we got a match, let's activate them!
				$query = "UPDATE {$this->options['table_users']} SET active = 1, activationCode = '' WHERE id = :id";
				$sth = $this->db->prepare($query);
				$sth = $this->db->bindParam(':id', $user_id, PDO::PARAM_INT);
				
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
	
	public function deactivate(){
	
	}
	
	public function login(){
	
	}
	
	public function login_remembered_user(){
	
	}
	
	public function is_max_login_attempts_exceeded(){
	
	}
	
	public function get_last_attempt_time(){
	
	}
	
	public function get_attempts_num(){
	
	}
	
	public function is_time_locked_out(){
	
	}
	
	public function clear_login_attempts(){
	
	}
	
	public function logged_in(){
	
	}
	
	public function logout(){
	
	}
	
	public function forgotten_password(){
	
	}
	
	public function forgotten_password_complete(){
	
	}
	
	public function forgotten_password_check(){
	
	}
	
	public function clear_forgotten_password_code(){
	
	}
	
	public function reset_password(){
	
	}
	
	public function change_password(){
	
	}
	
	//get the currently logged in user, if the user is not logged in, gets the current session
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
	
	
	//plans:
	//consistent interface to RBAC
	//registering users
		//sending emails
		//encrypting passwords (bcrypt encryption using php_hash or bcrypt library)
		//using the ircmaxell/password-compat library too
		//storing data
	//updating users
	//deleting users
	//activating users
	//logging in users
		//login attempts tracking
	//logging out users
	//getting users
	//managing the user session and cookies (shit, this may require Codeigniter's Session Library?)
	//will require email handler
	//later on include Oauth2 or HybridAuth
	
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
	
	public function encrypt_session($data, $key){

		return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $data, MCRYPT_MODE_CBC, md5(md5($key))));

	}
	
	public function decrypt_session($data, $key){
	
		return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($data), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
	
	}
	
	public function get_errors(){
		if(!empty($this->errors)){
			return $this->errors;
		}else{
			return false;
		}
	}

}