<?php

namespace Realtime\Chat;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

use Realtime\FindBinary;

class Chat implements WampServerInterface {

	protected $binary;
	//auth class
	protected $auth;
	//object of the current user, includes all session data and user data
	protected $current_user;
	//array of rooms_ids (topic/channels) to connections
	protected $rooms = array();
	//array of pending requests
	protected $rooms = array();
	
	//accepts an auth object
	public function __construct($auth){
	
		$this->auth = $auth;
		$find_binary = new FindBinary;
		$this->binary = $find_binary->init_binary('php');
	
	}
	
    public function onOpen(ConnectionInterface $conn) {
    
		//authenticate chat, only general members and up can do it (the group ids cascade up)
		$this->current_user = $this->auth->authenticate($conn, MEMBER_GROUP);
		
		if(!$this->current_user){
		
			echo "Connection to Chat from ({$conn->resourceId}) did not pass authentication.\n";
			$conn->close();
		
		}else{
		
			echo "Connection to Chat from ({$conn->resourceId}) has been opened.\n";
			
			$conn->Chat = new \StdClass;
			$conn->Chat->rooms = array(); //i think this is an array of rooms the current user is in
			
			if(isset($this->current_user->username)){
				$conn->Chat->username = $this->current_user->username;
			}else{
				$conn->Chat->username = 'Guest ' . $conn->resourceId;
			}
			
			if(isset($this->current_user->userId)){
				$conn->Chat->userId = $this->current_user->userId;
			}else{
				$conn->Chat->userId = $conn->resourceId;
			}
			
		}
	
	}
	
    public function onClose(ConnectionInterface $conn) {
	
		echo "Connection ({$conn->resourceId}) has been closed.\n";
		
        foreach($conn->Chat->rooms as $topic => $value) {
            $this->onUnSubscribe($conn, $topic);
        }
	
    }
	
	/**
	 * RPC Call, can be used to execute things that don't fit into pub/sub
	 * Here used to create a new chat room! Use this at the start of creating game session
	 */
    public function onCall(ConnectionInterface $conn, $id, $method, array $params) {
	
		switch($method){
			case: 'createRoom':
			
				//room ids are given to the web sockets from the client side/RESTful app
				$room_id = $params[0];
				
				if(empty($room_id)){
					return $conn->callError($id, 'You cannot create a room without a room ID!');
				}
				
				//if the room id doesnt exist, we create a new room
				if(!array_key_exists($room_id, $this->rooms){
					$this->rooms[$room_id] = new \SplObjectStorage; //$this->rooms will be an array of room ids, which will contain a list of connections, the room_id will be equivalent to a topic/channel
				}
				
				//if it already exists, just return back the id
				return $conn->callResult($id, array('roomId' => $room_id));
				
				break;
				
			default:
				return $conn->callError($id, 'Unknown call');
			break;
		}

	}
	
	/**
	 * Subscription to a specified room id ($topic)
	 * $topic must be an existing room id
	 */
    public function onSubscribe(ConnectionInterface $conn, $topic) {
	
		//if the $topic (room_id) does not exist as part of the rooms, just return nothing
        if(!array_key_exists($topic, $this->rooms)) {
			echo "User ({$conn->resourceId}) tried to subscribe to non existent room ({$topic})\n";
            return;
        }
		
        //Notify everyone this guy has joined the room they're in
		//with the specific room ($topic) the message to be joinRoom, sessionId and username, and of course conn
        $this->broadcast($topic, array('joinRoom', $conn->WAMP->sessionId, $conn->Chat->username), $conn);
		
		// List all the people already in the room to the person who just joined
		foreach($this->rooms[$topic] as $chatter){
			$conn->event($topic, array('joinRoom', $chatter->WAMP->sessionId, $chatter->Chat->username));
		}
		
		//now subscribe the connection to the room
        $this->rooms[$topic]->attach($conn);
		//add the room to the list of rooms that the current $conn is subscribed too
		$conn->Chat->rooms[$topic] = 1;
	
    }
	
	/**
	 * Unsubscription to a specified room id ($topic)
	 * $topic must be an existing room id
	 */
    public function onUnSubscribe(ConnectionInterface $conn, $topic) {
		
		unset($conn->Chat->rooms[$topic]);
		$this->rooms[$topic]->detach($conn);
		
		//if there are no more clients attached to a room
		if($this->rooms[$topic]->count() == 0){
		
			echo "Chat room {$topic} has been closed due to no participants.\n";
			unset($this->rooms[$topic]);
			
		} else {
		
			echo "User {$conn->resourceId} has left chat room {$topic}.\n";
			$this->broadcast($topic, array('leftRoom', $conn->WAMP->sessionId, $conn->Chat->username));
		
		}
	
    }
	
	/**
	 * Publish a message to everybody that is part of a topic
	 * This will store the publish into a pending request
	 * This is because it needs to server to validate the message, store it, before sending it off to everybody
	 */
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude = array(), array $eligible = array()) {
    
		//event is the message
		$event = (string)$event;
		if(empty($event)){
			return;
		}
		$event = htmlspecialchars($event);
		
		//if the current user is not subscribed to the topic or that the topic doesnt exist, fail as you cannot publish
		if(!array_key_exists($topic, $conn->Chat->rooms) || !array_key_exists($topic, $this->rooms)){
			return;
		}
		
		//store the $event message into a pending requests
		$request_id = uniqid(); //this id will be unique and used to identify the request to resolve
		$payload = array(
			'type'			=> 'onPublish',
			'request_id'	=> $request_id,
			'conn'			=> $conn,
			'topic'			=> $topic,
			'event'			=> $event,
			'exclude'		=> $exclude,
			'eligible'		=> $eligible,
		);
		$this->pending_requests[$request_id] = $payload;
		
		//now we need to send the message and request_id to the RESTful APP
		//this CLI request must be ASYNCHRONOUS
		
		if (substr(php_uname(), 0, 7) == "Windows"){
			$cmd = 'start /B ' . $this->binary . ' ' . FCPATH . ' cli chat cli_create > D:/wamp/www/Log.txt 2>&1'; //NOTE CHANGE TO NUL afterwards
		}else{
			$cmd = $this->binary . ' ' . FCPATH . ' cli chat cli_create > /dev/null 2>&1 &';
		}
		
		//we only need STDIN (so we can be asynchronous)
		$descriptorspec = array(
			0 => array("pipe", "r"),
		);
		
		$process = proc_open($cmd, $descriptorspec, $pipes);
		
		if(!is_resource($process)){
			echo "Could not establish process at cli_create!\n";
			return false;
		}
		
		//pump in the payload while being json encoded
		fwrite($pipes[0], json_encode($payload));
		fclose($pipes[0]);
		
		proc_close($process);
		
	}
	
    public function onError(ConnectionInterface $conn, \Exception $e) {
		$conn->close();
    }
	
	//exclude should be an array! (but currently only one conn can be excluded, mainly the current connection
	//fix this later
	public function broadcast($topic, $msg, ConnectionInterface $exclude = null){
		foreach($this->rooms[$topic] as $chatter){
			if ($chatter !== $exclude) {
				$chatter->event($topic, $msg);
			}
		}
	}
	
	//$message (request_id, content, code), each will have more stuff
	public function onReturn($message){
	
		//do a json_decode on the message (make sure it is an associative array)
		$message = json_decode($message, true);
		$request_id = $message['request_id'];
		
		//the message should be an array of (content, code), if code is success, just use it
		if(isset($this->pending_requests[$request_id])){
			
			$payload = $this->pending_requests[$request_id];
			
			if($message['code'] == 'success'){
			
				$this->broadcast($payload['topic'], array('message', $payload['conn']->WAMP->sessionId, $payload['event'], date('c')));
		
			}else{
				
				//uhoh the code was not success
				//send back a message to the person
				
				if(is_array($message['content']){
					$message['content'] = implode(' ', $message['content']);
				}
				
				$payload['conn']->event($payload['topic'], array('message', $payload['conn']->WAMP->sessionId, $message['content'], date('c')));
			
			}
			
			//remove the pending request now
			unset($this->pending_requests[$request_id]);
			
		}else{
			//nothing to do if the request id doesn't exist
			return;
		}
	
	}
	
}