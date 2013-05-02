<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_polyauth extends CI_Migration {

	public function up(){
	
		//this migration will setup the tables for user accounts and login attempts
		//it will not setup migrations for the sessions table, that is up to the end user, and to pass a session save handler object
		
		//modify these migrations to reflect extra fields that you want
		//also we need to define default permissions, roles and associated users!
		
		// Table structure for table 'user_accounts'
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'MEDIUMINT',
				'constraint' => '8',
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
			'ipAddress' => array(
				'type' => 'VARBINARY',
				'constraint' => '16'
			),
			'username' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
			),
			'password' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
			),
			'email' => array(
				'type' => 'VARCHAR',
				'constraint' => '100'
			),
			'activationCode' => array(
				'type' => 'VARCHAR',
				'constraint' => '40',
				'null' => TRUE
			),
			'forgottenPasswordCode' => array(
				'type' => 'VARCHAR',
				'constraint' => '40',
				'null' => TRUE
			),
			'forgottenPasswordTime' => array(
				'type' => 'INT',
				'constraint' => '11',
				'unsigned' => TRUE,
				'null' => TRUE
			),
			'rememberCode' => array(
				'type' => 'VARCHAR',
				'constraint' => '40',
				'null' => TRUE
			),
			'createdOn' => array(
				'type' => 'DATETIME',
			),
			'lastLogin' => array(
				'type' => 'DATETIME',
			),
			'active' => array(
				'type' => 'TINYINT',
				'constraint' => '1',
				'unsigned' => TRUE,
				'null' => TRUE
			),
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('user_accounts', true);
		
		// Dumping data for table 'users'
		$data = array(
			'id'					=> '1',
			'ipAddress'				=> inet_pton('127.0.0.1'),
			'username'				=> 'administrator',
			'password'				=> '$2y$10$EiqipvSt3lnD//nchj4u9OgOTL9R3J4AbZ5bUVVrh.Tq/gmc5xIvS',
			'email'					=> 'admin@admin.com',
			'activationCode'		=> '',
			'forgottenPasswordCode'	=> NULL,
			'createdOn'				=> date('Y-m-d H:i:s'),
			'lastLogin'				=> date('Y-m-d H:i:s'),
			'active'				=> '1',
		);
		$this->db->insert('user_accounts', $data);
		
		// Table structure for table 'login_attempts'
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'MEDIUMINT',
				'constraint' => '8',
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
			'ipAddress' => array(
				'type' => 'VARBINARY',
				'constraint' => '16'
			),
			'login' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'null', TRUE
			),
			'time' => array(
				'type' => 'DATETIME',
			)
		));
		
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('login_attempts', true);
	
		//This is the RBAC schema designed for MySQL, it's complex, so we use direct queries!
		//This is LEVEL 1 RBAC, later on you can update to LEVEL 2 RBAC
		
		$create_auth_permission = 
			'CREATE TABLE `auth_permission` (
				`permission_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
				`name`          VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
				`description`   TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
				`added_on`      DATETIME NULL DEFAULT NULL,
				`updated_on`    DATETIME NULL DEFAULT NULL,
				PRIMARY KEY (`permission_id`),
				UNIQUE INDEX `uniq_perm` USING BTREE (`name`)
			) ENGINE = InnoDB;';
		
		$this->db->query($create_auth_permission);
		
		$create_auth_role = 
			'CREATE TABLE `auth_role` (
				`role_id`     INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
				`name`        VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
				`description` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
				`added_on`    DATETIME NULL DEFAULT NULL,
				`updated_on`  DATETIME NULL DEFAULT NULL,
				PRIMARY KEY (`role_id`),
				UNIQUE INDEX `uniq_name` USING BTREE (`name`)
			) ENGINE = InnoDB;';
			
		$this->db->query($create_auth_role);
		
		$create_auth_role_permissions = 
			'CREATE TABLE `auth_role_permissions` (
				`role_permission_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
				`role_id`            INT(10) UNSIGNED NOT NULL,
				`permission_id`      INT(10) UNSIGNED NOT NULL,
				`added_on`           DATETIME NULL DEFAULT NULL,
				PRIMARY KEY (`role_permission_id`),
				FOREIGN KEY (`permission_id`) REFERENCES `auth_permission` (`permission_id`) ON DELETE CASCADE ON UPDATE CASCADE,
				FOREIGN KEY (`role_id`) REFERENCES `auth_role` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE,
				INDEX `fk_role` USING BTREE (`role_id`),
				INDEX `fk_permission` USING BTREE (`permission_id`)
			)
			ENGINE = InnoDB;';
		
		$this->db->query($create_auth_role_permissions);
		
		$create_auth_subject_role = 
			'CREATE TABLE `auth_subject_role` (
				`subject_role_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
				`subject_id`      INT(10) UNSIGNED NOT NULL,
				`role_id`         INT(10) UNSIGNED NOT NULL,
				PRIMARY KEY (`subject_role_id`),
				FOREIGN KEY (`role_id`) REFERENCES `auth_role` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE,
				UNIQUE INDEX `role_id` USING BTREE (`role_id`, `subject_id`),
				INDEX `fk_subjectid` USING BTREE (`subject_id`),
				INDEX `fk_roleid` USING BTREE (`role_id`)
			)
			ENGINE = InnoDB;';
		
		$this->db->query($create_auth_subject_role);
		
	}

	public function down(){
	
		$this->dbforge->drop_table('user_accounts');
		$this->dbforge->drop_table('login_attempts');
		//when using foreign keys, if you need to drop them, make sure to ignore them and then set them up again
		$this->db->query('SET foreign_key_checks = 0;');
		$this->dbforge->drop_table('auth_permission');
		$this->dbforge->drop_table('auth_role');
		$this->dbforge->drop_table('auth_role_permissions');
		$this->dbforge->drop_table('auth_subject_role');
		$this->db->query('SET foreign_key_checks = 1;');
	
	}
}