<?php
/**
 * Generic implementation of a series of connected steps to be performed
 * as a single OAuth flow action.
 *
 * This class is both array-indexable (for assigning actions), and iterable
 * (for processing them in order). This should make it syntactically very brief.
 *
 * @package	goauth
 * @author	Sam Pospischil <pospi@spadgos.com>
 * @since	5 Dec 2012
 */
class GOAuthFlow implements ArrayAccess, Iterator
{
	private $actions = array();

	const SESSION_PROGRESS_KEY	= 'goauth_progress';
	const SESSION_STATE_KEY		= 'goauth_state';

	protected $clientId;
	protected $clientSecret;

	/**
	 * Create a new auth flow.
	 *
	 * @param string $clientId		Facebook app key
	 * @param string $clientSecret	Facebook app secret key
	 * @param mixed  $scope    		single permission or array of permissions to request. @see http://developers.facebook.com/docs/concepts/login/permissions-login-dialog/
	 */
	public function __construct($clientId, $clientSecret, $scope = null)
	{
		self::ensureSession();

		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;
	}

	/**
	 * Gets the current progress of the flow (index we are up to in $this->actions)
	 *
	 * :WARNING: if your own code is responsible for initializing sessions, be certain that you don't call this method
	 * 			 before doing so as this method will begin a session if one is not already present.
	 *
	 * @return scalar
	 */
	public function getCurrentProgress()
	{
		return isset($_SESSION[GOAuthFlow::SESSION_PROGRESS_KEY]) ? $_SESSION[GOAuthFlow::SESSION_PROGRESS_KEY] : null;
	}

	/**
	 * Executes the flow, picking up where we left off.
	 *
	 * :WARNING: if your own code is responsible for initializing sessions, be certain that you don't call this method
	 * 			 before doing so as this method will begin a session if one is not already present.
	 *
	 * @param  array  $params initial parameters to pass for beginning the flow.
	 */
	public function execute($params = array())
	{
		$startAt = $this->getCurrentProgress();
		$started = false;

		foreach ($this->actions as $i => $action) {
			if ($startAt !== null && !$started) {
				if ($i == $startAt) {
					$started = true;
				}
				continue;
			}

			$action->setParams($params);		// pass parameters to first action
			$params = $action->process();		// use the return value as input to the next action in the flow

			// if this was a terminal action, jump out
			if ($action->isFinal()) {

				// store the progress we're at in the flow in a session so we can pick it up easily later
				$_SESSION[self::SESSION_PROGRESS_KEY] = $i;

				return $params;
			}
		}
		return $params;		// return the result from the terminal action for further processing
	}

	/**
	 * Clean up session state after completing an auth flow.
	 */
	public function finalize()
	{
		unset($_SESSION[self::SESSION_PROGRESS_KEY]);
		unset($_SESSION[self::SESSION_STATE_KEY]);
	}

	//--------------------------------------------------------------------------
	// Array implementation
	//--------------------------------------------------------------------------

	public function offsetGet($key)
	{
		return isset($this->actions[$key]) ? $this->actions[$key] : false;
	}

	public function offsetSet($k, $v)
	{
		if (!isset($k)) {
			$this->actions[] = $v;
		} else if (!$k) {
			$this->actions[0] = $v;
		} else {
			$this->actions[$k] = $v;
		}

		return true;
	}

	public function offsetExists($key)
	{
		return isset($this->actions[$key]);
	}

	public function offsetUnset($key)
	{
		unset($this->actions[$key]);
	}

	//--------------------------------------------------------------------------
	// Iterator implementation
	//--------------------------------------------------------------------------

	public function rewind() {
		reset($this->actions);
	}

	public function current() {
		return current($this->actions);
	}

	public function key() {
		return key($this->actions);
	}

	public function next() {
		return next($this->actions);
	}

	public function valid() {
		return key($this->actions) !== null;
	}

	public function count() {
		return count($this->actions);
	}

	//--------------------------------------------------------------------------
	//	Helpers
	//--------------------------------------------------------------------------

	/**
	 * Return some random data for passing with various requests as nonces
	 * @return string
	 */
	public static function getNonce()
	{
		return md5(uniqid(rand(), true));
	}

	public static function ensureSession()
	{
		if (!session_id()) {
			new Session('goauth');
		}
	}

	//--------------------------------------------------------------------------
	//	Debug layer
	//--------------------------------------------------------------------------

	private $debug = false;	// ProcessLogger instance used for debugging

	public function enableDebug()
	{
		$this->debug = new ProcessLogger();
	}
}
