<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	PIGEON ROUTING
 */
Pigeon::map(function($r){
	
	//RESOURCES ROUTING
	$r->route('api', false, function($r){
	
		//for migrations, these should be commented out when you've done your migration!
		$r->get('migrate', 'migrate/index');
		$r->get('migrate/latest', 'migrate/latest');
		$r->get('migrate/current', 'migrate/current');
		$r->get('migrate/version/(:num)', 'migrate/version/$1');
		$r->get('migrate/restart',  'migrate/restart');
		$r->get('migrate/restart/(:num)',  'migrate/restart/$1');

		//various demonstrations of code
		$r->get('random/(:any)', 'random/$1');
		$r->post('random/(:any)', 'random/$1');
		
		//for random code to test
		$r->get('test/(:any)', 'test/$1');

		//oauth test
		$r->get('oauthtest', 'oauthtest/index');

		//for authentication and authorisation tests
		$r->resources('authtests');
		
		//generic CRUD demonstration
		$r->resources('courses');
		
		//Websockets based Chat demo
		$r->resources('chat');
		
		//for user accounts
		$r->resources('accounts');
		
		//for logging in and out
		$r->resources('sessions');
		
		//let's try subresource routing
		//(http://e.com/posts/2/comments/3) - get the third comment of the 2nd post
		//(http://e.com/posts/2/comments) - get all the comments of the 2nd post
		//(http://e.com/posts/2) - get the 2nd post
		//(http://e.com/comments/2) - get the 2nd comment OR (http://e.com/comments) - get all the comments
		$r->resources('super');
		$r->resources('sub');
		$r->route('super/(:num)', false, function($r){ //it can also be (:any)
			$r->resources('sub');
			$r->resources('othersub'); //this can equate to multiple sub resources
		});
		
	});
	
	//CLI ROUTING
	$r->route('cli', false , function($r){
	
		//if you needed to access your app through the CLI, you should add separate routes using $r->route, this is because CLI requests don't go through GET POST PUT DELETE
		
	});
	
	//CLIENT SIDE ROUTING
	$r->route('(.*)', 'home#index');
	
});

$route = Pigeon::draw();
$route['default_controller'] = 'home';
$route['404_override'] = '';

/*
	CSRF EXCLUSION, use api paths
 */
$config =& load_class('Config', 'core');
$config->set_item('csrf_exclude_uris', array());