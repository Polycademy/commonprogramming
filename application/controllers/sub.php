<?php

class Sub extends CI_Controller{

	public function __construct(){
		parent::__construct();
	}
	
	//at this point, the $id must come from the super, it is impossible to hit the index() of sub with a sub id
	
	public function index($id = false){
	
		//If $id exists, then it is a super id, if it is false, then the Sub must have been hit directly
		/*
			http://example.com/api/sub => index()
			http://example.com/super/1/sub => index(1)
		*/
		
		if(is_numeric($id)){
		
			var_dump('get all the "subs" that are part of the super\'s $id => ' . $id);
		
		}else{
		
			var_dump('get all the "subs"');
		
		}
		
	
	}
	
	//here is where it gets interesting
	//the $id could be the $id of the sub, or it could be the $id of the super
	//it depends on whether the sub is hit directly, or as a subresource of super
	//you can decide on what you prefer by modifying the routes, sometimes it's easier to limit the options available
	//however I'm going to assume ultimate flexibility
	
	public function show($id, $id2 = false){
	
		//if $id2 is false, that could mean
		/*
			http://example.com/api/sub/1 => show(1)
		*/
		//if $id2 is true, that could mean
		/*
			http://example.com/api/super/2/sub/1 => show(2, 1) 
		*/
		
		if(is_numeric($id2)){
		
			//$id is the id of the super
		
			var_dump('get "sub"(s) with the id of $id2 => ' . $id2 . ' that is part of the super with $id => ' . $id);
		
		}else{
		
			//$id is id of the sub
		
			var_dump('get "sub"(s) with the id of $id => ' . $id);
		
		}
		
		//this flexibility is only required if you need to be able to acquire distinct subresources as a plurality of super resources
		//that is http://e.com/super/2/sub/1 is different from http://e.com/super/3/sub/1
		//if the above condition is not relevant to your app, it's easier to not bother with subresource routing
		//just go for http://e.com/super/2 and http://e.com/sub/1
	
	}

}