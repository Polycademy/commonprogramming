<?php defined('BASEPATH') OR exit('No direct script access allowed');
 
class Migrate extends CI_Controller {
 
	public function __construct(){
 
		parent::__construct();
		$this->load->library('migration');
 
	}
 
	public function index(){
 
		//this code means we're always going to get the latest migrations, that's why we didn't need to worry about migration_version in the migration config.
		if(!$this->migration->latest()){
			show_error($this->migration->error_string());
		}
 
	}
	
	public function revert($num){
	
		if(!$this->migration->version($num)){
			show_error($this->migration->error_string());
		}
		
	}
 
}