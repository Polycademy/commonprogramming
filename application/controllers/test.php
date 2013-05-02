<?php

use RBAC\Permission;
use RBAC\Role\Role;
use RBAC\Manager\RoleManager;

class Test extends CI_Controller{

	protected $role_manager;
	protected $logger;
	
	public function __construct(){
	
		parent::__construct();
		$this->logger = $this->config->item('ioc')['logger'];
		$this->role_manager  = new RoleManager($this->db->conn_id, $this->logger);
		
	}
	
	public function index(){
	
		//testing out the RBAC
		//this will be most likely be moved to the model code
		
		$perm = Permission::create('admin_view', "");
		if (!$this->role_manager->permissionSave($perm)) {
			var_dump('COULD NOT SAVE PERMISSION');
		}
	
	}


}