<?php 

namespace PolyAuth\Sessions;

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

//for sessions
use PolyAuth\Sessions\CookieManager;
use Aura\Session\Manager as SessionManager;
use Aura\Session\SegmentFactory;
use Aura\Session\CsrfTokenFactory;

//for RBAC (to authenticate against access)
use PolyAuth\UserAccount;
use RBAC\Permission;
use RBAC\Role\Role;
use RBAC\Manager\RoleManager;

//this class handles all the login and logout functionality
class LoginLogout{

	protected $options;
	protected $db;
	protected $logger;

	public function __construct(PDO $db, Options $options, SessionInterface $session_handler = null, LoggerInterface $logger = null){
	
		$this->options = $options;
		$this->db = $db;
		$this->logger = $logger;
	
	}

}