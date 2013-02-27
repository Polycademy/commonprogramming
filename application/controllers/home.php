<?php

use Guzzle\Url\Mapper;

class Home extends CI_Controller {

	public function index(){
        
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
	
		$view_data = array(
			0	=> array(
				'line'		=> false,
				'message'	=> 'No response was passed to the json view file',
			),
		);
		
		Template::compose(false, $view_data, 'json');
	
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
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */