<?php

/**
 * Pimple uses anonymous functions (lambdas) so it can "lazy load" the classes.
 * The functions will not be processed when the PHP interpreter goes through this file.
 * They will be kept inside the function waiting to be called as part of the container array.
 * Once you call the functions, then the objects will be created! Thus "lazy loading", not "eager loading". Saves memory too!
 * The functions can also be "shared", so they are not executed everytime it is called, even more lazier loading!
 * Note Pimple is an object that acts like an array, see the actual Pimple code to see how this works.
 * This usage assumes that you have autoloading working, so that the references to the classes will be autoloaded!
 * "$this->config" corresponds to the config files. It can be accessed inside the closures in 5.4.
 */

$ioc = new Pimple;

//Setup a database connection here, this is for libraries that will require a database connection
//this only works for PDO based connections (which you should be using!)
$ioc['Database'] = $ioc->share(function($c){
	$CI = get_instance();
	$CI->load->database();
	$dbh = $CI->db->conn_id;
	return $dbh;
});

//MONOLOG BASED LOGGER, use this for libraries that need to log things, in fact this can replace the standard Codeigniter Logger
$ioc['Logger'] = $ioc->share(function($c){

	//$this is available inside the anonymous function in 5.4
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
	
});

//Using PolyAuth!

$ioc['PolyAuth\Options'] = $ioc->share(function($c){
	return new PolyAuth\Options($this->config['polyauth']);
});

$ioc['PolyAuth\Language'] = $ioc->share(function($c){
	//it's possible to get a custom language file, you would need to load it from the get_instance just like how the $dbh was handled
	return new PolyAuth\Language;
});

$ioc['PolyAuth\AuthStrategies\HTTPStrategy'] = $ioc->share(function($c){

	$http_strategy = new PolyAuth\AuthStrategies\HTTPStrategy(
		$c['Database'],
		$c['PolyAuth\Options'],
		$c['Logger']
	);

	return $http_strategy;

});

$ioc['PolyAuth\Accounts\AccountsManager'] = $ioc->share(function($c){

	$accounts_manager = new PolyAuth\Accounts\AccountsManager(
		$c['Database'], 
		$c['PolyAuth\Options'], 
		$c['PolyAuth\Language'], 
		$c['Logger']
	);

	return $accounts_manager;

});

$ioc['PolyAuth\Sessions\UserSessions'] = $ioc->share(function($c){

	$user_sessions = new PolyAuth\Sessions\UserSessions(
		$c['PolyAuth\AuthStrategies\HTTPStrategy'],
		$c['Database'],
		$c['PolyAuth\Options'],
		$c['PolyAuth\Language'],
		$c['Logger']
	);

	return $user_sessions;

});

//we need to pass the $ioc into the global $config variable, so now it can be accessed by Codeigniter
$config['ioc'] = $ioc;