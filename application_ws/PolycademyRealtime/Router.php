<?php

namespace PolycademyRealtime;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class Router implements WampServerInterface {

	private $routes = array();
	
	/**
	 * Binds the route ("/chess") to a class implementing the WampServerInterface
	 * It adds the end point component to an array of routes
	 */
	public function set_route($path, WampServerInterface $component){
	
		$this->routes[$path] = $component;
	
	}
	
	/**
	 * Checks if the request path is compatible with the array of routes
	 * If it is, it will add the correct route object to the $conn object
	 * Each $conn connection will have one route
	 * The route can then be accessed in the other event handlers
	 */
    public function onOpen(ConnectionInterface $conn){
	
		if(array_key_exists($conn->WebSocket->request->getPath(), $this->routes)) {
			echo "The connection ({$conn->resourceId}) has been routed to ({$conn->WebSocket->request->getPath()}).\n";
			$conn->route = $this->routes[$conn->WebSocket->request->getPath()];
			$conn->route->onOpen($conn);
		} else {
			echo "The connection ({$conn->resourceId}) tried to access unknown resource.\n";
			$conn->close();
		}
		
    }
	
	/**
	 * If the connection's route still exists, then pass it on
	 */
    public function onClose(ConnectionInterface $conn) {
	
		if(isset($conn->route)){
			$conn->route->onClose($conn);
		}
    
	}
	
	/**
	 * Pass on the subscribe
	 */
    public function onSubscribe(ConnectionInterface $conn, $topic){
	
		$conn->route->onSubscribe($conn, $topic);
	
    }
	
	/**
	 * Pass on the unsubscribe
	 */
    public function onUnSubscribe(ConnectionInterface $conn, $topic) {
	
		$conn->route->onUnSubscribe($conn, $topic);
	
    }
    
	/**
	 * Pass on the call
	 */
	public function onCall(ConnectionInterface $conn, $id, $method, array $params) {
	
		$conn->route->onCall($conn, $id, $method, $params);
	
    }
	
	/**
	 * Pass on the publish
	 */
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude = array(), array $eligible = array()) {
	
		$conn->route->onPublish($conn, $topic, $event, $exclude, $eligible);
	
    }
	
	/**
	 * Pass on the errors the route!
	 */
    public function onError(ConnectionInterface $conn, \Exception $e) {
	
		$conn->route->onError($conn, $e);
    
	}
	
	/**
	 * This function is called from ZMQ when the RESTful app returns a response
	 * It should handle broadcasting depending on the nature of the message
	 */
	public function onReturn($message){
	
		$conn->route->onReturn($message);
	
	}
	
}