<?php

class Courses extends CI_Controller{

	public function __construct(){
		parent::__construct();
		$this->load->model('Courses_model');
	}
	
	/**
	 * Gets all Courses
	 *
	 * @queryparam int Limit the number of courses
	 * @queryparam int Offset the number of courses for pagination
	 * @return JSON
	 **/
	public function index(){
		
		$limit = $this->input->get('limit', true);
		$offset = $this->input->get('offset', true);
		
		$query = $this->Courses_model->read_all($limit, $offset);
		
		if($query){
			
			$content = $query; //assign query
			$code = 'success'; //assign code
			
		}else{
		
			$this->output->set_status_header('404');
			$content = current($this->Courses_model->get_errors());
			$code = key($this->Courses_model->get_errors());
			
		}
		
		$output = array(
			'content'	=> $content,
			'code'		=>$code,
		);
		
		Template::compose(false, $output, 'json');

	}
	
	/**
	 * Gets one course
	 *
	 * @param int Course ID
	 * @return JSON
	 **/
	public function show($id){
		
		$query = $this->Courses_model->read($id);		
		
		if($query){
			
			$content = $query;
			$code = 'success';
		
		}else{
		
			$this->output->set_status_header('404');
			$content = current($this->Courses_model->get_errors());
			$code = key($this->Courses_model->get_errors());
		
		}
		
		$output = array(
			'content'	=> $content,
			'code'		=> $code,
		);
		
		Template::compose(false, $output, 'json');
		
	}
	
	/**
	 * Posts a new course
	 *
	 * @postparam json Input data of the course
	 * @return JSON
	 **/
	public function create(){
		//post a new course
		
		$this->authenticated();
		
		$data = $this->input->json(false, true);
		
		$data['numberOfApplications'] = (!empty($data['numberOfApplications']) ? $data['numberOfApplications'] : 0);
		$data['numberOfStudents'] = (!empty($data['numberOfStudents']) ? $data['numberOfStudents'] : 0);
		
		$query = $this->Courses_model->create($data);
		
		if($query){
		
			$this->output->set_status_header('201');
			$content = $query; //resource id
			$code = 'success';
		
		}else{
		
			
			$content = current($this->Courses_model->get_errors());
			$code = key($this->Courses_model->get_errors());
			
			if($code == 'validation_error'){
				$this->output->set_status_header(400);
			}elseif($code == 'system_error'){
				$this->output->set_status_header(500);
			}
			
		}
		
		$output = array(
			'content'	=> $content,
			'code'		=> $code,
		);
		
		Template::compose(false, $output, 'json');
		
	}
	
	/**
	 * Updates a particular course
	 *
	 * @param int Course ID
	 * @putparam json Updated input data for the course
	 * @return JSON
	 **/
	public function update($id){
		//update a course
		
		$this->authenticated();
		
		$data = $this->input->json(false, true);
		
		$query = $this->Courses_model->update($data, $id);
		
		if($query){
		
			$content = $id;
			$code = 'success';
			
		}else{
		
			$this->output->set_status_header('200');
			$content = current($this->Courses_model->get_errors());
			$code = key($this->Courses_model->get_errors());
			
		}
		
		$output = array(
			'content'	=> $content,
			'code'		=> $code,
		);
		
		Template::compose(false, $output, 'json');
		
	}
	
	/**
	 * Deletes a particular course
	 *
	 * @param int Course ID
	 * @return JSON
	 **/
	public function delete($id){
		//delete a course
		
		$this->authenticated();
		
		$query = $this->Courses_model->delete($id);
		
		if($query){
		
			$content = $id;
			$code = 'success';
			
		}else{
		
			$this->output->set_status_header('200');
			$content = current($this->Courses_model->get_errors());
			$code = key($this->Courses_model->get_errors());
		
		}
		
		$output = array(
			'content'	=> $content,
			'code'		=> $code,
		);
		
		Template::compose(false, $output, 'json');
		
	}
	
	private function authenticated(){
		//check if person was authenticated
		/*
			$output = array(
				'content'	=> 'You need to login to do this action.',
				'code'	=> 'error',
			);
		*/
	}

}