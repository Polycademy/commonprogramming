'use strict';

angular.module('Services')
	.factory('WebSocketsServ', [
		'$location',
		'AutobahnServ',
		function($location, AutobahnServ){
		
			//object containing all the current web socket sessions, identified by "name"
			//name will also be the actual "path" to the web sockets like "chess" or "chat" without preceding "/"
			//in the future you may have to access it like: connectionSessions['chess/wild']
			var connectionSessions = {};
			
			//default config options
			//allow this to be configurable as a provider soon
			var config = {
				protocol: 'ws://',
				host: $location.host(),
				port: 8080
			};
		
			var WebSocketApi = {
				getSessions: function(){
					return connectionSessions;
				},
				setupConfig: function(newConfig){
					angular.extend(config, newConfig);
				},
				connect: function(name, connectCallback, hangupCallback, sessionOptions){
					
					var path;
					
					if(name){
						//something like ws://localhost/chess:8080
						path = '/' + name;
					}else{
						//ws://localhost:8080
						path = '';
					}
					
					var wsurl = config.protocol + config.host + ':' + config.port + path;
					
					AutobahnServ.connect(
						wsurl,
						function(session){
							//add the session to the list of connections based on the name
							connectionSessions[name] = session;
							connectCallback(session);
						},
						function(code, reason){
							hangupCallback(code, reason);
							//if the session exists, we need to delete it!
							if(typeof connectionSessions[name] !== 'undefined'){
								delete connectionSessions[name];
							}
						},
						sessionOptions
					);				
					
				},
				//one or more arguments after the id (id of the call)
				call: function(name, id, method, params){
					
					var session = connectionSessions[name];
					var identifier = id + '#' + method;
					var fullParameters = [identifier];
					fullParameters.concat(params);
					return session.call.apply(null, fullParameters);
					
				}
			};
			
			return WebSocketApi;
		
		}
	]);