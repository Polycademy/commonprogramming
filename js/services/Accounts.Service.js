'use strict';

angular.module('Services')
	.factory('AccountsServ', [
		'$resource',
		function($resource){
		
			return $resource(
				'api/accounts/:id',
				{},
				{
					update: {
						method: 'PUT'
					}
				}
			);
		
		}
	]);