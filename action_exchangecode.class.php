<?php
/**
 * Special case API call action used to exchange the request code
 * for a final service token.
 *
 * The code to send for the request should come from the return value of the previous action.
 *
 * @package	goauth
 * @author	Sam Pospischil <pospi@spadgos.com>
 * @since	9 Dec 2012
 */
class GOAuthAction_ExchangeCode extends GOAuthAction_APICall
{
	private $code = null;

	public function setParams($params = array(), $fromPrevAction = false)
	{
		if ($fromPrevAction) {
			$this->code = $params;
			return;
		}
		return parent::setParams($params, $fromPrevAction);
	}

	protected function getGetParams()
	{
		$params = parent::getGetParams();
		$params['code'] = $this->code;
		return $params;
	}
}
