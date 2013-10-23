<?php

//setting up the service
use OAuth\ServiceFactory;
use OAuth\OAuth2\Service\GitHub;

//dealing with the uris
use OAuth\Common\Http\Uri\Uri; //seems useless
use OAuth\Common\Http\Uri\UriFactory;

//storage
use OAuth\Common\Storage\Session;

//analysing credentials
use OAuth\Common\Consumer\Credentials;

//common exceptions
use OAuth\Common\Http\Exception\TokenResponseException;

class Oauthtest extends CI_Controller{

	public $current_uri;
	public $github;
	
	public function __construct(){

		$credentials = [
		    'github' => [
		        'key'       => 'dcefd5ecfa6d4e663471',
		        'secret'    => 'c40b10a06d66bb96766baef99c959f0b2964cadb',
		    ],
		];

		//setup uri
		$uri_factory = new UriFactory;
		$this->current_uri = $uri_factory->createFromSuperGlobalArray($_SERVER);
		//this is for query parameters (might be useful if you need carry over query parameters...)
		$this->current_uri->addToQuery('lol', 'blah');

		//storage
		$storage = new Session(true, 'Some Oauth Cookie');

		//credentials KEY, SECRET, REDIRECT URI! (HERE WE CAN SET A CUSTOM REDIRECT URI)
		$credentials = new Credentials(
			$credentials['github']['key'],
			$credentials['github']['secret'],
			$this->current_uri->getAbsoluteUri()
		);

		//here's an example of using the uris manually, so no need to use the UriFactory!
		// $credentials = new Credentials(
		// 	$credentials['github']['key'],
		// 	$credentials['github']['secret'],
		// 	'http://lol.com?q=fdgfdg'
		// );

		$service_factory = new ServiceFactory;
		//name, credentials, storage, scope
		$this->github = $service_factory->createService(
			'github', //does this require capitalisation?
			$credentials,
			$storage,
			['user']
		);

		//setup a session cookie
		setcookie('state', '15fdg48h', 0, '/', '', false, true);

	}

	public function index(){

		//relative uri of the current page
		//var_dump($this->current_uri->getRelativeUri());
		//absolute uri of the current page
		//var_dump($this->current_uri->getAbsoluteUri());

		//this is the authorizationuri, it's an object that can echoed out or used as a string
		//you can either A: SERVER REDIRECT to this location B: Give it out as a link C: CLIENT REDIRECT to this location
		//it will depend on the application, like for example SPAs will require client redirect, or perhaps a popup like cloud9...
		////popups will simply require a separate method that the popup gets redirected to, then call a javascript function that calls the parent window's function
		//that could be done on the client side, using angularjs as a routing mechanism
		//However that would require us to manually set the redirect uri, not based on the current page which it is automatically doing currently
		echo $this->github->getAuthorizationUri(array('state' => '15fdg48h'));

		var_dump($this->github->getStorage());

		var_dump($_SESSION);

		//CHECK IF AUTHORIZATION CODE IS PASSED IN
		if(!empty($_GET['code'])){

			$token = $this->github->requestAccessToken($_GET['code']);

			if($_COOKIE['state'] == $_GET['state']){
				echo 'state matches';
			}else{
				echo 'state doesn\'t match';
			}

			//delete state cookie!

			//ok this means we have code being sent back from the service (this could be faked, and we would need a failsafe)
			//exchange for access token!
			// try {
				
			// 	// var_dump($this->github->getStorage()->retrieveAccessToken($this->github));
			// 	var_dump($token);
			// }catch(TokenResponseException $e){
			// 	//at this point we should check if that worked, it would throw an exception if it didn't, in which case we'll simply disregard the login attempt
			// 	var_dump('Code is incorrect, or has already been used. Try again. ERROR: ' . $e->getMessage());
			// }
			
			//let's do some requests
			var_dump(json_decode($this->github->request('user/emails'), true));

		}



	}

}