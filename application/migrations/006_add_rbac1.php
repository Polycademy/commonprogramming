<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_rbac1 extends CI_Migration {

	public function up(){
	
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
	
		//when using foreign keys, if you need to drop them, make sure to ignore them and then set them up again
		$this->db->query('SET foreign_key_checks = 0;');
		$this->dbforge->drop_table('auth_permission');
		$this->dbforge->drop_table('auth_role');
		$this->dbforge->drop_table('auth_role_permissions');
		$this->dbforge->drop_table('auth_subject_role');
		$this->db->query('SET foreign_key_checks = 1;');
	
	}
}