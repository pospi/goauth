<?php
/**
 * Low-level client class responsible for performing and encoding requests
 *
 * @package	goauth
 * @author	Sam Pospischil <pospi@spadgos.com>
 * @since	5 Dec 2012
 */
abstract class GOAuthClient
{
	const USER_AGENT = 'gOAuth (https://github.com/pospi/goauth)';

	/**
	 * Loads an appropriate client instance.
	 * @param  int $ver OAuth API version (1 or 2)
	 * @return string GOAuthClient instance name to use for calling methods
	 */
	public static function getClient($ver = 2)
	{
		switch ($ver) {
			case 1:
				require_once(dirname(__FILE__) . '/client_v1.class.php');
				return new GOAuthClient_v1();
			case 2:
				require_once(dirname(__FILE__) . '/client_v2.class.php');
				return new GOAuthClient_v2();
		}
	}

	//--------------------------------------------------------------------------

	protected $endpointUrl;
	protected $getParams = array();
	protected $postParams = null;

	/**
	 * Inject user-agent header and any other core data before sending underlying request
	 */
	final public function send($uri, $getParams = array(), $postParams = null, $headers = null)
	{
		if (!$headers) {
			$headers = new Headers();
		}
		$headers['User-Agent'] = self::USER_AGENT;

		if ($this->debug) {
			$this->debug[] = 'Requesting: ' . $uri;
		}

		return $this->realSend($uri, $getParams, $postParams, $headers);
	}

	/**
	 * Performs a request with a remote service's OAuth endpoint
	 *
	 * @param  string 	$uri        URI to request
	 * @param  array  	$getParams  GET parameters to pass
	 * @param  array  	$postParams POST parameters to pass
	 * @param  Headers  $headers	any additional headers to pass
	 *
	 * @return the raw response from the request, as a string
	 */
	protected function realSend($uri, $getParams = array(), $postParams = null, $headers = null) {}

	//--------------------------------------------------------------------------

	protected $debug = false;	// ProcessLogger instance used for debugging

	public function enableDebug()
	{
		$this->debug = new ProcessLogger();
	}
}
