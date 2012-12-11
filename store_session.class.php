<?php
/**
 * Storage for session data using builtin PHP sessions.
 *
 * @package	goauth
 * @author	Sam Pospischil <pospi@spadgos.com>
 * @since	9 Dec 2012
 */
class GOAuthStore_Session extends GOAuthStore
{
	const SESSION_PROGRESS_KEY	= 'goauth_progress';
	const SESSION_STATE_KEY		= 'goauth_state';

	public function __construct()
	{
		self::ensureSession();
	}

	public function setProgress($val)
	{
		$_SESSION[self::SESSION_PROGRESS_KEY] = $val;
	}

	public function getProgress()
	{
		return isset($_SESSION[self::SESSION_PROGRESS_KEY]) ? $_SESSION[self::SESSION_PROGRESS_KEY] : null;
	}

	public function setState($k, $v)
	{
		if (!isset($_SESSION[self::SESSION_STATE_KEY])) {
			$_SESSION[self::SESSION_STATE_KEY] = array();
		}
		$_SESSION[self::SESSION_STATE_KEY][$k] = $v;
	}

	public function getState($k = null)
	{
		if ($k === null) {
			return isset($_SESSION[self::SESSION_STATE_KEY]) ? $_SESSION[self::SESSION_STATE_KEY] : null;
		}
		return isset($_SESSION[self::SESSION_STATE_KEY][$k]) ? $_SESSION[self::SESSION_STATE_KEY][$k] : null;
	}

	public function clear()
	{
		unset($_SESSION[self::SESSION_PROGRESS_KEY]);
		unset($_SESSION[self::SESSION_STATE_KEY]);
	}

	public static function ensureSession()
	{
		if (!session_id()) {
			new Session('goauth');
		}
	}
}
