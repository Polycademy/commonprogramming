<?php

class PasswordComplexity
{
	/** constants - are arbritrary numbers - but used for bitwise **/
	const REQUIRE_MIN		 = 1;
	const REQUIRE_MAX		 = 2;
	const REQUIRE_LOWERCASE   = 4;
	const REQUIRE_UPPERCASE   = 8;
	const REQUIRE_NUMBER	  = 16;
	const REQUIRE_SPECIALCHAR = 32;
	const REQUIRE_DIFFPASS	= 64;
	const REQUIRE_DIFFUSER	= 128;
	const REQUIRE_UNIQUE	  = 256;
	protected $_passwordMinLength = 6;
	protected $_passwordMaxLength = 32;
	protected $_passwordDiffLevel = 3;
	protected $_uniqueChrRequired = 4;
	protected $_complexityLevel = 0;
	protected $_issues = array();
	/**
	 * returns the standard options
	 * @return integer
	 */
	public function getComplexityStandard()
	{
		return self::REQUIRE_MIN + self::REQUIRE_MAX + self::REQUIRE_LOWERCASE + self::REQUIRE_UPPERCASE + self::REQUIRE_NUMBER;
	}
	/**
	 *returns all of the options
	 *@return integer
	 */
	public function getComplexityStrict()
	{
		$r = new ReflectionClass($this);
		$complexity = 0;
		foreach ($r->getConstants() as $constant) {
			$complexity += $constant;
		}
		return $complexity;
	}
	public function setComplexity($complexityLevel)
	{
		$this->_complexityLevel=$complexityLevel;
	}
	/**
	 * checks for complexity level. If returns false, it has populated the _issues array
	 */
	public function complexEnough($newPass, $oldPass, $username)
	{
		$enough = TRUE;
		$r = new ReflectionClass($this);
		foreach ($r->getConstants() as $name=>$constant) {
			/** means we have to check that type then **/
			if ($this->_complexityLevel & $constant) {
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