<?php

/**
 * FRONT CONTROLLER for WEBSOCKETS
 * Run it as a daemon using "php index_ws.php"
 * ALL TRANSPORTED DATA IS TO BE ENCODED USING JSON!
 */

//setup autoloading from composer, will expect composer.json to have proper autoloading
require 'vendor/autoload.php';

//RATCHET IMPORTS
use Ratchet\Server\IoServer; //I/O for TCP
use Ratchet\WebSocket\WsServer; //Web Sockets
use Ratchet\Wamp\WampServer; //WAMP protocol
use Ratchet\Server\FlashPolicy; //For flash fallback sockets

//CUSTOM IMPORTS
use PolycademyRealtime\Auth;
use PolycademyRealtime\Router; //for distributing apps
use PolycademyRealtime\Chat\Chat; //for chat app
use PolycademyRealtime\Chess\Chess; //for chess app

echo "Web Socket Server Started!\n";

//CONFIGURATION
define('FCPATH', 'index.php'); //front controller of the RESTful App relative to this file
define('ADMIN_GROUP', '1');
define('MEMBER_GROUP', '2');

//ROUTING
$router = new Router;
$router->set_route('/chat', new Chat(new Auth)); // ws://example.com/chat
$router->set_route('/chess', new Chess(new Auth)); // ws://example.com/chess

//REACT EVENT LOOP
$loop   = React\EventLoop\Factory::create();

//ZMQ LISTENING ON 8081 PORT (PULL SOCKET)
$context = new React\ZMQ\Context($loop);
$pull = $context->getSocket(ZMQ::SOCKET_PULL);
// Binding to 127.0.0.1 means the only localhost can connect
$pull->bind('tcp://127.0.0.1:8081'); 
//expects onReturn to exist on $router (handler for returned messages from RESTful application)
$pull->on('message', array($router, 'onReturn')); 

//WEBSOCKET LISTENING ON 8080 PORT
$webSock = new React\Socket\Server($loop);
$webSock->listen(8080, '0.0.0.0'); // Binding to 0.0.0.0 means remotes can connect
$webServer = new IoServer(new WsServer(new WampServer($router)), $webSock);

//FLASH SOCKET FALLBACK ON 843, CONNECT ON 8080
$flashSock = new React\Socket\Server($loop);
$flashSock->listen(843, '0.0.0.0');
$policy = new FlashPolicy;
$policy->addAllowedAccess('*', 8080);
$webServer = new IoServer($policy, $flashSock);

$loop->run();