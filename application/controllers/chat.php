<?php

class Chat extends CI_Controller{

	//THIS chat controller will be accessible via RESTful Routing or CLI
	//if it is a CLI request, you will need to authenticate differently..?
	//actually allow the authentication to happen elsewhere
	//always trust the CLI connection!
	
	public function __construct(){	
		parent::__construct();		
	}
	
	//show all chat messages from one room ID
	public function show($id){}
	
	//cli show all chat messages
	public function cli_show($id){
	
		$context = new ZMQContext();
		$socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my router');
		$socket->connect("tcp://localhost:8081");
		
		$query = $this->Chat_model->read_room($id);
		
		if($query){
			
			//if success send message via ZMQ to WS server
			$output = array(
				'content'	=> $query,
				'code'		=> 200,
			);
			$socket->send(json_encode($output));
		
		}else{
		
			//if failure send message via ZMQ to WS server
		
		}
		
		echo $code . "\n" . (is_array($content)) ? multi_implode($content, "\n") : $content;
		
	}
	
	//create a chat message based on the room id (rooms are created as soon as a message is inserted
	public function create(){
		//non-cli request
	}
	
	public function cli_create(){
	
		/*
		$payload = array(
			'type'			=> 'onPublish',
			'request_id'	=> $request_id,
			'conn'			=> $conn,
			'topic'			=> $topic,
			'event'			=> $event,
			'exclude'		=> $exclude,
			'eligible'		=> $eligible,
		);
		*/
	
		$context = new ZMQContext();
		$socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my router');
		$socket->connect("tcp://localhost:8081");
	
		//cli request (No authentication necessary here)	
		$data = $this->input->stdin(false, true, 'json');
		
		$input_data = array(
			'userId'	=> $data['conn']->Chat->userId,
			'roomId'	=> $data['topic'],
			'message'	=> $event,
		);
		
		$query = $this->Chat_model->create($input_data);
		
		if($query){
		
			$content = $data;
			$code = 'success';
			//when the query succeeds, send a response via ZMQ to WS Server
		
		}else{
		
			$content = current($this->Chat_model->get_errors());
			$code = key($this->Chat_model->get_errors());
			//when the query fails, send a response via ZMQ to WS Server
			
		}
		
		$output = array(
			'request_id'	=> $data['request_id'],
			'content'		=> $content,
			'code'			=> $code,
		);
		
		$socket->send(json_encode($output));
		
		//echo content back
		echo $code . "\n" . (is_array($content)) ? multi_implode($content, "\n") : $content;
		
	}

}