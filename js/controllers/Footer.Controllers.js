'use strict';

angular.module('Controllers')
	.controller('FooterCtrl', [
		'$scope',
		'httpMessages',
		function($scope, httpMessages){
			
			$scope.httpMessages = httpMessages;
			
		}
	]);