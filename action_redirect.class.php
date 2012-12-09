<?php
/**
 * Implements a redirect action as part of an OAuth flow.
 *
 * Parameters:
 * 	uri		- URL to redirect the remote user to
 * 	get		- any GET parameters to pass with the redirect
 *
 * @package	goauth
 * @author	Sam Pospischil <pospi@spadgos.com>
 * @since	5 Dec 2012
 */
class GOAuthAction_Redirect extends GOAuthAction
{
	/**
	 * Process the redirect action. The script continues after this point -
	 * you may perform any additional logic before terminating the script at your discretion.
	 *
	 * @return true if the redirect was successfully performed
	 */
	public function process()
	{
		// generate redirection URL
		$url = $this->params['uri'];
		if (isset($this->params['get'])) {
			$url = Request::getURLString($url, $this->params['get']);
		}

		// check for headers sent, and log error if we can't redirect
		if (headers_sent()) {
			if ($this->debug) {
				$this->debug[] = "Unable to redirect to $url: headers already sent";
			}
			return false;
		}

		// send the redirect header
		if ($this->debug) {
			$this->debug[] = "Redirecting to " . $url;
		}

		$resp = new Response();
		$resp->redirect($url);

		return true;
	}

	public function isFinal()
	{
		return true;
	}
}
