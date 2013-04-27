'use strict';

angular.module('Controllers')
	.controller('DummyIndexCtrl', [
		'$scope',
		function($scope){
		
			//scope inheritance requires an object
			//Index Controller grabs information relevant to current account, passes it down to Sub Controller
			$scope.accountId = 4;
			
			var children = [
				{
					id: 1,
					userId: 2,
					name: 'Sarah'
				},
				{
					id: 2,
					userId: 4,
					name: 'David'
				},
				{
					id: 3,
					userId: 4,
					name: 'Dominic'
				}
			];
			
			//grab the id the relevant children based on parent id
			var relevantChildren = [];
			for(var i=0; i<children.length; i++){
				if(children[i].userId === $scope.accountId){
					relevantChildren.push(children[i]);
				}
			}
			
			$scope.children = relevantChildren;
			
		}
	])
	.controller('ChildSubCtrl', [
		'$scope',
		function($scope){
			
			//here's our child for the ng-repeat
			console.log($scope.child.id);
			
			//use the $scope.child.id in order acquire the plans
			var plans = [
				{
					id: 1,
					childId: 2,
					planTitle: 'Hello world!'
				},
				{
					id: 2,
					childId: 3,
					planTitle: 'Yeaa'
				},
				{
					id: 4,
					childId: 3,
					planTitle: 'Another Plan'
				},
				{
					id: 3,
					childId: 4,
					plantTitle: 'Blastoise'
				}
			];
			
			var relevantPlans = [];
			for(var i=0; i<plans.length; i++){
				if(plans[i].childId === $scope.child.id){
					relevantPlans.push(plans[i]);
				}
			}
			
			$scope.plans = relevantPlans;
		
		}
	])
	.controller('PlanSubCtrl', [
		'$scope',
		function($scope){
		
		}
	]);