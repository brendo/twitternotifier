<?php

	require_once('../lib/twitteroauth/twitteroauth/twitteroauth.php');
	session_start();

				$_SESSION['twitter_notifier'] = array(
					'consumer_key'			=> "fbfKLqdLxGRctPXLp22Wg",
					'consumer_secret'		=> "FpgHyNMCaWkowK1ZBSKZBvHRCvGrjBCyge3iGxYrqU",
					'oauth_callback'		=> "http://contentmonkey.com/extensions/twitternotifier/content/content.twitter_connect.php",
					'oauth_token'			=> "",
					'oauth_token_secret'	=> "",
					'oauth_verifier'		=> "",
					'twitter_user_id'		=> "",
					'twitter_screen_name'	=> ""
				);
	
	/**
	 * Initialise the callback to Twitter
	 * First check that the session is correctly initiated by the Symphony content page
	 * If so, create the oAuth instance
	 *
	 */
	if(isset($_SESSION['twitter_notifier']) || !empty($_SESSION['twitter_notifier']))
	{
		// Shorten the session variable
		$session = $_SESSION['twitter_notifier'];
		
		echo("original session:\n");
		var_dump($session);
		
		// If there's no oAuth token, we need to aquire one
		if($session['oauth_token'] == "" || empty($session['oauth_token']))
		{
			// Create the TwitterOAuth instance
			$TwitterOAuth = new TwitterOAuth($session['consumer_key'], $session['consumer_secret']);
			
			// Get the request tokens for user authorisation
			$request_token = $TwitterOAuth->getRequestToken($session['oauth_callback']);
			$session['oauth_token'] = $request_token['oauth_token'];
			$session['oauth_token_secret'] = $request_token['oauth_token_secret'];
			
			// Send the user to authenticate
			if($TwitterOAuth->http_code == 200){
				// Let's generate the URL and redirect
				$url = $TwitterOAuth->getAuthorizeURL($request_token['oauth_token']);
				header('Location: '. $url);
			}
		}
		
		echo("authorised session:\n");
		var_dump($session);
		
		// If Twitter has responded and supplied the GET params
		if($_GET && $_GET['oauth_verifier'] && ($_GET['oauth_token'] && $_GET['oauth_token'] = $session['oauth_token']))
		{
			$session['oauth_verifier'] = $_GET['oauth_verifier'];
			
			// TwitterOAuth instance, with two new parameters we got in twitter_login.php
			$TwitterOAuth = new TwitterOAuth($session['consumer_key'], $session['consumer_secret'], $session['oauth_token'], $session['oauth_token_secret']);
			
			// Get the access tokens for the access
			$access_token = $TwitterOAuth->getAccessToken($session['oauth_verifier']);
			$session['oauth_token'] = $access_token['oauth_token'];
			$session['oauth_token_secret'] = $access_token['oauth_token_secret'];
			$session['twitter_user_id'] = $access_token['user_id'];
			$session['twitter_screen_name'] = $access_token['screen_name'];
			$session['authorised'] = true;
			
			// Let's get the user's info
			$user_info = $TwitterOAuth->get('account/verify_credentials');
		}

		// Print user's info
		echo("final session and info:\n");
		var_dump($session);
		echo("\n");
		print_r($user_info);

		// Re-apply the session variable
		$_SESSION['twitter_notifier'] = $session;
		
	}

?>
<html>
	<head>
		<title>Connect</title>
	</head>
	<body>
		<script>
			//window.close();
		</script>
	</body>
</html>