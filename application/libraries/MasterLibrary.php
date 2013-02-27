<?php

class MasterLibrary{

	private $dependent;
	
	//this MasterLibrary needs a WorkerLibrary
	public function __construct($object){
	
		$this->dependent = $object;
	
	}
	
	//call this to do something
	public function do_something(){
	
		$this->dependent->do_something();
	
	}

}