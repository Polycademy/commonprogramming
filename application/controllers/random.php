<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Guzzle\Url\Mapper;

//this controller demonstrates some random functions that be of interest
class Random extends CI_Controller{

	public function __construct(){
		parent::__construct();
		FB::log('I am logging from firephp!');
	}

	public function test_decode2(){

		$result = file_get_contents('D:/youtube.txt');

		$json = json_decode($result, true);

		$ifp = fopen('D:/random.png', "wb"); 
		fwrite($ifp, base64_decode($json['screenshot'])); 
		fclose($ifp);

	}

	public function test_decode(){

		$postdata = json_encode(
		    array(
		        'url' => 'http://polycademy.com',
		        'callback' => "var box = document.getElementsByClassName('grid_box')[0]; box.style.height = '400px';"
		    )
		);

		$opts = array('http' =>
		    array(
		        'method'  => 'POST',
		        'header'  => 'Content-type: application/json',
		        'content' => $postdata
		    )
		);

		$context  = stream_context_create($opts);

		$result = file_get_contents('http://localhost:8989', false, $context);

		$json = json_decode($result, true);

		$ifp = fopen('D:/random.png', "wb"); 
		fwrite($ifp, base64_decode($json['screenshot'])); 
		fclose($ifp);

	}

	public function test_getting(){

		// $uri = 'https://google.com';

		$ctx = stream_context_create(array(
			'http' => array(
				'header' => "Accept: application/xrds+xml\r\n",
			)
		));

		// $ctx = null;

		// $fh = @fopen($uri, 'r', false, $ctx);

		// $details = stream_get_meta_data($fh);

		// var_dump($details);

		//$level = error_reporting(0);
		fopen('http://google.com', 'r', false, $ctx);
		//error_reporting($level);

		var_dump(error_get_last());



	}

	public function test_urls(){

		//xri://@free*cmcdragonkai/blah/blah?lol=fgfdg
		$uri = 'blahblah.com';

		//first remove the xri:// scheme if it exists
		if(substr($uri, 0, 6) == 'xri://'){
			$uri = substr($uri, 6);
		}

		//xri parsing, xris could also be passed without the xri:// scheme
		if(in_array($uri[0], array('=', '@', '+', '$', '!'))){
			
			//add the xri proxy resolver
			$uri = $this->xri_resolver . $uri;

			$uri = Purl\Url::parse($uri);
			if(!$uri->getData()){
				return false;
			}

			//add the xrds query parameter to get the xrds document for discovery
			$uri->query->set('_xrd_r', 'application/xrds+xml');

			var_dump($uri->getUrl());

		}

		//parse normal http urls
		$uri = Purl\Url::parse($uri);
		if(!$uri->getData()){
			return false;
		}

		//add http:// if needed
		if(!$uri->scheme){
			$uri->scheme = 'http';
		}

		var_dump($uri);

	}

	public function test_session(){
		echo session_save_path();
	}

	public function test_another_http(){


		$headers = getallheaders();
		// $attributes = array();
		// if(isset($headers['Authorization'])){
		// 	$attributes['authorization'] = $headers['Authorization'];
		// }

		$request = Request::createFromGlobals();
		$request->headers->set('Authorization', $headers['Authorization']);

		// var_dump($request->attributes->all());
		var_dump($request->headers->all());

		var_dump($request->headers->get('authorization'));

	}

	public function test_http(){

		var_dump($_SERVER);

		var_dump(getallheaders());

		var_dump(file_get_contents('php://input'));

	}

	public function test_accepts(){

		$request = Request::createFromGlobals();

		var_dump($request->getAcceptableContentTypes());

	}

	public function test_response(){

		$request = Request::createFromGlobals();

		$response = new Response;

		$response->headers->setCookie(new Cookie(
			'session',
			'ghgfhgfh'
		));

		$response->prepare($request);

		$response->sendHeaders();
		
		header('HTTP/1.1 401 Unauthorized');

		//var_dump(headers_list());

	}
	
	public function test_interface(){
	
		$armor = new Armor();
		$armor->weight();
	
	}
	
	public function test_namespace(){
	
		$mapper = new Mapper();
		
	}
	
	public function test_shell(){
		
		$descriptor_spec = array(
		1 => array('pipe', 'w'), //STDOUT write mode
		2 => array('pipe', 'w'), //STDERR write mode
		);

		$cmd = 'tracert -w 10 accettura.com';

		$process = proc_open($cmd, $descriptor_spec, $pipes);

		if(is_resource($process)){

			while(!feof($pipes[1])){
				$output = fgets($pipes[1]);
				echo $output;
				ob_flush();
				flush();
			}
			
			// while (($output = fgets($pipes[1], 4096)) !== false) {
				// echo $output;
				// ob_flush();
				// flush();
			// }

			fclose($pipes[1]);

			$errors = stream_get_contents($pipes[2]);
			fclose($pipes[2]);

			$exit_code = proc_close($process);

		}
	
	}
	
	public function test_async_shell(){
	
		// function execInBackground($cmd) {
			// if (substr(php_uname(), 0, 7) == "Windows"){
				// pclose(popen("start /B ". $cmd . ' > C:/wamp/www/dirlist.txt', "r")); 
				// echo 'lol';
			// }
			// else {
				// exec($cmd . " > /dev/null &");
				
			// }
		// } 
		
		// execInBackground('tracert -w 10 accettura.com');
	
	}
	
	public function test_spark(){
	
		$this->load->spark('restclient/2.1.0');
		$this->load->library('rest');
		$this->rest->initialize(array('server' => 'http://pipes.yahoo.com/'));
		$tweets = $this->rest->get('pipes/pipe.run?_id=24a7ee6208f281f8dff1162dbac57584&_render=rss');
		
		var_dump($tweets);
	
	}

}