<?php

namespace PolyAuth;

//this project will extend upon RBAC package from LeighMacDonald
use RBAC\UserInterface;

//this class will not only be used for the interface for RBAC, but will also contain all the methods necessary to interact with a single user

class UserAccount implements UserInterface{

    private $user_id;
    private $roles = [];
	
	//you should construct a new user based on their user id each time you need a new user
	public function __construct($user_id){
		$this->user_id = $user_id;
	}
	
	//get the current id
	public function id(){
		return $this->user_id;
	}
	
	//this is used by the RBAC!
	public function loadRoleSet(RoleSet $role_set){
		$this->roles = $role_set;
	}
	
	//use this to get all the roles of the current user
	public function getRoleSet(){
		return $this->roles;
	}
	
}