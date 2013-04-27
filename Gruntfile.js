module.exports = function(grunt){

	/*
		STEPS:
		1. compile main.less to main.css (and compress)
		2. lints all the non concatenated files
		3. concatenate all of your javascript files (ignoring vendor.js and main.min.js) into main.min.js
		4. lints the concatenated file
		5. minify main.min.js to main.min.js
		6. delete the old build directory
		7. copy over everything to the build directory
			a) Except:
				css (except main.css)
				js (except main.min.js and vendor)
				bin, 
				tests, 
				tools, 
				node_modules, 
				.git,
				.c9revisions,
				"application/logs/*", 
				"application/cache/*", 
				"application/config/development/*",
				Gruntfile.js, 
				package.json, 
				README.md, 
				composer.lock
				composer.json
				.gitignore, 
				.gitattributes, 
		8. change index.php to use 'production' constant (inside the build directory)
	*/

	//project configuration
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'), //get the package.json to load dependencies!
		less:{
			main: { //apparently needs a target...
				options: {
					yuicompress: true
				},
				files: {
					"css/main.css": "css/main.less" //target -> source
				}
			}
		},
		concat:{
			dist:{
				src: [
						'js/app.js', 
						'js/controllers/*.js', 
						'js/directives/*.js', 
						'js/filters/*.js', 
						'js/services/*.js', 
						'js/animations/*.js'
				],
				dest: 'js/main.min.js'
			}
		},
		jshint:{
			options: {
				camelcase: true,
				eqeqeq: true,
				es5: true,
				esnext: true,
				globalstrict: true,
				browser: true,
				expr: true, //allows expressions such as 'use strict';
				devel: true, //false when doing true production
				globals: {
					jQuery: true,
					$: true,
					angular: true,
					serverVars: true,
					Kinetic: true,
					ab: true
				}
			},
			beforeconcat: [
				'js/app.js', 
				'js/controllers/*.js', 
				'js/directives/*.js', 
				'js/filters/*.js', 
				'js/services/*.js', 
				'js/animations/*.js'
			],
			afterconcat: ['js/main.min.js']
		},
		uglify: {
			options: {
				banner: '/*! <%= pkg.name %> built on <%= grunt.template.today("yyyy-mm-dd") %> by <%= pkg.author %> */\n' //setting up the top line comment
			},
			build: {
				src: 'js/main.min.js',
				dest: 'js/main.min.js'
			}
		},
		clean: ['build/**'],
		copy:{
			main:{
				files:[
					{src: ['**'], dest: 'build/', dot: true, filter: function(filepath){
					
						//directory separator
						var dir = require('path').sep;
						
						// ignoring "css/" except css/main.css
						var cssPattern = new RegExp('^css\\'+dir);
						if(cssPattern.test(filepath) && filepath != 'css'+dir+'main.css'){
							grunt.log.writeln('Not copying: ' + filepath);
							return false;
						}
						
						// ignoring "js/" except  js/main.min.js OR "js/vendor/"
						var jsPattern = new RegExp('^js\\'+dir);
						var jsVendorPattern = new RegExp('^js\\'+dir+'vendor\\'+dir);
						if(jsPattern.test(filepath)){
						
							//now we're in the js directory
							if(filepath != 'js'+dir+'main.min.js' && !jsVendorPattern.test(filepath)){
								grunt.log.writeln('Not copying: ' + filepath);
								return false;
							}
							
						}
						
						// ignoring various directories
						if(
							/^bin/.test(filepath) || 
							/^tests/.test(filepath) || 
							/^tools/.test(filepath) || 
							/^node_modules/.test(filepath) || 
							/^\.git/.test(filepath) ||
							/^\.c9revisions/.test(filepath)
						){
							grunt.log.writeln('Not copying: ' + filepath);
							return false;
						}
						
						//ignoring inner files
						var logPattern = new RegExp('^application\\'+dir+'logs\\'+dir);
						var cachePattern = new RegExp('^application\\'+dir+'cache\\'+dir);
						var configPattern = new RegExp('^application\\'+dir+'config\\'+dir+'development\\'+dir);
						if(logPattern.test(filepath) || cachePattern.test(filepath) || configPattern.test(filepath)){
							grunt.log.writeln('Not copying: ' + filepath);
							return false;
						}
						
						//ignoring various files
						if(
							filepath === 'README.md' ||
							filepath === 'composer.json' ||
							filepath === 'composer.lock' ||
							filepath === 'Gruntfile.js' || 
							filepath === 'package.json' || 
							filepath === '.gitignore' || 
							filepath === '.gitattributes'
						){
							grunt.log.writeln('Not copying: ' + filepath);
							return false;
						}
						
						return true;
						
					}}
				]
			}
		},
		replace:{
			main:{
				src: ['build/index.php'],
				overwrite: true,
				replacements: [{
					from: /((?:[a-z][a-z]+)\(\'ENVIRONMENT\', isset\(\$_SERVER\[\'CI_ENV\'\]\) \? \$_SERVER\[\'CI_ENV\'\] : \'development\'\).)/g,
					to: "define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'production');"
				}]
			}
		}
	});
	
	//unit testing I don't do it here just yet, they will happen during development, there may be a final unit test
	
	grunt.loadNpmTasks('grunt-shell'); //for random shell commands later on
	grunt.loadNpmTasks('grunt-contrib-less');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-text-replace');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	
	grunt.registerTask('default', ['less', 'concat', 'jshint', 'uglify', 'clean', 'copy', 'replace']);

};