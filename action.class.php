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
	protected $params = array();	// miscellaneous parameters for the action

	public function __construct($flow, $params = array())
	{
		$this->flow = $flow;
		$this->setParams($params);
	}

	/**
	 * Determines whether this action is final (ie: the last in a chain before a browser redirect,
	 * final result or some other terminating action within a flow)
	 */
	public function isFinal()
	{
		return false;
	}

	//--------------------------------------------------------------------------
	// basic parameter storage
	//--------------------------------------------------------------------------

	/**
	 * Sets an array of parameters all at once. Existing params are not cleared first.
	 * @param	array	$params
	 */
	public function setParams($params = array())
	{
		if (!is_array($params)) {
			return;
		}
		$this->params = array_merge($this->params, $params);
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
