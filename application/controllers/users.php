<?php

class Users extends CI_Controller{

	private $view_data = array();

	public function __construct(){
	
		parent::__construct();
		$this->load->library('session');
		$this->load->library('form_validation');
		
		$this->form_validation->set_error_delimiters(
			'<p>',
			'</p>'
		);
		
		//configure default view data
		
		$this->view_data += array(
			'header' => array(
				'header_message' => 'THIS IS A HEADER MESSAGE',
			),
			'footer' => array(
				'footer_message' => 'THIS IS A FOOTER MESSAGE',
			),
		);
	
	}

	//main method that gets loaded by default
	public function index(){
	
		if($this->ion_auth->logged_in()){
			redirect('home');
		}
		
		$this->view_data += array(
			'form_destination' => $this->router->fetch_class() . '/create_new',
		);
	
		Template::compose('index', $this->view_data);	
		
	}
	
	public function create_new(){
	
		$this->form_validation->set_rules('username', 'Username', 'required');
		$this->form_validation->set_rules('password', 'Password', 'required');
		
		if($this->form_validation->run() == true){
		
			$post_data = $this->input->post();
			
			var_dump($post_data);
		
		}else{

			$this->view_data += array(
			
			);
		
		}
		
	}

}