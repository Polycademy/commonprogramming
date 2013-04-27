<?php

class Super extends CI_Controller{

	public function __construct(){
		parent::__construct();
	}
	
	public function index(){
		var_dump('You hit the index() of Super');
	}
	
	public function show($id){
		var_dump('You hit the show() of Super with ' . $id);
	}

}