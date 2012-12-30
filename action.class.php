<?php
/**
 * Generic implementation of an action performed as part of an OAuth flow.
 *
 * @package	goauth
 * @author	Sam Pospischil <pospi@spadgos.com>
 * @since	5 Dec 2012
 */
interface IGOAuthAction
{
	/**
	 * Processes the endpoint - runs the action it represents.
	 *
	 * In some cases this method may return a value for use in calling code or to pass to following
	 * actions, in others the behaviour is undefined. See class documentation for each subclass accordingly.
	 */
	public function process();
}

abstract class GOAuthAction
{
	protected $flow = null;			// link back to the Flow instance we're a part of
	protected $flowIndex;			// our key within the flow's array of actions
	protected $params = array();	// miscellaneous parameters for the action


	public function __construct($flow, $params = array())
	{
		$this->flow = $flow;
		$this->setParams($params);

		if (GOAuthFlow::DEBUG_ALL) {
			$this->enableDebug();
		}
	}

	public function setIndex($i)
	{
		$this->flowIndex = $i;
	}

	/**
	 * Determines whether this action is final (ie: the last in a chain before a browser redirect,
	 * final result or some other terminating action within a flow)
	 */
	public function isFinal()
	{
		return false;
	}

	/**
	 * Determines whether this action should be resumed after returning to the script.
	 * Redirects and so forth should return TRUE here, whereas failure cases and so on should return FALSE.
	 *
	 * :NOTE: this method is only called when isFinal() == true
	 */
	public function shouldResume()
	{
		return true;
	}

	// accessor for our associated client
	public function getClient()
	{
		return $this->flow->client;
	}

	//--------------------------------------------------------------------------
	// basic parameter storage
	//--------------------------------------------------------------------------

	/**
	 * Sets an array of parameters all at once. Existing params are not cleared first.
	 * @param	array	$params
	 * @param	bool	$fromPrevAction	if true, these parameters are being passed
	 *                             		from the previous action in the flow as it executes.
	 */
	public function setParams($params = array(), $fromPrevAction = false)
	{
		if (!is_array($params)) {
			return;
		}
		$this->params = array_merge_recursive($this->params, $params);
	}

	public function clearParams()
	{
		$this->params = array();
	}

	public function setParam($k, $v)
	{
		$this->params[$k] = $v;
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
