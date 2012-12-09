<?php
/**
 * Check a redirect return nonce to ensure against CSRF.
 *
 * Parameters:
 * 	state_var	- name of the state variable(s) to read from GET
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

		if (!is_array($this->params['state_var'])) {
			$this->params['state_var'] = array($this->params['state_var']);
		}
		foreach ($this->params['state_var'] as $state) {
			if (!isset($_GET[$state]) || !isset($_SESSION[GOAuthFlow::SESSION_STATE_KEY][$state]) || $_GET[$state] != $_SESSION[GOAuthFlow::SESSION_STATE_KEY][$state]) {
				$this->valid = false;
				return false;
			}
		}

		$this->valid = true;

		return $this->valid;
	}

	public function isFinal()
	{
		return !$this->valid;
	}
}
