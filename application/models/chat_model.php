<?php

use Polycademy\Validation\Validator;

class Chat_model extends CI_Model{

	protected $validator;
	protected $errors;

	public function __construct(){
	
		parent::__construct();
		$this->validator = new Validator;
		
	}
	
	public function create($data){
	
		$this->validator->setup_rules(array(
			'userId'	=> array(
				'set_label:User ID',
				'NotEmpty',
				'Number',
			),
			'roomId'	=> array(
				'set_label:Room ID',
				'NotEmpty',
				'Number',
			),
			'message'	=> array(
				'set_label:Message',
				'NotEmpty',
				'MaxLength:1000',
			),
		));
		
		if(!$this->validator->is_valid($data)){
		
			//returns array of key for data and value			
			$this->errors = array(
				'validation_error'	=> $this->validator->get_errors(),
			);
			
			return false;
			
		}
		
		$query = $this->db->insert('chat', $data); 
 
        if(!$query){
			
			$this->errors = array(
				'system_error'	=> 'Problem inserting chat message to chat table.',
			);
			
            return false;
			
        }
		
        return $this->db->insert_id();
		
	}
	
	//gets all the chat messages from a room
	public function read_room($id, $limit = false, $offset = false){
	
		//if limit is false, pass in a default 100
		$limit = ($limit) ? $limit : 100;
	
		$this->db->select('*');
		$this->db->limit($limit, $offset);
		$query = $this->db->get_where('chat', array('roomId' => $id));	
		
		if($query->num_rows() > 0){
		
			foreach($query->result() as $row){
			
				//inside each row now!
				$data[] = array(
					'id'			=> $row->id,
					'timestamp'		=> $row->timestamp,
					'userId'		=> $row->userId,
					'roomId'		=> $row->roomId,
					'message'		=> $row->message,
				);
			
			}
			
			return $data;
			
		}else{
		
			$this->errors = array(
				'error' => 'There are no chat messages at room ' . $id,
			);
			return false;
		
		}
	
	}
	
	//gets one chat message
	public function read($id){
	
		$query = $this->db->get_where('chat', array('id' => $id));
		
		if($query->num_rows() > 0){
			
			$row = $query->row();
			$data = array(
				'id'			=> $id,
				'timestamp'		=> $row->timestamp,
				'userId'		=> $row->userId,
				'roomId'		=> $row->roomId,
				'message'		=> $row->message,
			);
			return $data;
			
		}else{
		
			$this->errors = array(
				'error' => 'Chat message doesn\'t exist!',
			);
			return false;
		
		}
		
	}
	
	public function get_errors(){
		return $this->errors;
	}

}