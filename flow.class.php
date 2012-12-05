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
class GOAuthFlow implements ArrayAccess, Iterable
{
	private $actions = array();

	public function execute($params = array())
	{
		foreach ($this->actions as $action) {
			$action->setParams($params);		// pass parameters to first action
			$params = $action->process();		// use the return value as input to the next action in the flow
		}
		return $params;		// return the result from the terminal action for further processing
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
}
