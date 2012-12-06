<?php
/**
 * Implements a call to a remote service's OAuth API as part of an OAuth flow.
 *
 * Parameters:
 * 	uri		- (required) URI of the service endpoint to request
 * 	ver		- OAuth version of the service (1 or 2)
 * 	get		- array of GET parameters to send
 * 	post	- array of POST parameters to send
 * 	header	- array of header lines, Headers object or header block as a string
 * 	encoding - encoding of the response determining how we should decode it (json, form or xml)
 *
 * @package	goauth
 * @author	Sam Pospischil <pospi@spadgos.com>
 * @since	5 Dec 2012
 */
class GOAuthAction_APICall extends GOAuthAction
{
	const ENC_JSON = 'json';
	const ENC_XML = 'xml';
	const ENC_FORM = 'form';

	protected $params = array('ver' => 2);

	/**
	 * Process the request action.
	 * The response from the request will be decoded and returned as an array.
	 *
	 * @return response from the API as an array
	 */
	public function process()
	{
		$get = isset($this->params['get']) ? $this->params['get'] : array();
		$post = isset($this->params['post']) ? $this->params['post'] : null;
		$headers = isset($this->params['header']) ? $this->params['header'] : null;

		$client = GOAuthClient::getClient($ver);

		return $this->getOutput($client->send($this->params['uri'], $get, $post, $headers));
	}

	/**
	 * Retrieves output parameters from the remote Endpoint, to pass to the action following this one.
	 *
	 * @return mixed	the decoded response from the remote API if there was one, or a boolean indicating
	 *                  the success of the request (based on HTTP status) otherwise.
	 */
	final public function getOutput($rawResponse = null)
	{
		$responseHeaders = new Headers();

		$body = $responseHeaders->parseDocument($rawResponse);

		$result = null;
		if ($body) {
			switch ($this->encoding) {
				case self::ENC_JSON:
					$result = @json_decode($body, true);
					break;
				case self::ENC_FORMDATA:
					@parse_str($body, $result);
					break;
				case self::ENC_XML:
					if ($this->debug) {
						$this->debug[] = ":TODO: XML encoding not yet implemented";
					}
					break;
			}
		}

		// handle API errors
		if ($this->debug) {
			$this->debug[] = "Bad response from {$this->endpointUrl}: HTTP " . $responseHeaders->getStatusCode();
		}

		return $result ? $result : $responseHeaders->ok();
	}
}
