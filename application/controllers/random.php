<?php

use Guzzle\Url\Mapper;

//this controller demonstrates some random functions that be of interest
class Random extends CI_Controller{

	public function __construct(){
		parent::__construct();
		FB::log('I am logging from firephp!');
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