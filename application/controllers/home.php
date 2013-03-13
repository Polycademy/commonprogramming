<?php

use Guzzle\Url\Mapper;

class Home extends CI_Controller {

	public function index(){
	
		$some_dynamic_variable = array('fgdfg', 'fdgdfgf');
		FB::log($some_dynamic_variable);
		FB::warn('Hey this is a warning!', 'Hey this is warning watch out!');
		FB::info('This is an information', 'BLAHBLHABLAH');
        
        $view_data = array(
			'header' => array(
				'header_message' => 'THIS IS A HEADER MESSAGE',
			),
			'footer' => array(
				'footer_message' => 'THIS IS A FOOTER MESSAGE',
			),
			'message' => 'THIS IS A STANDARD MESSAGE for the INDEX VIEW',
		);
		
		Template::compose('index', $view_data);
        
    }
	
	public function json(){
	
		var_dump($this->input->server('HTTP_ACCEPT'));
	
		$view_data = array(
			0	=> array(
				'line'		=> false,
				'message'	=> 'No response was passed to the json view file',
			),
		);
		
		//Template::compose(false, $view_data, 'json');
	
	}
	
	public function table(){
	
		$view_data = array(
			'header' => array(
				'header_message' => 'THIS IS A HEADER MESSAGE',
			),
			'footer' => array(
				'footer_message' => 'THIS IS A FOOTER MESSAGE',
			),
			'row_data' => array(
				array(
					'name' => 'fgfdh',
					'id' => 'More rows to loop!'
				),
				array(
					'name' => 'fgfdh',
					'id' => 'Yay another loop!'
				),
			),
		);
		
		Template::compose('table', $view_data);
	
	}
	
	public function test_interface(){
	
		$armor = new Armor();
		$armor->weight();
	
	}
	
	public function test_namespace(){
	
		$mapper = new Mapper();
		
	}
	
	public function test_ioc(){
	
		$ioc = $this->config->item('ioc');
		
		$masterlibrary = $ioc['MasterLibrary'];
		
		$masterlibrary->do_something();
		
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

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */