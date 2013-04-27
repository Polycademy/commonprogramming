<?php defined('BASEPATH') OR exit('No direct script access allowed');

//VERY IMPORTANT, if your are using codeigniter session tables, you need to switch off sess_use_tables before running migrations! Or else, this controller may try to access the sessions tables when it hasn't been created yet.
//Switch it back on after you've migrated, then you don't need to worry about it later
class Migrate extends CI_Controller {
 
	public function __construct(){
 
		parent::__construct();
		$this->load->library('migration');
 
	}
	
	public function index(){
	
		echo 'Migration is initialised. Make sure this is not accessible in production! You may need to run this first before running any of the functions, especially if they make consecutive modifications to your tables. Now go do the latest, current, version or restart!';
	
	}
 
	public function latest(){ 
	
		if(!$this->migration->latest()){
			show_error($this->migration->error_string());
		}
 
	}
	
	public function current(){
	
		if(!$this->migration->current()){
			show_error($this->migration->error_string());
		}
	
	}
	
	public function version($num){
	
		$this->migration->version($num);
	
	}
	
	//restarts the migration from 0 to the number specified or latest
	public function restart($num = false){
	
		$this->migration->version(0);
		
		if(!empty($num)){
		
			if(!$this->migration->version($num)){
				show_error($this->migration->error_string());
			}
		
		}else{
		
			if(!$this->migration->latest()){
				show_error($this->migration->error_string());
			}
		
		}
	
	}
 
}