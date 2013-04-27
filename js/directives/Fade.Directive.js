'use strict';

/**
 * Fade in then out directive, use it on items.
 *
 * @param int Number of ms to delay the fade.
 */
angular.module('Directives')
	.directive('fadeInOutDir', [
		function(){
			return {
				link: function(scope, element, attributes){
				
					element.parent().show();
					
					element.hide().fadeIn('fast').delay(attributes.fadeInOutDir).fadeOut('slow', function(){
						//we cant use ng-repeat's $last, because our messages happen intermittently
						if(element.is(':last-child')){
							element.parent().hide();
						}
					});
				
				}
			};
		}
	]);