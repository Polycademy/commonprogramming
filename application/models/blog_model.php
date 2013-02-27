<?php

class Blog_model extends CI_Model{

	public function __construct(){
		parent::__construct();
		//$this->load->database(); //incase you didnt autoload it
	}
	
	public function create($title){
	
		$data = array(
			'title' => $title,
		);
		
		//insert into the blog table, using the $data array
		$query = $this->db->insert('blog', $data);
		
		if(!$query){

			$msg = $this->db->_error_message();
			$num = $this->db->_error_number();
			$last_query = $this->db->last_query();
			
			log_message('error', 'Problem inserting data into the blog table ' . $msg . '(' . $num . '), using this query: "' . $last_query . '"');
			
			return false;
		
		}
		
		return $this->db->insert_id();
	
	}
	
	//gets a data field based on the $id...
	public function read($id){
		
		if(!is_numeric($id)){
			return false;
		}
		
		$this->db->select('blog.*');
		$this->db->where('blog.id', $id);
		$this->db->from('blog');
		
		$query = $this->db->get();
		
		if($query->num_rows() > 0){
		
			$result = $query->row_array();
			
			return $result;
		
		}
		
		return false;
	
	}
	
	public function read_all(){
		
		//CONSTRUCTS THE QUERY
		$this->db->select('*')->from('blog');
		
		//MODEL STANDARD: CRUD
		//SQL STANDARD: INSERT SELECT UPDATE DELETE (ISUD)
		//CRUD ==> ISUD
		
		//EXECUTES THE QUERY
		$blog_result = $this->db->get();
		
		//BOOLEANS -> true/false
		//STRINGS -> 'fdgdfg'
		//INTEGERS -> 34873298504 0
		//FLOATS - > 0.45453
		
		if($blog_result->num_rows() > 0){
		
			$blog_result = $blog_result->result_array();
			
			return $blog_result;
		
		}else{
			return false;
		}
	
	}
	
	public function update($id, $title){
	
		$data = array(
			'title'	=> $title,
		);
	
		$this->db->where('id', $id);
		$this->db->update('blog', $data);
		
		if($this->db->affected_rows() > 0){
		
			return $this->db->affected_rows();
		
		}
		
		//this does not mean that the query failed
		//this could simply mean that your update was redundant.
		return false;
	
	}
	
	

}