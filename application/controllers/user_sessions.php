<?php

class User_sessions extends CI_Controller{

	private $view_data = array();

	public function __construct(){
	
		parent::__construct();
		$this->load->library('session');
		$this->load->library('form_validation');
		
		//abstracted the commonly shared view data to the site_config.php file that is being autoloaded
		$this->view_data += $this->config->item('view_data');
	
	}
	
	//give the person a login form
	public function login_form(){
	
		if($this->ion_auth->logged_in()){
			redirect('home');
		}
		
		$login_messages = ($this->session->flashdata('message')) ? $this->session->flashdata('message') : false;
		//THE ABOVE CODE IS EQUIVALENT TO THIS
		// if($this->session->flashdata('message')){
			// $login_messages = $this->session->flashdata('message');
		// }else{
			// $login_messages = false;
		// }
		
		$this->view_data += array(
			'form_destination'	=> 'sessions',
			'login_messages'	=> $login_messages,
		);
		
		Template::compose('login_form', $this->view_data);
	
	}
	
	//really login
	public function login(){
	
		$username = $this->input->post('username');
		$password = $this->input->post('password');
	
		$this->form_validation->set_rules('username', 'Username', 'required');
		$this->form_validation->set_rules('password', 'Password', 'required');
		
		if($this->form_validation->run() == true){
			
			if($this->ion_auth->login($username, $password)){
			
				//login successful
				redirect('/');
			
			}else{
			
				//login not successful
				$this->session->set_flashdata('message', $this->ion_auth->errors());
				redirect($this->input->server('HTTP_REFERER'));
			
			}
		
		}else{
		
			//validation not successful
			$this->session->set_flashdata('message', validation_errors());
			redirect($this->input->server('HTTP_REFERER'));
			
		}
	
	}
	
	//log a user with the id of $id
	//make sure to authenticate the request that the person actually owns the $id
	public function logout(){
	
		$this->ion_auth->logout();
		redirect($this->input->server['HTTP_REFERER']);
	
	}
	
}