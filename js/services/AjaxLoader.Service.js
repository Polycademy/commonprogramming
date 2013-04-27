'use strict';

/**
 * AJAX Loading Screen Toggle. It injects itself as part of the response interceptors!
 */
angular.module('Services')
	.config([
		'$httpProvider',
		function($httpProvider){
		
			//AJAX Loader
			var ajaxQueue = 0;
			var ajaxLoaderImg = $('.ajax_loader');
			
			//you can do dependency injection inside the callback to interceptors
			$httpProvider.responseInterceptors.push(['$q', function($q) {
					
				return function(promise){
				
					ajaxQueue++;
					ajaxLoaderImg.show();
					
					return promise.then(
						function(successResponse){
						
							//if the number of loadings is still positive, we want to continue hiding
							//if(0) false, so its like if(!0), if it is true (still positive)
							if (!(--ajaxQueue)){
								ajaxLoaderImg.hide();
							}
							
							//return the response, we're not doing anything interesting here
							return successResponse;
							
						},
						function(failureResponse){
						
							if (!(--ajaxQueue)){
								ajaxLoaderImg.hide();
							}
							
							return $q.reject(failureResponse);
							
						}
					);
					
				};
						
			}]);
		
		}
	]);