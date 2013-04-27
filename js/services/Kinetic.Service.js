'use strict';

angular.module('Services')
	.factory('KineticServ', [
		function(){
		
			//assuming Kinetic exists on the page
			//allowing KineticServ to be dependency injected
			return Kinetic;
		
		}
	]);