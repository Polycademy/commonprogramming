<?php

//calling namespace of interfaces, then the specific classname of \iPhysicalObject
//here Interfaces is a namespace of a folder starting at the root of the PSR-0 specified directory
//You always call use straight to the class you want to use. Then you can call it with impunity!
//the last parameter of use would always correspond to what we want to use
//without use corresponding to the actual class, it will call by prepending the current namespace
use Interfaces\iPhysicalObject;

class Armor implements iPhysicalObject{

	public function weight(){
		echo '1000 KG';
	}

}