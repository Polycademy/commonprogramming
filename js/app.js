'use strict';

/* ==========================================================================
   BOOTSTRAPPER
   ========================================================================== */

//app is an module that is dependent on several top level modules
var app = angular.module('App', [
	'Controllers',
	'Filters',
	'Services',
	'Directives',
	'ngResource', //for RESTful resources
	'ngCookies',
	'ui', //from angular UI
	'ui.bootstrap'
]);

//Define all the page level controllers (Application Logic)
angular.module('Controllers', []);
//Define all shared filters (UI Filtering)
angular.module('Filters', []);
//Define all shared services (Interaction with Backend)
angular.module('Services', []);
//Define all shared directives (UI Logic)
angular.module('Directives', []);

/* ==========================================================================
   ROUTER
   ========================================================================== */

//Define all routes here and which page level controller should handle them
app.config(
	[
		'$routeProvider',
		'$locationProvider',
		'UsersServProvider',
		function($routeProvider, $locationProvider, UsersServProvider) {
			
			//setting up the authentication/register page (before any instantiation)
			UsersServProvider.setLoginPage = '/auth';
			
			//HTML5 Mode URLs
			$locationProvider.html5Mode(true).hashPrefix('!');
			
			//Routing
			$routeProvider
				.when(
					'/',
					{
						templateUrl: 'home_index.html',
						controller: 'HomeIndexCtrl'
					}
				)
				.when(
					'/auth',
					{
						templateUrl: 'auth_index.html',
						controller: 'AuthIndexCtrl'
					}
				)
				.when(
					'/courses',
					{
						templateUrl: 'courses_index.html',
						controller: 'CoursesIndexCtrl'
					}
				)
				.when(
					'/blog',
					{
						templateUrl: 'blog_index.html',
						controller: 'BlogIndexCtrl'
					}
				)
				.when(
					'/canvas',
					{
						templateUrl: 'canvas_index.html',
						controller: 'CanvasIndexCtrl'
					}
				)
				.otherwise(
					{
						redirectTo: '/'
					}
				);
			
		}
	]
);

/* ==========================================================================
   CONFIGURE ANGULAR UI
   ========================================================================== */
/*//not using currently
app.value('ui.config', {
	select2: {
		allowClear: true
	}
});
*/

/* ==========================================================================
   GLOBAL FEATURES
   ========================================================================== */

app.run([
	'$rootScope',
	'$cookies',
	'$http',
	function($rootScope, $cookies, $http){
	
		//XSRF INTEGRATION
		$rootScope.$watch(
			function(){
				return $cookies[serverVars.csrfCookieName];
			},
			function(){
				$http.defaults.headers.common['X-XSRF-TOKEN'] = $cookies[serverVars.csrfCookieName];
			}
		);		
		
	}
]);