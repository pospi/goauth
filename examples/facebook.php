<?php
/**
 * Facebook login test
 *
 * @package	goauth
 * @author	Sam Pospischil <pospi@spadgos.com>
 * @since	9 Dec 2012
 */

define('YOUR_APP_CLIENT_ID',		'');
define('YOUR_APP_CLIENT_SECRET',	'');

require_once('../goauth.php');
require_once('../flows/facebook.php');

// create an unauthenticated OAuth client
$client = GOAuthClient::getClient(YOUR_APP_CLIENT_ID, YOUR_APP_CLIENT_SECRET);

// authenticate using an auth flow. Token can be retrieved for external storage etc
$flow = new FacebookAuthFlow($client);
$token = $flow->execute();
if ($flow->getLastAction() instanceof GOAuthAction_Redirect) {
	// redirected - stop. IMPORTANT: there must not be any output before this point!
	exit;
}
$flow->finalize();

// just some styling to make the debug info easily readable... ?>
<style type="text/css">
	body { white-space: pre; }
</style>
<?php

// Here is the response from the final token exchange request. 'access_token' is the token, 'expires' is the lifetime of the token, in seconds.
// You can store this token alongside your user in your client application to manage their connection with the remote service.
echo "<h4>Token successfuly requested:</h4><pre>";
print_r($token);
echo "</pre>";

if ($client->isAuthed()) {
	$resp = $client->send('https://graph.facebook.com/me');
	echo "<h4>Sample response from the API:</h4><pre>";
	print_r($resp);
	echo "</pre>";
} else {
	echo "<h4>Authentication failed.</h4>";
}
