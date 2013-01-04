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
	const DEBUG_ALL = false;

	public static $STORAGE_TYPE = 'session';

	public $storage;		// GOAuthStore instance used to handle session persistence between flow requests

	protected $actions = array();
	protected $lastProcessedAction = null;

	public $client;		// GOAuthClient instance

	protected $scope;		// requested access scope
	protected $redirectUri;	// redirect URI to return to for handling authentication (defaults to current URI)

	/**
	 * Create a new auth flow.
	 *
	 * @param string $client		GOAuthClient instance to authenticate
	 * @param mixed  $scope    		single permission or array of permissions to request. @see http://developers.facebook.com/docs/concepts/login/permissions-login-dialog/
	 * @param string $returnURI		URL to return to from the remote service's auth endpoint to continue the process. Defaults to current URI.
	 */
	public function __construct($client, $scope = null, $returnURI = null)
	{
		$this->client = $client;

		$this->scope = $scope;
		$this->redirectUri = isset($returnURI) ? $returnURI : Request::getFullURI();

		$this->storage = GOAuthStore::getStore(self::$STORAGE_TYPE);

		if (self::DEBUG_ALL) {
			$this->enableDebug();
		}
	}

	/**
	 * Gets the current progress of the flow (index we are up to in $this->actions).
	 * The flow will be resumed from the action *FOLLOWING* this action upon being run.
	 *
	 * :WARNING: if your own code is responsible for initializing sessions, be certain that you don't call this method
	 * 			 before doing so as this method will begin a session if one is not already present.
	 *
	 * @return scalar
	 */
	public function getCurrentProgress()
	{
		return $this->storage->getProgress();
	}

	/**
	 * Sets the progress state of the action.
	 * The flow will be resumed from the action *FOLLOWING* this action upon being run.
	 * @param scalar $prevAction action from which iteration should pick up
	 */
	public function setProgress($prevAction)
	{
		$this->storage->setProgress($prevAction);
	}

	/**
	 * Retrieves the last action processed as part of this flow's execution.
	 * @return GOAuthAction
	 */
	public function getLastAction()
	{
		return $this->lastProcessedAction ? $this->actions[$this->lastProcessedAction] : null;
	}
	public function getLastActionName()
	{
		return $this->lastProcessedAction;
	}

	/**
	 * Executes the flow, picking up where we left off.
	 *
	 * :WARNING: if your own code is responsible for initializing sessions, be certain that you don't call this method
	 * 			 before doing so as this method will begin a session if one is not already present.
	 *
	 * @param  array  $params initial parameters to pass for beginning the flow.
	 *
	 * @return	the result of the last flow action, or NULL if no flow actions occurred. Note that some actions may return FALSE to indicate failures.
	 */
	public function execute($params = array())
	{
		$startAt = $this->getCurrentProgress();
		$started = false;
		$processed = false;

		$remainingToProcess = $this->actions;

		foreach ($this->actions as $i => $action) {
			if ($startAt && !$started) {
				if ($i == $startAt) {
					$started = true;
				}
				unset($remainingToProcess[$i]);
				continue;
			}

			$action->setIndex($i);

			$action->setParams($params, true);		// pass parameters to first action
			$params = $action->process();		// use the return value as input to the next action in the flow

			$processed = true;
			$this->lastProcessedAction = $i;
			unset($remainingToProcess[$i]);

			// if this was a terminal action, jump out
			if ($action->isFinal()) {

				// store the progress we're at in the flow in a session so we can pick it up easily later
				if ($action->shouldResume()) {
					$this->storage->setProgress($i);
				} else {
					$this->storage->setProgress(null);
				}

				return $params;
			}
		}

		// if we've reached the end of the flow, set the access token from the last action's return value
		if (!$remainingToProcess) {
			$this->acceptTokenFromResponse($params);
		}

		return $processed ? $params : null;		// return the result from the terminal action for further processing, or FALSE if nothing happened
	}

	/**
	 * Interprets a response from the service's token exchange endpoint
	 * and passes the stored access token on to our associated Client instance.
	 */
	protected function acceptTokenFromResponse($actionResponse)
	{
		$this->client->setAccessToken($actionResponse['access_token']);
	}

	/**
	 * Clean up session state after completing an auth flow.
	 */
	public function finalize()
	{
		$this->storage->clear();
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

	//--------------------------------------------------------------------------
	//	Debug layer
	//--------------------------------------------------------------------------

	protected $debug = false;	// ProcessLogger instance used for debugging

	public function enableDebug()
	{
		$this->debug = new ProcessLogger();
	}
}
