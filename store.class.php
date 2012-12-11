<?php
/**
 * Storage interface for handling of transient auth flow session data.
 *
 * @package	goauth
 * @author	Sam Pospischil <pospi@spadgos.com>
 * @since	9 Dec 2012
 */
interface IGOAuthStore
{
	public function setProgress($val);
	public function getProgress();

	public function setState($k, $v);
	public function getState($k = null);

	public function clear();
}

abstract class GOAuthStore
{
	// simple class loader
	public static function getStore($type)
	{
		$cls = "GOAuthStore_" . ucfirst(strtolower($type));
		if (!class_exists($cls)) {
			require_once(dirname(__FILE__) . '/store_' . strtolower($type) . '.class.php');
		}
		return new $cls;
	}
}
