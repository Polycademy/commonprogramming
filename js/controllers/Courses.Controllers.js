'use strict';

angular.module('Controllers')
	.controller('CoursesIndexCtrl', [
		'$scope',
		'CoursesServ',
		function($scope, CoursesServ){
			
			//get all the courses
			CoursesServ.get(
				{},
				function(response){
					// $scope.courses = response;
					console.log(response);
				},
				function(response){
					// if(typeof response.data.error !== 'undefined'){
						// $scope.coursesError = response.data.error.database;
					// }else{
						// $scope.coursesError = 'Uh oh something did not work!';
					// }
				}
			);
			
			//get course 2
			/*
			CoursesServ.get(
				{ id: 2 },
				function(response){
					//all good
					$scope.specificCourse = response;
				},
				function(response){
					//oh no!
					if(typeof response.data.error !== 'undefined'){
						$scope.specificCourseError = response.data.error.database;
					}else{
						$scope.specificCourseError = 'Uh oh something did not work!';
					}
				}
			);
			*/
			
		}
	]);