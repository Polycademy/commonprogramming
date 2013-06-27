<?php

namespace Realtime;

use Ratchet\ConnectionInterface;
use Realtime\FindBinary;

//this class will send a request to authenticate on the first web socket connection open
class Auth{

	protected $binary;

	public function __construct(){
		$find_binary = new FindBinary;
		$this->binary = $find_binary->init_binary('php');
	}
	
	//It behaves exactly like PHP, but will also evaluates the string "false" as false:
	public function is_boolean($value) {
		if ($value && strtolower($value) !== "false") {
			return true;
		} else {
			return false;
		}
	}

	//authentication depends on where the person is going
	public function authenticate(ConnectionInterface $conn, $group){
		
		//this will be blocking
		//array of cookies will be passed in, Ratchet won't know the name of the cookie, but Codeigniter will!
		$cookies = json_encode($conn->WebSocket->request->getCookies());
		
		$descriptorspec = array(
			0 => array("pipe", "r"),
			1 => array("pipe", "w"),
			2 => array("pipe", "w")
		);
		
		$cmd = $this->binary . ' ' . FCPATH . ' cli sessions cli_check_ws_session ' . $group;
		
		$process = proc_open($cmd, $descriptorspec, $pipes);
		
		if(!is_resource($process)){
			echo 'Could not establish process at cli_check_ws_session!';
			return false;
		}
		
		//var_dump($process);
		
		//pump in the cookie
		fwrite($pipes[0], $cookies);
		fclose($pipes[0]);
		
		//scoop out the output
		$stdout = stream_get_contents($pipes[1]);
		fclose($pipes[1]);
		
		//oh no errors?
		$stderr = stream_get_contents($pipes[2]);
		fclose($pipes[2]);
	
		$return_value = proc_close($process);
		
		if(!empty($stderr)){
			//there were some errors coming from the PHP file!
			echo $stderr . "\n";
			return false;
		}
		
		//var_dump($stderr);
		//echo $stdout . "\n";
		
		if($this->is_boolean($stdout)){
			//true means the user is part of the specified group
			//echo 'WHAT THIE';
			return json_decode($stdout); //json serialised user data
		}else{
			//false means the user is not part of the specified group
			//echo 'HELL';
			return false;
		}
		
	}

}