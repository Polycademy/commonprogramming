'use strict';

angular.module('Directives')
	.directive('chessCanvasDir', [
		'UtilitiesServ',
		'KineticServ',
		'$timeout',
		function(UtilitiesServ, KineticServ, $timeout){
			return {
				scope: true,
				link: function(scope, element, attributes){
				
					// var chessboard = [];
					// var x = 0;
					// var y = 0;
					
					// this runs 8 times for each row
					// for(var i=1; i<9; i++){
						// i gives me 1, 9, starting point of the square
						
						// y += 100;
						// var j = 1;
						
						// if(i % 2 !== 0){
							// odd row
							// for (j=1; j<9; j++){
							
								// x += 100;
							
								// if(j % 2 !== 0){
									// odd column
									// chessboard.push({
										// type: 'white',
										// coord: String.fromCharCode(65 + (j - 1)) + '' + (9 - i)
									// });
								// }else{
									// even column
									// chessboard.push({
										// type: 'black',
										// coord: String.fromCharCode(65 + (j - 1)) + '' + (9 - i)
									// });
								// }
							
							// }
						// }else{
							// even row
							// for (j=1; j<9; j++){
							
								// x += 100;
								
								// if(j % 2 !== 0){
									// odd column
									// chessboard.push({
										// type: 'black',
										// coord: String.fromCharCode(65 + (j - 1)) + '' + (9 - i)
									// });
								// }else{
									// even column
									// chessboard.push({
										// type: 'white',
										// coord: String.fromCharCode(65 + (j - 1)) + '' + (9 - i)
									// });
								// }
							
							// }
						// }
						
					// }
					
					// console.dir(chessboard);
					
					
					
					// for(var i = 1; i < 65; i++){
					
						// if(i % 8 != 0){
							// if(i % 2 != 0){
								// var square = {
									// type: 'white',
									// id: i
								// };
								// chessboard.push(square);
							// }else{
								// var square = {
									// type: 'black',
									// id: i
								// };
								// chessboard.push(square);
							// }
						// }else{
						
						
						// }
					
					// }
					
					// console.dir(chessboard);
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
					/*
					//establishing the stage
					var stage = new KineticServ.Stage({
						container: element[0], //pointing to current element
						width: 700,
						height: 700
					});
					
					//establishing the layer
					var layer = new KineticServ.Layer();
					
					//image sources
					var sources = {
						yoda: 'img/logo.png'
					};
					
					UtilitiesServ.canvasPreloadImages(sources, function(images){
					
						var yoda = new KineticServ.Image({
							x: 10,
							y: 20,
							image: images.yoda,
							width: images.yoda.width,
							height: images.yoda.height
						});
					
						//creating a rectangle
						var rectangle = new KineticServ.Rect({
							x: 239,
							y: 75,
							width: 100,
							height: 20,
							fill: '#ff0000',
							stroke: 'black',
							strokeWidth: 5,
							cornerRadius: 10,
							draggable: true
						});
						
						var circle = new KineticServ.Circle({
							x: stage.getWidth() / 2,
							y: stage.getHeight() / 2,
							radius: 70,
							fill: 'red',
							stroke: 'black',
							strokeWidth: 2
						});
						
						var ellipse = new KineticServ.Ellipse({
							x: stage.getWidth() / 3,
							y: stage.getHeight() / 3,
							radius:{
								x: 20,
								y: 100
							},
							fill: 'yellow'
						});
						
						var wedge = new KineticServ.Wedge({
							x: 450,
							y: 550,
							radius: 30,
							angle: 0.8 * Math.PI,
							fill: 'red',
							rotationDeg: 60,
							draggable: true
						});
						
						var line = new KineticServ.Line({
							points: [73, 70, 340, 23, 450, 60, 500, 20],
							stroke: 'orange',
							strokeWidth: 3,
							lineCap: 'round',
							dashArray: [33, 10, 3, 20]
						});
						
						line.move(0, 200);
						
						var poly = new KineticServ.Polygon({
							points: [73, 192, 73, 160, 340, 23, 500, 109, 499, 139, 342, 93],
							fill: '#00D2FF',
							stroke: 'black',
							strokeWidth: 5
						});
						
						//adding the rectangle to the layer
						//order matters here! each superseding object overlays previous objects!
						layer.add(rectangle);
						layer.add(circle);
						layer.add(ellipse);
						layer.add(wedge);
						layer.add(line);
						layer.add(poly);
						layer.add(yoda);
						
						//adding the layer to the stage
						stage.add(layer);
						
						yoda.applyFilter(KineticServ.Filters.Grayscale, null, function(){
							layer.draw();
						});
					
					});
					*/
					
					/* //previous code
					var drawRectangle = function(myRectangle, context){
						context.beginPath();
						context.rect(myRectangle.x, myRectangle.y, myRectangle.width, myRectangle.height);
						context.fillStyle = '#8ED6FF';
						context.fill();
						context.lineWidth = myRectangle.borderWidth;
						context.strokeStyle = 'black';
						context.stroke();
					};
					
					var animate = function(myRectangle, canvas, context, startTime){
					
						// updated time, on each iteration of animate, the time is different, it needs to be used for acceleration calculation to know what the current distance needs to be
						var time = (new Date()).getTime() - startTime;
						
						// pixels/second^2 (before meters per second squared)
						var gravity = 200;
						
						//calculating distance based on acceleration (however without velocity * time)
						myRectangle.y = 0.5 * gravity * Math.pow(time / 1000, 2);
						
						console.log(myRectangle.y);
						
						//this is detecting if the rectangle has hit the floor (by minusing the height and border)
						if(myRectangle.y > canvas.height - myRectangle.height - myRectangle.borderWidth / 2) {
							//if it has hit the floor, stay on the floor!
							myRectangle.y = canvas.height - myRectangle.height - myRectangle.borderWidth / 2;
						}
						
						// clear the previous drawing
						context.clearRect(0, 0, canvas.width, canvas.height);

						// draw the new rectangle!
						drawRectangle(myRectangle, context);

						// request new frame, do it again!
						UtilitiesServ.requestAnimationFrame(function(){
							animate(myRectangle, canvas, context, startTime);
						});
					
					};
					
					var canvas = element[0];
					var context = canvas.getContext('2d');
					
					var myRectangle = {
						x: 239,
						y: 0,
						width: 100,
						height: 50,
						borderWidth: 5
					};
					
					//draws the rectangle
					drawRectangle(myRectangle, context);
					
					// wait one second before animating the rectangle
					$timeout(function(){
						var startTime = (new Date()).getTime();
						animate(myRectangle, canvas, context, startTime);
					}, 1000);
					
					*/
				
				}
			};
		}
	]);