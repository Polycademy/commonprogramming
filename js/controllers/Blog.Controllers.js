'use strict';

angular.module('Controllers')
	.controller('BlogCtrl', [
		'$scope',
		'UsersServ',
		function($scope){
		
			//demonstrate partial authentication here
			//$scope.$emit('authenticationFull'); //-> this works, it does a proper redirect
			
			$scope.codeMirrorModel = 'var Hello =  "world";';
			
			$scope.oneAtATime = true;

			$scope.groups = [
				{
					title: "Dynamic Group Header - 1",
					content: "Dynamic Group Body - 1"
				},
				{
					title: "Dynamic Group Header - 2",
					content: "Dynamic Group Body - 2"
				}
			];

			$scope.items = ['Item 1', 'Item 2', 'Item 3'];

			$scope.addItem = function() {
				var newItemNo = $scope.items.length + 1;
				$scope.items.push('Item ' + newItemNo);
			};
			
		}
	]);