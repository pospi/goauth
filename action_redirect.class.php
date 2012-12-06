<?php
/**
 * Implements a redirect action as part of an OAuth flow.
 *
 * @package	goauth
 * @author	Sam Pospischil <pospi@spadgos.com>
 * @since	5 Dec 2012
 */
class GOAuthAction_Redirect extends GOAuthAction
{
	protected $redirectUrl;			// parameterless endpoint URI

	public function __construct($params = array(), $redirectUrl = null)
	{
		if ($redirectUrl) {
			$params['_url'] = $redirectUrl;
		}
		$this->setParams($params);
	}

	/**
	 * @param	array	$params			Parameters to send with this redirect. Use '_url' to set the URL itself.
	 */
	public function setParams($params = array())
	{
		if (isset($params['_url'])) {
			$this->redirectUrl = $params['_url'];
			unset($params['_url']);
		}
		parent::setParams($params);
	}

	/**
	 * Process the redirect action. The script continues after this point -
	 * you may perform any additional logic before calling finalise(), which will
	 * then terminate the script on demand.
	 *
	 * @return true if the redirect was successfully performed
	 */
	public function process()
	{
		if (headers_sent()) {
			return false;
		}

		$resp = new Response();
		$resp->redirect(Request::getURLString($this->redirectUrl, $this->params));

		return true;
	}

	/**
	 * Finalises all actions performed as part of this endpoint.
	 * In this case, that means terminating the script to prevent any further output
	 * modifying the success of the performed redirect.
	 */
	public function finalise()
	{
		exit;
	}
}
