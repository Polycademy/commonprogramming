<?php

class Secrets{
	
	public function __construct(){


	}

	public function load(){

		//see if there are any .php files
		//if there are, load them and run them
		

		//see if "secrets folder exists"
		if(file_exists(FCPATH . '/secrets') AND is_dir(FCPATH . '/secrets')){
			echo 'YAY';
		}
		
	}

}