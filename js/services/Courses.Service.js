'use strict';

angular.module('Services')
	.factory('CoursesServ', [
		'$resource',
		function($resource){
			
			return $resource(
				'api/courses/:id',
				{},
				{
					update: {
						method: 'PUT', //THIS METHOD DOESN'T EXIST BY DEFAULT
					}
				}
			);
			
		}
	]);