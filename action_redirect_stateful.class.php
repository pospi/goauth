<?php
/**
 * Implements a redirect action which passes a persistent nonce to maintain state between
 * us and the remote server.
 *
 * Parameters:
 * 	uri			- URL to redirect the remote user to
 * 	get			- any GET parameters to pass with the redirect
 *  state_var	- the name(s) of stateful information present in GET which we should store for comparison on return
 *
 * @package	goauth
 * @author	Sam Pospischil <pospi@spadgos.com>
 * @since	6 Dec 2012
 */
class GOAuthAction_RedirectStateful extends GOAuthAction_Redirect
{
	protected $params = array('state_var' => array('state', 'redirect_uri'));

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
		if ($ok && isset($this->params['state_var'])) {
			if (!is_array($this->params['state_var'])) {
				$this->params['state_var'] = array($this->params['state_var']);
			}

			$state = array();
			foreach ($this->params['state_var'] as $k) {
				$state[$k] = $this->params['get'][$k];
			}
			$_SESSION[GOAuthFlow::SESSION_STATE_KEY] = $state;
		}

		return $ok;
	}
}
