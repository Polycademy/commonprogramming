<?php

namespace Realtime\Chess;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class Chess implements WampServerInterface {

	protected $auth;
	
	//accepts an auth object
	public function __construct($auth = null){
		$this->auth = $auth;
	}
	
    public function onOpen(ConnectionInterface $conn) {
    }
    public function onClose(ConnectionInterface $conn) {
    }
    public function onSubscribe(ConnectionInterface $conn, $topic) {
    }
    public function onUnSubscribe(ConnectionInterface $conn, $topic) {
    }
    public function onCall(ConnectionInterface $conn, $id, $method, array $params) {
    }
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude = array(), array $eligible = array()) {
    }
    public function onError(ConnectionInterface $conn, \Exception $e) {
    }
	public function onReturn($message){
	}
	
}