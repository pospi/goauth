<?php
/**
 * Implements a redirect action as part of an OAuth flow.
 *
 * Parameters:
 * 	uri		- URL to redirect the remote user to
 * 	get		- any GET parameters to pass with the redirect
 * 	page_content	- a string, Response object or array of output chunks to echo for the page upon redirecting.
 * 					  In good practise, this should contain a <meta http-equiv="refresh"> tag to redirec the user agent, as well as a link to the new URL.
 * 					  note that this will only be visible to users running browsers which do not obey header redirects.
 *
 * @package	goauth
 * @author	Sam Pospischil <pospi@spadgos.com>
 * @since	5 Dec 2012
 */
class GOAuthAction_Redirect extends GOAuthAction
{
	protected $params = array('page_content' => 'You are now being redirected to <a href="%1$s">%1$s</a> <meta http-equiv="refresh" content="0;URL=\'%1$s\'">');

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

		// send the redirect header & body
		if ($this->debug) {
			$this->debug[] = "Redirecting to " . $url;
		}

		if (is_string($this->params['page_content'])) {
			$this->params['page_content'] = sprintf($this->params['page_content'], $url);
		}
		$resp = new Response();
		$resp->addBlock($this->params['page_content']);
		$resp->redirect($url);

		return true;
	}

	public function isFinal()
	{
		return true;
	}
}
