<?php

namespace PolycademyRealtime;

//this class attempts to bind the PHP binary
class FindBinary{

	private $operating_system;
	
	//now php_binary can be a string (to the binary or alias) or false
	public function init_binary($php_binary = false){
	
		if(stripos(PHP_OS, 'win') !== false){
			$this->operating_system = 'WIN';
		}else{
			$this->operating_system = 'UNIX';
		}
		
		//if the binary is empty, go find the binary
		if(empty($php_binary)){
		
			$php_binary = $this->find_binary();
		
		}
		
		//otherwise just return it
		return $php_binary;
		
	}

	public function find_binary(){
	
		if ($this->operating_system == 'WIN') {
		
			//big assumption!
			return 'c:\wamp\bin\php\php5.4.3\php.exe';
		
		}else{
		
			//this will work on unix computers
			$php_binary = trim(shell_exec('which php'));
			
			if(!empty($php_binary)){
				return $php_binary;
			}else{
				return false;
			}
			
		}
	
	}

}