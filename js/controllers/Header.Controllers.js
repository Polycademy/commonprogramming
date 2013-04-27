'use strict';

angular.module('Controllers')
	.controller('HeaderCtrl', [
		'$scope',
		function($scope){
			$scope.testData = 'Hello World!';
		}
	]);