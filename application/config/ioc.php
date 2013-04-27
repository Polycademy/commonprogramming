<?php

/**
 * Pimple uses anonymous functions (lambdas) so it can "lazy load" the classes.
 * The functions will not be processed when the PHP interpreter goes through this file.
 * They will be kept inside the function waiting to be called as part of the container array.
 * Once you call the functions, then the objects will be created! Thus "lazy loading", not "eager loading". Saves memory too!
 * Note Pimple is an object that acts like an array, see the actual Pimple code to see how this works.
 * This usage assumes that you have autoloading working, so that the references to the classes will be autoloaded!
 */

$ioc = new Pimple;

$ioc['WorkerLibrary'] = function($c){
	return new WorkerLibrary;
};

//Demonstration of the self-referential $c to use the WorkerLibrary and to pass it in as a dependency to the MasterLibrary
$ioc['MasterLibrary'] = function($c){
	return new MasterLibrary($c['WorkerLibrary']);
};

//we need to pass the $ioc into the global $config variable, so now it can be accessed by Codeigniter
$config['ioc'] = $ioc;