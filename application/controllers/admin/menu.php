<?php

class Menu extends CI_Controller {

	public function index(){
	
        $view_data = array(
			'header' => array(
				'header_message' => 'THIS IS A HEADER MESSAGE',
			),
			'footer' => array(
				'footer_message' => 'THIS IS A FOOTER MESSAGE',
			),
			'message' => 'WE ARE IN ADMIN/MENU',
		);
		
		Template::compose('index', $view_data);
	
	}

}