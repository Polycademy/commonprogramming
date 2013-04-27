'use strict';

angular.module('Controllers')
	.controller('AuthIndexCtrl', [
		'$scope',
		'UsersServ',
		function($scope, UsersServ){
		
			$scope.logout = function(){
				//both ways are possible
				//UsersServ.logoutSession(UsersServ.getUserData().id);
				$scope.$emit('authenticationDestroy', UsersServ.getUserData().id); //this does a redirect
			};
			
			$scope.login = function(){
			
				var payload = {
					username: $scope.loginForm.username,
					password: $scope.loginForm.password,
					rememberMe: $scope.loginForm.rememberMe
				};
				
				//reset the submission errors!
				$scope.loginErrors = [];
				$scope.validationErrors = {};
				
				UsersServ.loginSession(
					payload,
					function(successResponse){
						console.log('We are logged in');
						
					},
					function(failResponse){
						console.log('Login failed, here\'s the errors.');
						if(failResponse.data.code === 'validation_error'){
						
							if(Array.isArray(failResponse.data.content)){
							
								//if it is an array of validation errors
								$scope.loginErrors = failResponse.data.content;
							
							}else{
							
								//else its an object of login errors
								$scope.validationErrors = {
									username: failResponse.data.content.username,
									password: failResponse.data.content.password,
									rememberMe: failResponse.data.content.rememberMe
								};
							
							}
							
						}
					}
				);
				
				$scope.$on('authenticationProvided', function(event, args){
					var userData = UsersServ.getUserData();
					console.log(userData);
					$scope.state = true;
				});
			
			};
		
		}
	]);