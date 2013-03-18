<?php

class Rest extends CI_Controller{

	public function __construct(){
		parent::__construct();
		$this->load->model('Rest_model');
	}
	
	/**
	 * Gets all Items
	 *
	 * @queryparam int Limit - limit the number of items
	 * @queryparam int Offset - offset the number of items
	 * @return JSON
	 **/
	public function index(){
	
		//gets me the limit parameter
		$limit = $this->input->get('limit', true);
		//gets me the offset parameter
		$offset = $this->input->get('offset', true);
		
		$query = $this->Rest_model->read_all($limit, $offset);
		
		Template::compose(false, $query, 'json');
		
		/*
		if($query){
			foreach($query as &$course){
				$course = output_message_mapper($course);
			}
			$output = $query;
		}else{
			$this->output->set_status_header('404');
			$output = array(
				'error'			=> output_message_mapper($this->Courses_model->get_errors()),
			);
		}
		*/
		
		
	
	}
	
	public function show($id){}
	public function create(){}
	public function update($id){}
	public function delete($id){}

}