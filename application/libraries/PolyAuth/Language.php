<?php

namespace PolyAuth;

//this is just so we can autoload this language class and so it can be replaced
class Language implements \ArrayAccess{

	public $lang = array();
	
	public function __construct(){
	
		$this->lang = array(
			// Account Creation
			'account_creation_successful'			=> 'Account Successfully Created',
			'account_creation_unsuccessful'			=> 'Unable to Create Account',
			'account_creation_duplicate_email'		=> 'Email already used or invalid',
			'account_creation_duplicate_username'	=> 'Username already used or invalid',
			'account_creation_invalid'				=> 'Cannot register without an identity or password.',
			'account_creation_email_invalid'		=> 'Cannot use email activation without a emails being registered.',
			'account_creation_assign_role'			=> 'Could not assign the role to newly created account.',
			// Password
			'password_change_successful'			=> 'Password Successfully Changed',
			'password_change_unsuccessful'			=> 'Unable to Change Password',
			'forgot_password_successful'			=> 'Password Reset Email Sent',
			'forgot_password_unsuccessful'			=> 'Unable to Reset Password',
			// Activation
			'activate_successful'					=> 'Account Activated',
			'activate_unsuccessful'					=> 'Unable to Activate Account',
			'deactivate_successful'					=> 'Account De-Activated',
			'deactivate_unsuccessful'				=> 'Unable to De-Activate Account',
			'activation_email_successful'			=> 'Activation Email Sent',
			'activation_email_unsuccessful'			=> 'Unable to Send Activation Email',
			// Login / Logout
			'login_successful'						=> 'Logged In Successfully',
			'login_unsuccessful'					=> 'Incorrect Login',
			'login_unsuccessful_not_active'			=> 'Account is inactive',
			'login_timeout'							=> 'Temporarily Locked Out.  Try again later.',
			'logout_successful'						=> 'Logged Out Successfully',
			// Account Changes
			'update_successful'						=> 'Account Information Successfully Updated',
			'update_unsuccessful'					=> 'Unable to Update Account Information',
			'delete_successful'						=> 'User Deleted',
			'delete_unsuccessful'					=> 'Unable to Delete User',
			// Groups
			'role_creation_successful'				=> 'Group created Successfully',
			'role_already_exists'					=> 'Group name already taken',
			'role_update_successful'				=> 'Group details updated',
			'role_delete_successful'				=> 'Group deleted',
			'role_delete_unsuccessful'				=> 'Unable to delete group',
			//email
			'email_activation_subject'				=> 'Account Activation Email',
			'email_activation_email_unsent'			=> 'The account activation email failed to be sent.',
		);
	
	}
	
	//this accepts a language array, otherwise it defines the default language, this can be changed on the fly, or you can just translate a few of them
	public function set_language(array $new_language_array){
		$this->lang = array_merge($this->lang, $new_language_array);
	}
	
	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->lang[] = $value;
		} else {
			$this->lang[$offset] = $value;
		}
	}
	
	public function offsetExists($offset) {
		return isset($this->lang[$offset]);
	}
	
	public function offsetUnset($offset) {
		unset($this->lang[$offset]);
	}
	
	public function offsetGet($offset) {
		return isset($this->lang[$offset]) ? $this->lang[$offset] : null;
	}
	
}