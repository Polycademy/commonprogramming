'use strict';

angular.module('Controllers')
	.controller('HomeCtrl', [
		'$scope',
		function($scope){
			$scope.data = 'HELLO WORLD!';
		}
	]);