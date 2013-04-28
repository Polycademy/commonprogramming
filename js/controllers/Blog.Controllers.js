'use strict';

angular.module('Controllers')
	.controller('BlogCtrl', [
		'$scope',
		function($scope){
			
		}
	])
	.controller('BlogPostsCtrl', [
		'$scope',
		function($scope){
		
			console.log($scope.$state);
			console.log($scope.$stateParams);
		
		}
	])
	.controller('BlogPostCtrl', [
		'$scope',
		function($scope){
		
		}
	])
	.controller('BlogPostCommentsCtrl', [
		'$scope',
		function($scope){
			console.log($scope.$state);
			console.log($scope.$stateParams);
		}
	]);