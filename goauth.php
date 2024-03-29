<?php
/**
 * gOAuth - Generic OAuth
 *
 * gOAuth is a generic implementation of an OAuth client which can readily be extended
 * to handle newly emerging services with minimal coding.
 *
 * @package	goauth
 * @depends	pWebFramework - https://github.com/pospi/pwebframework
 * @author	Sam Pospischil <pospi@spadgos.com>
 * @since	5 Dec 2012
 */

// load dependent code only if not already inited by some other framework
if (!class_exists('pwebframework')) {
	require_once(dirname(__FILE__) . '/pwebframework/pwebframework.inc.php');
}
pwebframework::loadClass('HTTPProxy');
pwebframework::loadClass('Response');
pwebframework::loadClass('Request');
pwebframework::loadClass('Headers');
pwebframework::loadClass('Session');
pwebframework::loadClass('ProcessLogger');

require_once(dirname(__FILE__) . '/store.class.php');
require_once(dirname(__FILE__) . '/client.class.php');
require_once(dirname(__FILE__) . '/action.class.php');
require_once(dirname(__FILE__) . '/action_redirect.class.php');
require_once(dirname(__FILE__) . '/action_redirect_stateful.class.php');
require_once(dirname(__FILE__) . '/action_check_state.class.php');
require_once(dirname(__FILE__) . '/action_read_code.class.php');
require_once(dirname(__FILE__) . '/action_apicall.class.php');
require_once(dirname(__FILE__) . '/action_exchangecode.class.php');
require_once(dirname(__FILE__) . '/flow.class.php');
