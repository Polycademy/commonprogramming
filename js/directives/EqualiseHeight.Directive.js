'use strict';

/**
 * Equalise Heights
 * 
 * @param string jQuery Selector of which to equalise
 */
angular.module('Directives')
	.directive('equaliseHeightsDir', [
		function(){
			return {
				scope: {},
				link: function(scope, element, attributes){
				
					//we're not using scope.watch here because, watch would require the values to change, and it can't watch browser events like window.resize, also we're not watching value changes, but events! therefore we're doing jquery event binding
					//another method here: http://jsfiddle.net/bY5qe/
					
					var items = element.find(attributes.equaliseHeightsDir);
					
					var equaliseHeight = function(){
						var maxHeight = 0;
						items
							.height('auto') //reset the heights to auto to see if the content pushes down to the same height
							.each(function(){
								//find out which has the max height (wrap it in angular.element, or else each this is the raw DOM)
								maxHeight = angular.element(this).height() > maxHeight ? angular.element(this).height() : maxHeight; 
							})
							.height(maxHeight); //then make them all the same maximum height!
					};
					
					//run it once
					equaliseHeight();
					
					//on the resize event from jquery, run a function, this function is a pointer!
					angular.element(window).resize(equaliseHeight);
				
				}
			};
		}
	]);