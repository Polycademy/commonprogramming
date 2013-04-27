'use strict';

/**
 * This service will abstract the creation, session management, and authencation of the current user.
 * If you need to manage users in general, don't use this, and use the AccountsServ directly!
 * There are three possibilities of needing authenticaion:
 *  1. Visits page that completely needs authentication
 *  2. Visits page that partially requests authentication
 *  3. Directly visits authentication login page
 * Six events will be broadcasted/listened to
 *  1. authenticationPartial - Authentication Triggered on an XHR request due to 401 or 403 status code 
 *  2. authenticationLogin - User has successfully logged in
 *  3. authenticationProvided - We have all the user information (propagate changes)
 *  4. authenticationDestroy - Authentication needs to logout (due to account deletion, or when person hits logout button)
 *  5. authenticationLogout - User has successfully logged out (propagate changes)
 *  6. authenticationRegister - User has successfully registered
 *  7. authenticationFull - Authentication is needed for the entire page state
 * You should listen for authenticationPartial in order to trigger a modal box dropdown for login
 * You should listen for authenticationProvided in order to restart any authenticated actions
 * You should trigger authenticationFull in any page controller that needs to check if the person is logged in and to handle it if they are not
 * You should trigger authenticationDestroy whenever someone clicks on a logout button
 * At startup we check if the session exists, if it does, we need the id of the current user, and grab its account data on startup, or else we'll have to login each time.
 */
angular.module('Services')
	.config([
		'$httpProvider',
		function($httpProvider){
			
			$httpProvider.responseInterceptors.push(['$q', '$rootScope', function($q, $rootScope){
			
				return function(promise){
				
					return promise.then(
						function(successResponse){
							return successResponse;
						},
						function(failResponse){
						
							//401 means login required (usually HTTP authentication, but we'll fudge it)
							//403 means that the person does not have enough permissions, regardless of login
							if(failResponse.status === 401 || failResponse.status === 403){
							
								var deferred = $q.defer();
								
								var req = {
									config: failResponse.config,
									deferred: deferred
								};
								
								//this event handler will append the requests
								$rootScope.$broadcast('authenticationPartial', req);
								
								//when the promise gets resolved, the rest of the callback code will activate
								return deferred.promise;
							
							}
							
							//otherwise return the fail response
							return $q.reject(failResponse);
							
						}
					);
				
				};
			
			}]);
		
		}
	])
	.provider('UsersServ', function(){
		
		var userData = {}, //userData is an object containing all the userData
			redirectDestination = '', //for saved redirect destination (by default it's home)
			loginPage = '/auth', //this will be configurable, but by default it's /login
			authenticationPartial = false, //this will be set as true when it's a partial authentication
			authenticatedRequests = []; //saved list of 401 and 403 requests that need authentication to be retried
		
		//allowing loginPage to be configurable
		this.setLoginPage = function(newLoginPage){
			loginPage = newLoginPage;
		};
		
		//the injectable instance
		this.$get = [
			'$rootScope',
			'$location',
			'$http',
			'AccountsServ',
			'SessionsServ',
			function($rootScope, $location, $http, AccountsServ, SessionsServ){
		
				//handling account creation API
				var userAPI = {
				
					getUserData: function(){
						return userData;
					},
					
					setUserData: function(newUserData){
						angular.extend(userData, newUserData); //only 1 dimensional!
					},
					
					//no promises available on $resources, so we just have to add a callback chaining
					registerAccount: function(payload, successFn, failFn){
					
						AccountsServ.save(
							{},
							payload,
							function(successResponse){
							
								//broadcast this event, should result in login attempt
								$rootScope.$broadcast('authenticationRegister', payload);
								
								if(typeof successFn === 'function'){
									successFn(successResponse);
								}
								
							},
							function(failResponse){
							
								if(typeof failFn === 'function'){
									failFn(failResponse);
								}
								
							}
						);
					
					},
					
					deleteAccount: function(id, successFn, failFn){
					
						AccountsServ.remove(
							{
								id: id
							},
							function(successResponse){
							
								//if this person's account got deleted
								//will lead to logout
								$rootScope.$broadcast('authenticationDestroy', id);
								
								if(typeof successFn === 'function'){
									successFn(successResponse);
								}
								
							},
							function(failResponse){
							
								if(typeof failFn === 'function'){
									failFn(failResponse);
								}
								
							}
						);
						
					},
					
					//only works if the person is logged in! (this needs to be ran on startup)
					getAccount: function(id, successFn, failFn){
					
						AccountsServ.get(
							{
								id: id
							},
							function(successResponse){
							
								//store the data on userData
								userData = successResponse.content;
								
								$rootScope.$broadcast('authenticationProvided', userData);
							
								if(typeof successFn === 'function'){
									successFn(successResponse);
								}
								
							},
							function(failResponse){
							
								if(typeof failFn === 'function'){
									failFn(failResponse);
								}
								
							}
						);
					
					},
					
					loginSession: function(payload, successFn, failFn){
					
						SessionsServ.save(
							{},
							payload,
							function(successResponse){
							
								//broadcast successful login (now go grab data for the listeners)
								$rootScope.$broadcast('authenticationLogin', successResponse.content);
								
								if(typeof successFn === 'function'){
									successFn(successResponse);
								}
								
							},
							function(failResponse){
							
								if(typeof failFn === 'function'){
									failFn(failResponse);
								}
								
							}
						);
					
					},
					
					logoutSession: function(id, successFn, failFn){
					
						//default parameter of id will be 0 if id doesn't exist
						id = id || 0;
					
						SessionsServ.remove(
							{
								id: id
							},
							function(successResponse){
							
								$rootScope.$broadcast('authenticationLogout', successResponse.content);
								
								//reset the userData
								userData = {};
								
								if(typeof successFn === 'function'){
									successFn(successResponse);
								}
								
							},
							function(failResponse){
							
								if(typeof failFn === 'function'){
									failFn(failResponse);
								}
								
							}
						);
					
					},
					
					//this acquires session information based on the session id
					getSession: function(id, successFn, failFn){
					
						SessionsServ.get(
							{
								id: id
							},
							function(successResponse){
							
								//if successResponse has an userId, this means is already logged in, so we should go get the account
								if(typeof successResponse.content.userId !== 'undefined'){
									$rootScope.$broadcast('authenticationLogin', successResponse.content.userId);
								}
							
								if(typeof successFn === 'function'){
									successFn(successResponse);
								}
								
							},
							function(failResponse){
							
								if(typeof failFn === 'function'){
									failFn(failResponse);
								}
								
							}
						);
					
					}
				
				};
				
				//PARTIAL AUTHENTICATION DUE TO 401 ON HTTP INTERCEPTORS (403s are for unauthorisation regardless of login, like needing admin permissions)
				$rootScope.$on('authenticationPartial', function(event, args){
				
					//this will only work if you attempt to fill up the session data from the very beginning
					//first check if userData object is empty, meaning that person was not logged in
					if(Object.keys(userData).length === 0){
					
						//clear the redirect
						redirectDestination = '';
						//setup that it is a partial
						authenticationPartial = true;
						//push the request object into the authenticatedRequests
						authenticatedRequests.push(args);
					
					}
					
					//if it reached here, this means person is logged in, but still received either a 401/403
					//either we're not truly logged in (due to tampering)
					//or the person does not have enough permissions to view the data
					//regardless, we now have to ignore the request
					
				});
				
				//FULL AUTHENTICATION TRIGGERED FROM PAGE CONTROLLER
				//this has no group scaling, it just needs person to be logged in!
				$rootScope.$on('authenticationFull', function(event, args){				
				
					//if the userData object is empty, meaning the user has not logged in, then we do the redirect
					//otherwise nothing happens
					if(Object.keys(userData).length === 0){
				
						//remember current page
						redirectDestination = $location.path;
						//redirect to login page (configured path)
						$location.path(loginPage);
					
					}
					
				});
				
				//COMPLETED REGISTRATION (MAY OVERRIDE AUTHENTICATION FULL)
				$rootScope.$on('authenticationRegister', function(event, args){
					
					//point to home after account is created
					redirectDestination = '/';
					userAPI.loginSession({
						username: args.username,
						password: args.password
					});
				
				});
				
				//COMPLETED ACCOUNT DESTRUCTION
				$rootScope.$on('authenticationDestroy', function(event, args){
				
					//point to home after account is deleted
					redirectDestination = '/';
					userAPI.logoutSession(args);
				
				});
				
				//COMPLETED LOGIN
				$rootScope.$on('authenticationLogin', function(event, args){
					
					//get the account information based on the id
					userAPI.getAccount(args);
					
					//redirect to home
					if(redirectDestination){
						$location.path(redirectDestination); //this could be home, a particular page, or no redirection at all
						redirectDestination = '';
					}
					
					var httpRetry = function(request){
						$http(request.config).then(
							function(response){
								request.deferred.resolve(response);
							},
							function(response){
								request.deferred.reject(response);
							}
						);
					};
					
					if(authenticationPartial){
						for(var i = 0; i < authenticatedRequests.length; i++){
							httpRetry(authenticatedRequests[i]);
						}
						authenticationPartial = false; //reset it
						authenticatedRequests = []; //reset the authenticated requests buffer
					}
				
				});
				
				//COMPLETED LOGOUT
				$rootScope.$on('authenticationLogout', function(event, args){
				
					if(redirectDestination){
						$location.path(redirectDestination); //this could be home, if account was deleted, or no redirection if the person simply clicked a button to logout
						redirectDestination = '';
					}
					
				});
				
				return userAPI;
		
			}
		];
		
	})
	.run([
		'UsersServ',
		function(UsersServ){
		
			//This will run at startup in order to determine if the user is already logged in, if it is, we need to grab the userData, and broadcast authenticationProvided!
			
			//acquires the session, if there is a user id, it means we're logged in, so we should broadcast authenticationLogin
			UsersServ.getSession(0);
			//it's 0 meaning that we want the current session, not a particular person's session
			
		}
	]);