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
	public function setParams($params = array(), $fromPrevAction = false)
	{
		if ($fromPrevAction && !is_array($params)) {
			$params = array('get' => array('code' => $params));
		}
		return parent::setParams($params, $fromPrevAction);
	}
}
