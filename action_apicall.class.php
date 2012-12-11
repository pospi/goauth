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

		$client = GOAuthClient::getClient($this->params['ver']);
		if (isset($this->params['encoding'])) {
			$client->setEncoding($this->params['encoding']);
		}

		$result = $client->send($this->params['uri'], $get, $post, $headers);

		return $result ? $result : $client->responseHeaders->ok();
	}
}
