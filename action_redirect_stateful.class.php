<?php
/**
 * Implements a redirect action which passes a persistent nonce to maintain state between
 * us and the remote server.
 *
 * Parameters:
 * 	uri			- URL to redirect the remote user to
 * 	get			- any GET parameters to pass with the redirect
 *  state_var	- the name of the state information present in GET which we should store for comparison on return
 *
 * @package	goauth
 * @author	Sam Pospischil <pospi@spadgos.com>
 * @since	6 Dec 2012
 */
class GOAuthAction_RedirectStateful extends GOAuthAction_Redirect
{
	protected $params = array('state_var' => 'state');

	/**
	 * In addition to sending the redirect header, we also store the contents of the state variable in SESSION
	 *
	 * @return true if the redirect was successfully performed
	 */
	public function process()
	{
		if (headers_sent()) {
			return false;
		}

		$ok = parent::process();

		// also store the connection state nonce
		if ($ok && isset($this->params['state_var']) && isset($this->params['get'][$this->params['state_var']])) {
			$_SESSION[GOAuthFlow::SESSION_STATE_KEY] = $this->params['get'][$this->params['state_var']];
		}

		return $ok;
	}
}
