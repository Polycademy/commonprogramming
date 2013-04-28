'use strict';

angular.module('Controllers')
	.controller('CanvasCtrl', [
		'$scope',
		'UsersServ',
		'WebSocketsServ',
		function($scope, UsersServ, WebSocketsServ){
			
			WebSocketsServ.connect(
				'chat',
				function(session){
				
					//open connection function
					console.log(session);
					
					WebSocketsServ.call('chat', 'http://polycademy.com/chat', 'createRoom', ['12355']).then(
						function (res) {
							// RPC success callback
							console.log("got result: " + res);
						},
						function (error, desc) {
							// RPC error callback
							console.log("error: " + desc);
						}
					);
					
				},
				function(code, reason){
					//close connection function
					console.log(code, '<- CODE');
					console.log(reason, '<- REASON');
				}
			);
			
		}
	]);