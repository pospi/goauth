<?php
/**
 * Implements a call to a remote service's OAuth API as part of an OAuth flow.
 *
 * Parameters:
 * 	uri		- (required) URI of the service endpoint to request
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
	protected $params = array();

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

		$client = $this->getClient();
		$currEncoding = $client->getEncoding();
		if (isset($this->params['encoding'])) {	// :NOTE: allow this request to be encoded differently to all other client requests
			$client->setEncoding($this->params['encoding']);
		}

		if ($this->debug) {
			$this->debug[] = 'Processing API call @ ' . $this->params['uri'];
		}

		$result = $client->send($this->params['uri'], $get, $post, $headers);

		$client->setEncoding($currEncoding);

		return $result ? $result : $client->responseHeaders->ok();
	}
}
