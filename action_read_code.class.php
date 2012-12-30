<?php
/**
 * Reads the request token from a return authentication request.
 *
 * Parameters:
 * 	code_var	- name of the code variable to read from GET
 *
 * @package	goauth
 * @author	Sam Pospischil <pospi@spadgos.com>
 * @since	6 Dec 2012
 */
class GOAuthAction_ReadCode extends GOAuthAction
{
	protected $params = array('code_var' => 'code');

	/**
	 * @return  the request token from the currently running OAuth authentication request
	 */
	public function process()
	{
		if ($this->debug) {
			$this->debug[] = 'Retrieve request code from URL...';
		}
		return isset($_GET[$this->params['code_var']]) ? $_GET[$this->params['code_var']] : null;
	}
}
