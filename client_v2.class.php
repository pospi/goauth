<?php
/**
 * OAuth 2 client implementation
 *
 * @package	goauth
 * @author	Sam Pospischil <pospi@spadgos.com>
 * @since	5 Dec 2012
 */
class GOAuthClient_v2 extends GoAuthClient
{
	protected function realSend($uri, $getParams = array(), $postParams = null, $headers = null)
	{
		$request = HTTPProxy::getProxy($uri . ($getParams ? '?' . Request::getQueryString($getParams) : ''));
		$request->followRedirects(true);

		if ($postParams) {
			return $request->post($this->encode($postParams), $headers);
		} else {
			return $request->get($headers);
		}
	}
}
