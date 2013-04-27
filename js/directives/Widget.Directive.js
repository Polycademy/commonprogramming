'use strict';

angular.module('Directives')
	.directive('widgetDir', [
		function(){
			return {
				templateUrl: 'directive_widget.html',
				link: function(scope, element, attributes){
					console.log(scope);
					console.log(element);
					console.log(attributes);
				}
			};
		}
	]);