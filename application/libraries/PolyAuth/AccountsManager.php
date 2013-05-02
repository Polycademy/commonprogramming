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

class AccountsManager{

	protected $db;
	protected $cookie_manager;
	protected $session_manager;
	protected $role_manager;
	protected $logger;
	protected $mailer;
	protected $options;
	protected $bcrypt_fallback = false;
	
	//expects PDO connection (potentially using $this->db->conn_id)
	//SessionInterface is a copy of the PHP5.4.0 SessionHandlerInterface, this allows backwards compatibility
	public function __construct($options = false, PDO $db, SessionInterface $session_handler = null, LoggerInterface $logger = null){
	
		$this->configure($options);
	
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
		
		if(version_compare(phpversion(), '5.3.7') < 0){
			$this->bcrypt_fallback = new BcryptFallback($this->options['hash_rounds']);
		}
		
	}
	
	public function configure($options){
		
		$this->options = array(
			//table options, see that the migration to be reflected. (RBAC options are not negotiable)
			'table_users'			=> 'user_accounts',
			'table_login_attempts'	=> 'login_attempts',
			//security options
			'hash_method'			=> PASSWORD_DEFAULT,
			'hash_rounds'			=> 8,
			//cookie options
			'cookie_domain'			=> '',
			'cookie_path'			=> '/',
			'cookie_prefix'			=> '',
			'cookie_secure'			=> false,
			'cookie_httponly'		=> false,
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
	
	
	//it will be possible to encrypt sessions and decrypt sessions on the fly between the data transportation!
	public function encrypt_session(){
	
		/*
		$key = 'password to (en/de)crypt';
		$string = 'string to be encrypted';

		$encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, md5(md5($key))));
		$decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($encrypted), MCRYPT_MODE_CBC, md5(md5($key))), "\0");

		var_dump($encrypted);
		var_dump($decrypted);
		
		*/
	
	}
	
	public function decrypt_session(){
	
	}

}