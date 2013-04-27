'use strict';

angular.module('Controllers')
	.controller('HomeIndexCtrl', [
		'$scope',
		function($scope){
			$scope.data = 'HELLO WORLD!';
		}
	]);