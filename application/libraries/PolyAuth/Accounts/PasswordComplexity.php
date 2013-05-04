<?php

class PasswordComplexity{

	const REQUIRE_MIN = 1;
	const REQUIRE_MAX = 2;
	const REQUIRE_LOWERCASE = 4;
	const REQUIRE_UPPERCASE = 8;
	const REQUIRE_NUMBER = 16;
	const REQUIRE_SPECIALCHAR = 32;
	const REQUIRE_DIFFPASS = 64;
	const REQUIRE_DIFFUSER = 128;
	const REQUIRE_UNIQUE = 256;
	
	protected $options;
	protected $lang;
	
	protected $min;
	protected $max;
	protected $diffpass = 3;
	protected $unique = 4;
	protected $complexity_level = 0;
	
	protected $issues = array();
	
	public function __construct(Options $options, Language $language){
	
		$this->options = $options;
		$this->lang = $language;
		$this->set_complexity($this->options['login_password_complexity']);
	
	}
	
	public function set_complexity(array $complexity_options){
	
		$this->min = (!empty($complexity_options['min']) $complexity_options['min'] : 0;
		$this->max = (!empty($complexity_options['max']) $complexity_options['max'] : 0;
		
		//if it is false, then no complexity settings
		if(!empty($complexity_options)){
		
			$complexity_level = 0;
			
			$r = new ReflectionClass($this);
			
			foreach($r->getConstants() as $name => $constant){
			
				//REQUIRE_MIN => min
				$name = explode('_', $name, 2);
				//check if the option is set and it is not strictly equal to false
				if(isset($complexity_options[$name] AND $complexity_options[$name] !== false){
					//add to the complexity level
					$complexity_level += $constant;
				}
			
			}
			
			$this->complexity_level = $complexity_level;
			
		}else{
		
			//a 0 byte would share no bits with any other number
			$this->complexity_level = 0;
			
		}
		
	}
	
	/**
	 * checks for complexity level. If returns false, it has populated the _issues array
	 */
	public function complexEnough($newPass, $oldPass, $username){
	
		$enough = TRUE;
		$r = new ReflectionClass($this);
		foreach ($r->getConstants() as $name => $constant) {
			/** means we have to check that type then **/
			if ($this->complexityLevel & $constant) {
				/** REQUIRE_MIN becomes _requireMin() **/
				$parts = explode('_', $name, 2);
				$funcName = "_{$parts[0]}" . ucwords($parts[1]);
				$result = call_user_func_array(array($this, $funcName), array($newPass, $oldPass, $username));
				if ($result !== TRUE) {
					$enough = FALSE;
					$this->_issues[] = $result;
				}
			}
		}
		return $enough;
		
	}
	public function getPasswordIssues()
	{
		return $this->_issues;
	}
	protected function _requireMin($newPass)
	{
		if (strlen($newPass) < $this->_passwordMinLength) {
			return 'Password is not long enough.';
		}
		return true;
	}
	protected function _requireMax($newPass)
	{
		if (strlen($newPass) > $this->_passwordMaxLength) {
			return 'Password is too long.';
		}
		return true;
	}
	protected function _requireLowercase($newPass)
	{
		if (!preg_match('/[a-z]/', $newPass)) {
			return 'Password requires a lowercase letter.';
		}
		return true;
	}
	protected function _requireUppercase($newPass)
	{
		if (!preg_match('/[A-Z]/', $newPass)) {
			return 'Password requires an uppercase letter.';
		}
		return true;
	}
	protected function _requireNumber($newPass)
	{
		if (!preg_match('/[0-9]/', $newPass)) {
			return 'Password requires a number.';
		}
		return true;
	}
	protected function _requireSpecialChar($newPass)
	{
		if (!preg_match('/[^a-zA-Z0-9]/', $newPass)) {
			return 'Password requires a special character.';
		}
		return true;
	}
	protected function _requireDiffpass($newPass, $oldPass)
	{
		if (strlen($newPass) - similar_text($oldPass,$newPass) < $this->_passwordDiffLevel || stripos($newPass, $oldPass) !== FALSE) {
			return 'Password must be a bit more different than the last password.';
		}
		return true;
	}
	protected function _requireDiffuser($newPass, $oldPass, $username)
	{
		if (stripos($newPass, $username) !== FALSE) {
			return 'Password should not contain your username.';
		}
		return true;
	}
	protected function _requireUnique($newPass)
	{
		$uniques = array_unique(str_split($newPass));
		if (count($uniques) < $this->_uniqueChrRequired) {
			return 'Password must contain more unique characters.';
		}
		return true;
	}
}