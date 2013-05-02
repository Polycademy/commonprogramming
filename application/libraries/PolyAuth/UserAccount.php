<?php

namespace PolyAuth;

//this project will extend upon RBAC package from LeighMacDonald
use RBAC\Subject\Subject;
use RBAC\Role\RoleSet;

//this class extends the Subject which implements the SubjectInterface, it will contain all the methods necessary to interact with the logged in user!
class UserAccount extends Subject{

	public function __construct($subject_id, RoleSet $role_set = null){
		parent::__construct($subject_id, $role_set);
	}
	
}