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
	//--------------------------------------------------------------------------
	// basic parameter storage
	//--------------------------------------------------------------------------

	protected $params = array();	// miscellaneous parameters for the action

	/**
	 * Sets an array of parameters all at once. Existing params are not cleared first.
	 * @param	array	$params
	 */
	public function setParams($params)
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

	private $debug = false;	// ProcessLogger instance used for debugging

	public function enableDebug()
	{
		pwebframework::loadClass('processlogger');

		$this->debug = new ProcessLogger();
	}
}
