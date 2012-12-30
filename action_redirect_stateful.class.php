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
	public function __construct($flow, $params = array())
	{
		parent::__construct($flow, array_merge(array('state_var' => array('state', 'redirect_uri')), $params));
	}

	/**
	 * In addition to sending the redirect header, we also store the contents of the state variable in SESSION
	 *
	 * @return true if the redirect was successfully performed
	 */
	public function process()
	{
		// :SHONK: we have to store our progress *now*, since the parent action will send a Location: header. Need to think of a better way of handling this.
		$this->flow->setProgress($this->flowIndex);

		if ($this->debug) {
			$this->debug[] = 'Storing stateful flow information...';
		}

		// also store the connection state nonce
		if (isset($this->params['state_var'])) {
			if (!is_array($this->params['state_var'])) {
				$this->params['state_var'] = array($this->params['state_var']);
			}

			foreach ($this->params['state_var'] as $k) {
				$this->flow->storage->setState($k, $this->params['get'][$k]);
			}
		}

		return parent::process();
	}
}
