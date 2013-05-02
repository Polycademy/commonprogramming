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
use RBAC\Role\Permission;
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
	protected $user;
	protected $mailer;
	protected $bcrypt_fallback = false;
	
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
		$this->user = new UserAccount;
		$this->mailer = new PHPMailer;
		
		//if you use bcrypt fallback, you must always use bcrypt fallback, you cannot switch servers!
		if($this->options['hash_fallback']){
			$this->bcrypt_fallback = new BcryptFallback($this->options['hash_rounds']);
		}
		
		$this->begin();
		
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
			'email_from'						=> 'enquiry@polycademy.com',
			'email_type'						=> 'html', //can be text or html
			//rbac options (initial roles from the migration, also who's the default role, and root access role?)
			'role_default'						=> 'members',
			'role_admin'						=> 'admin',
			//login options (this is the field used to login with, plus login attempts)
			'login_identity'					=> 'username', //can be email, username or any database field, all fields are optional when registering, you need to force constraints
			'login_password_minlength'			=> 8,
			'login_password_maxlength'			=> 20,
			'login_persistent'					=> true, //allowing remember me or not
			'login_expiration'					=> 86500, // How long to remember the user (seconds). Set to zero for no expiration
			'login_expiration_extend'			=> true, //allowing whether autologin extends the login_expiration
			'login_attempts'					=> 0, //if 0, then it is disabled
			'login_lockout'						=> 0, //lockout time in seconds
			'login_forgot_password_expiration'	=> 0, //how long before the temporary password expires
			//registration options
			'reg_activation'					=> 'none', //can be email, manual, or none
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
	
	protected function begin(){
	
		if(!$this->logged_in() && $this->cookie_manager->get_cookie('identity') && $this->cookie_manager->get_cookie('rememberCode')){
			$this->login_remembered_user();
		}
	
	}
	
	public function logged_in(){
	
	}
	
	public function login_remembered_user(){
	
	}
	
	public function forgotten_password(){
	
	}
	
	public function forgotten_password_complete(){
	
	}
	
	public function forgotten_password_check(){
	
	}
	
	public function register(){
	
	}
	
	public function activate(){
	
	}
	
	public function logout(){
	
	}
	
	//helper function to determine if the current logged in user has admin level access
	public function is_admin(){
	
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

}