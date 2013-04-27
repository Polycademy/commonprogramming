'use strict';

angular.module('Filters')
	.filter('Interpolate', [
		function(){
			return function(text){
				return String(text).replace(/\%VERSION\%/mg, 'example');
			};
		}
	]);