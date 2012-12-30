<?php
/**
 * Github flow definition
 *
 * @package	goauth
 * @author	Sam Pospischil <pospi@spadgos.com>
 * @since	30 Dec 2012
 */
class GithubAuthFlow extends GOAuthFlow
{
	const ENDPOINT_DIALOG = 'https://github.com/login/oauth/authorize';
	const ENDPOINT_TOKEN = 'https://github.com/login/oauth/access_token';

	/**
	 * Create a new auth flow.
	 *
	 * @param GOAuthClient	$client	gOAuth client instance for connecting to the service
	 * @param mixed  		$scope  single permission or array of permissions to request. @see http://developer.github.com/v3/oauth/#scopes
	 */
	public function __construct($client, $scope = null)
	{
		parent::__construct($client, $scope);

		$redirectParams = array(
			'uri' => self::ENDPOINT_DIALOG,
			'get' => array(
				'client_id'	=> $this->client->getId(),
				'state'		=> self::getNonce(),
				'redirect_uri' => Request::getFullURI(),
			),
		);
		if ($scope) {
			$redirectParams['get']['scope'] = is_array($scope) ? implode(',', $scope) : $scope;
		}

		$this['beginrequest'] = new GOAuthAction_RedirectStateful($this, $redirectParams);

		// -- :NOTE: flow takes the user to Facebook auth page, and back again --

		$this['checkstate'] = new GOAuthAction_CheckState($this);
		$this['getrequestcode'] = new GOAuthAction_ReadCode($this);
		$this['readservicetoken'] = new GOAuthAction_ExchangeCode($this, array(
			'uri' => self::ENDPOINT_TOKEN,
			'get' => array(
				'client_id' => $this->client->getId(),
				'client_secret' => $this->client->getSecret(),
				'redirect_uri' => $this->storage->getState('redirect_uri'),
				'state' => $this->storage->getState('state'),
			),
			'encoding' => GOAuthClient::ENC_FORM,
		));
	}

}
