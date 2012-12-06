<?php
/**
 * Check a redirect return nonce to ensure against CSRF.
 *
 * Parameters:
 * 	state_var	- name of the state variable to read from GET
 * 	state_val	- if provided, compares this value against the stored session value instead of the one located at <state_var>
 *
 * @package	goauth
 * @author	Sam Pospischil <pospi@spadgos.com>
 * @since	6 Dec 2012
 */
class GOAuthAction_CheckState extends GOAuthAction
{
	protected $params = array('state_var' => 'state');

	protected $valid = false;

	/**
	 * Checks the state value from the redirect back from the remote service endpoint.
	 * If there is a mismatch, this action becomes the last in the chain.
	 *
	 * @return true if the state matched OK
	 */
	public function process()
	{
		if (!isset($_SESSION[GOAuthFlow::SESSION_STATE_KEY])) {
			$this->valid = false;
			return false;
		}

		$valToCheck = isset($this->params['state_val']) ? $this->params['state_val'] : $_GET[$this->params['state_var']];

		$this->valid = $_SESSION[GOAuthFlow::SESSION_STATE_KEY] == $valToCheck;

		return $this->valid;
	}

	public function isFinal()
	{
		return $this->valid;
	}
}
