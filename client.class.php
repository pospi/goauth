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
	const ENC_JSON = 'json';
	const ENC_XML = 'xml';
	const ENC_FORM = 'form';

	const USER_AGENT = 'gOAuth (https://github.com/pospi/goauth)';

	/**
	 * Loads an appropriate client instance.
	 *
	 * @param  string $clientId	Client ID from the OAuth provider
	 * @param  string $clientSecret	Client secret from the OAuth provider
	 * @param  int    $ver		OAuth API version (1 or 2)
	 * @param  string $encoding	expected response encoding from the service API
	 *
	 * @return GOAuthClient
	 */
	public static function getClient($clientId, $clientSecret, $ver = 2, $encoding = 'json')
	{
		switch ($ver) {
			case 1:
				require_once(dirname(__FILE__) . '/client_v1.class.php');
				return new GOAuthClient_v1($clientId, $clientSecret, $encoding);
			case 2:
				require_once(dirname(__FILE__) . '/client_v2.class.php');
				return new GOAuthClient_v2($clientId, $clientSecret, $encoding);
		}
	}

	public function __construct($clientId, $clientSecret, $encoding = 'json')
	{
		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;
		$this->setEncoding($encoding);
	}

	//--------------------------------------------------------------------------

	protected $endpointUrl;

	protected $clientId;
	protected $clientSecret;

	protected $encoding = 'json';
	protected $getParams = array();
	protected $postParams = null;

	public $responseHeaders;

	public function getId()
	{
		return $this->clientId;
	}

	public function getSecret()
	{
		return $this->clientSecret;
	}

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

		$response = $this->realSend($uri, $getParams, $postParams, $headers);
		$result = $this->decode($response);

		// log request errors
		if ($this->debug && !$this->responseHeaders->ok()) {
			$this->debug[] = "Bad response from {$uri}: HTTP " . $this->responseHeaders->getStatusCode();
		}

		return $result;
	}

	public function setEncoding($enc)
	{
		$this->encoding = $enc;
	}

	public function getEncoding()
	{
		return $this->encoding;
	}

	/**
	 * Decodes a raw API response body.
	 */
	private function decode($response)
	{
		$this->responseHeaders = new Headers();

		$body = $this->responseHeaders->parseDocument($response);

		$result = null;
		if ($body) {
			switch ($this->encoding) {
				case GOAuthClient::ENC_FORM:
					@parse_str($body, $result);
					break;
				case GOAuthClient::ENC_XML:
					if ($this->debug) {
						$this->debug[] = ":TODO: XML encoding not yet implemented";
					}
					break;
				default:
					$result = @json_decode($body, true);
					break;
			}
		}

		return $result ? $result : $this->responseHeaders->ok();
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
