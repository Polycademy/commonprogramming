<?php

namespace PolyAuth;

//this project will extend upon RBAC package from LeighMacDonald
use RBAC\Subject\Subject;
use RBAC\Role\RoleSet;

//this class extends the Subject which implements the SubjectInterface, it will contain all the methods necessary to interact with the logged in user!
class UserAccount extends Subject{

	protected $user_data = array();

	public function __construct($subject_id, RoleSet $role_set = null){
		parent::__construct($subject_id, $role_set);
	}
	
	public function get_role_set(){
		return $this->getRoleSet();
	}
	
	public function has_permission($permission){
		return $this->hasPermission($permission);
	}
	
	public function require_permission($permission){
		return $this->requirePermission($permission);
	}
	
	public function set_user_data(array $data){
		$this->user_data = array_merge($this->user_data, $data);
	}
	
	public function get_user_data(){
		return $this->user_data;
	}
	
	public function get($key){
		return $this->user_data[$key];
	}
	
}