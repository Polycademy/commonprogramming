<?php

/**
 * Pimple uses anonymous functions (lambdas) so it can "lazy load" the classes.
 * The functions will not be processed when the PHP interpreter goes through this file.
 * They will be kept inside the function waiting to be called as part of the container array.
 * Once you call the functions, then the objects will be created! Thus "lazy loading", not "eager loading". Saves memory too!
 * Note Pimple is an object that acts like an array, see the actual Pimple code to see how this works.
 * This usage assumes that you have autoloading working, so that the references to the classes will be autoloaded!
 */

$ioc = new Pimple;

//Setup a database connection here, this is for libraries that will require a database connection
$ioc['Database'] = function($c){
	$CI = get_instance();
	$CI->load->database();
	$dbh = $CI->db->conn_id;
	return $dbh;
};

//HERE IS JUST SOME RANDOM EXAMPLES!
$ioc['WorkerLibrary'] = function($c){
	return new WorkerLibrary;
};

//Demonstration of the self-referential $c to use the WorkerLibrary and to pass it in as a dependency to the MasterLibrary
$ioc['MasterLibrary'] = function($c){
	return new MasterLibrary($c['WorkerLibrary']);
};

//MONOLOG BASED LOGGER, use this for libraries that need to log things, in fact this can replace the standard Codeigniter Logger
$ioc['Logger'] = function($c){

	if($this->config['log_threshold'] !== 0){
	
		$log_path = ($this->config['log_path'] !== '') ? $this->config['log_path'] : APPPATH.'logs/';
		
		//codeigniter's options is a maximum threshold, while monolog is a minimum threshold, we'll need to switch them around
		switch($this->config['log_threshold']){
			case 1:
				$log_threshold = Monolog\Logger::ERROR;
				break;
			case 2:
				$log_threshold = Monolog\Logger::NOTICE;
				break;
			case 3:
				$log_threshold = Monolog\Logger::INFO;
				break;
			case 4:
				$log_threshold = Monolog\Logger::DEBUG;
				break;
			default:
				$log_threshold = Monolog\Logger::DEBUG;
		}
		
		$logger = new Monolog\Logger('Monolog');
		$logger->pushHandler(new Monolog\Handler\StreamHandler($log_path, $log_threshold));
	
	}else{
	
		//if the log_threshold was 0, then we should simply disable it, this is fine for libraries that accept a null parameter for optional classes
		$logger = null;
		
	}
	
	return $logger;
	
};

// $ioc['Options'] = function($c){
// 	return new PolyAuth\Options
// }

// $ioc['AccountsManager'] = function($c){

// 	$accounts_manager = new PolyAuth\AccountsManager($c['Database']);

// 	return $accounts_manager;

// }

//we need to pass the $ioc into the global $config variable, so now it can be accessed by Codeigniter
$config['ioc'] = $ioc;