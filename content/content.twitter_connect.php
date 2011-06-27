<?php
	session_start();

	require_once('../lib/twitteroauth/twitteroauth/twitteroauth.php');
	var_dump($_SESSION);
	/**
	 * Step 1:  Initialise the callback to Twitter
	 * First check that the session is correctly initiated by the Symphony content page
	 * Also check that the process hasn't already started
	 * If so, create the oAuth instance and authorise
	 */
	if((isset($_SESSION['twitter_notifier']) && !empty($_SESSION['twitter_notifier'])))
	{
		// Create the TwitterOAuth instance
		$TwitterOAuth = new TwitterOAuth(
			$_SESSION['twitter_notifier']['consumer_key'],
			$_SESSION['twitter_notifier']['consumer_secret']
		);
	var_dump("TwitterOAuth object created successfully\n");

		// Get the request tokens for user authorisation
		$request_token = $TwitterOAuth->getRequestToken($_SESSION['twitter_notifier']['oauth_callback']);

		$_SESSION['twitter_notifier']['oauth_token'] = $request_token['oauth_token'];
		$_SESSION['twitter_notifier']['oauth_token_secret'] = $request_token['oauth_token_secret'];
	var_dump("Tokens requested and ession set\n");

		// Send the user to authenticate
		if($TwitterOAuth->http_code == 200){
	var_dump("Status code 200, redirecting to authorise");
			// Let's generate the URL and redirect
			$url = $TwitterOAuth->getAuthorizeURL($_SESSION['twitter_notifier']['oauth_token']);
			header('Location: '. $url);
		}
	}

?>
