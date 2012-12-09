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
	const DEBUG_ALL = true;

	public static $STORAGE_TYPE = 'Session';

	public $storage;		// GOAuthStore instance used to handle session persistence between flow requests

	protected $actions = array();
	protected $lastProcessedAction = null;

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
		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;

		$this->storage = GOAuthStore::getStore(self::$STORAGE_TYPE);

		if (self::DEBUG_ALL) {
			$this->enableDebug();
		}
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
		return $this->storage->getProgress();
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

		foreach ($this->actions as $i => $action) {
			if ($startAt !== null && !$started) {
				if ($i == $startAt) {
					$started = true;
				}
				continue;
			}

			$action->setParams($params);		// pass parameters to first action
			$params = $action->process();		// use the return value as input to the next action in the flow

			$processed = true;
			$this->lastProcessedAction = $i;


			// if this was a terminal action, jump out
			if ($action->isFinal()) {

				// store the progress we're at in the flow in a session so we can pick it up easily later
				if ($action->shouldResume()) {
					$this->storage->setProgress($i);

				}

				return $params;
			}
		}
		return $processed ? $params : null;		// return the result from the terminal action for further processing, or FALSE if nothing happened
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

	private $debug = false;	// ProcessLogger instance used for debugging

	public function enableDebug()
	{
		$this->debug = new ProcessLogger();
	}
}
