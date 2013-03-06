<?php

class Posts extends CI_Controller {

	public function index(){
		echo 'Hi Im the Index';
	}
	
	public function create_new(){
		echo 'Hi Im the create new page!';
	}
	
	public function edit($id){
		echo 'You\'re asking me to edit a post with the id of: ' . $id;
	}
	
	public function show($id){
		echo 'You\'re asking me to show a post with the id of: ' . $id;
	}
	
	public function create(){
		//GET THE POST DATA AND CREATE SOMETHING
	}
	
	public function update($id){
		//GET THE PUT DATA AND UPDATE SOMETHING
	}
	
	public function delete($id){
		//DELETE THE POST WITH THIS ID!
	}
	
}