'use strict';

angular.module('Services')
	.service('UtilitiesServ', [
		'$window',
		'$timeout',
		function($window, $timeout){
		
			/**
			 * Cross browser request animation frame
			 */
			this.requestAnimationFrame = (function(callback) {
				return $window.requestAnimationFrame || $window.webkitRequestAnimationFrame || $window.mozRequestAnimationFrame || $window.oRequestAnimationFrame || $window.msRequestAnimationFrame ||
				function(callback) {
					$timeout(callback, 1000 / 60);
				};
			})().bind($window); //this is required because we're aliasing a property that is part of the window
			
			/**
			 * Finds out what the true type is, giving back capitalised "Array" or "Object" or "Function"... etc
			 * see http://juhukinners.wordpress.com/2009/01/11/typeof-considered-useless-or-how-to-write-robust-type-checks/
			 */
			this.type = function(value){
				return Object.prototype.toString.call(value).slice(8, -1);
			};
			
			/**
			 * Preloads images for canvas
			 * Returns an object similar to sources but the actual image value not the src
			 */
			this.canvasPreloadImages = function(sources, callback){
			
				//sources is a object
				if(this.type(sources) !== 'Object' || this.type(callback) !== 'Function'){
					return false;
				}
				
				var images = {}; //this is the final object to be returned
				var loadedImages = 0; //keeps track of how many images loaded
				var numImages = Object.keys(sources).length; //total number of sources
				
				//in order to be more efficient, functions should be created outside of a loop and assigned
				//handler is a closure, so no need to specify arguments, it will be as if it was executed inside the loop!
				var makeHandler = function(){
					return function(){
						//pre increment which means loadedImages increments each time is loaded
						if(++loadedImages >= numImages) {
							//when all images are loaded, run the callback, passing in the image of all the images
							callback(images);
						}
					};
				};
				
				//src is the prop name, not prop value
				for(var src in sources){
				
					if(sources.hasOwnProperty(src)){
						
						//setup the image object
						images[src] = new Image();
						//attach the handler
						images[src].onload = makeHandler();
						//add the src to the image object
						images[src].src = sources[src];
					
					}
					
				}
				
			};
		
		}
	]);
