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

	//--------------------------------------------------------------------------
	//	Initialisation

	/**
	 * Loads an appropriate client instance.
	 *
	 * @param  string $clientId			Client ID from the OAuth provider
	 * @param  string $clientSecret		Client secret from the OAuth provider
	 * @param  string $responseEncoding	expected response encoding from the service API
	 * @param  string $requestEncoding	default encoding for requests to the API
	 * @param  int    $ver				OAuth API version (1 or 2)
	 *
	 * @return GOAuthClient
	 */
	public static function getClient($clientId, $clientSecret, $responseEncoding = 'json', $requestEncoding = 'form', $ver = 2)
	{
		switch ($ver) {
			case 1:
				require_once(dirname(__FILE__) . '/client_v1.class.php');
				return new GOAuthClient_v1($clientId, $clientSecret, $responseEncoding, $requestEncoding);
			case 2:
				require_once(dirname(__FILE__) . '/client_v2.class.php');
				return new GOAuthClient_v2($clientId, $clientSecret, $responseEncoding, $requestEncoding);
		}
	}

	public function __construct($clientId, $clientSecret, $responseEncoding = 'json', $requestEncoding = 'form')
	{
		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;

		$this->setResponseEncoding($responseEncoding);
		$this->setRequestEncoding($requestEncoding);

		if (GOAuthFlow::DEBUG_ALL) {
			$this->enableDebug();
		}
	}

	//--------------------------------------------------------------------------
	//	Member vars

	protected $clientId;
	protected $clientSecret;
	protected $accessToken = null;

	protected $encoding = 'json';
	protected $requestEncoding = 'form';

	public $responseHeaders;	// headers from the last API request made
	public $rawResponse;

	//--------------------------------------------------------------------------
	//	Authentication

	public function getId()
	{
		return $this->clientId;
	}

	public function getSecret()
	{
		return $this->clientSecret;
	}

	public function setAccessToken($token)
	{
		$this->accessToken = $token;
	}

	public function getAccessToken()
	{
		return $this->accessToken;
	}

	public function isAuthed()
	{
		return $this->getAccessToken() !== null;
	}

	//--------------------------------------------------------------------------
	//	Requests

	/**
	 * Inject user-agent header and any other core data before sending underlying request
	 */
	final public function send($uri, $getParams = array(), $postParams = array(), $headers = null)
	{
		if (!$headers) {
			$headers = new Headers();
		}
		$headers['User-Agent'] = self::USER_AGENT;

		// filter input
		if (!is_array($getParams)) {
			$getParams = $this->parseInput($getParams, $this->requestEncoding);
		}
		if (!is_array($postParams)) {
			$postParams = $this->parseInput($postParams, $this->requestEncoding);
		}

		// inject access token before sending request
		if ($this->isAuthed()) {
			$this->passAccessToken($getParams, $postParams, $headers);
		}

		// log the request
		if ($this->debug) {
			$this->debug[] = 'Requesting: ' . $uri;
		}

		// perform request and decode the response
		$response = $this->realSend($uri, $getParams, $postParams, $headers);
		$result = $this->decode($response);

		// log request errors
		if ($this->debug && !$this->responseHeaders->ok()) {
			$this->debug[] = "Bad response from {$uri}: HTTP " . $this->responseHeaders->getStatusCode();
		}

		return $result;
	}

	final public function get($uri, $params = array())
	{
		return $this->send($uri, $params);
	}

	final public function post($uri, $params = array())
	{
		return $this->send($uri, array(), $params);
	}

	private function parseInput($input, $encoding)
	{
		if ($input === null) {
			return array();
		}
		switch ($encoding) {
			case self::ENC_JSON:
				return json_decode($input, true);
			case self::ENC_XML:
				// :TODO:
				if ($this->debug) $this->debug[] = 'XML implementation not yet completed';
				return null;
			default:
				$paramArray = array();
				parse_str($input, $paramArray);
				return $paramArray;
		}
	}

	/**
	 * Performs a request with a remote service's OAuth endpoint. Child classes must implement this method.
	 *
	 * @param  string 	$uri        URI to request
	 * @param  array  	$getParams  GET parameters to pass
	 * @param  array  	$postParams POST parameters to pass
	 * @param  Headers  $headers	any additional headers to pass
	 *
	 * @return the raw response from the request, as a string
	 */
	protected function realSend($uri, $getParams = array(), $postParams = null, $headers = null) {}

	/**
	 * Automatically pass the stored access token on with all requests made. This method is only
	 * called if the Client has been authenticated previously.
	 *
	 * Simply append the desired parameter to the desired parameterset for passing with requests!
	 */
	protected function passAccessToken(Array &$getParams, Array &$postParams, Headers &$headers)
	{
		$getParams['access_token'] = $this->getAccessToken();
	}

	//--------------------------------------------------------------------------
	//	Encoding

	public function setResponseEncoding($enc)
	{
		$this->encoding = $enc;
	}

	public function getResponseEncoding()
	{
		return $this->encoding;
	}

	public function setRequestEncoding($enc)
	{
		$this->requestEncoding = $enc;
	}

	public function getRequestEncoding()
	{
		return $this->requestEncoding;
	}

	/**
	 * Encodes some variables for sending in the request.
	 * The method of their encoding is determined by $this->requestEncoding by default.
	 * @param  array	$vars	variables for encoding
	 * @return string
	 */
	protected function encode($input, $encoding = null)
	{
		if (!$input) {
			return '';
		}
		if ($encoding === null) {
			$encoding = $this->requestEncoding;
		}
		switch ($encoding) {
			case self::ENC_JSON:
				return json_encode($input);
			case self::ENC_XML:
				// :TODO:
				if ($this->debug) $this->debug[] = 'XML implementation not yet completed';
				return null;
			default:
				return http_build_query($input);
		}
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

	//--------------------------------------------------------------------------
	//	Debug

	protected $debug = false;	// ProcessLogger instance used for debugging

	public function enableDebug()
	{
		$this->debug = new ProcessLogger();
	}
}
