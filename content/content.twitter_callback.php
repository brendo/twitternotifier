<?php

	require_once('../lib/twitteroauth/twitteroauth/twitteroauth.php');
	session_start();

	// If Twitter has responded and supplied the GET params
	if((isset($_GET['oauth_token']) && isset($_GET['oauth_verifier'])) && ($_GET['oauth_token'] !== $_SESSION['twitter_notifier']['oauth_token']))
	{
		$_SESSION['twitter_response']['oauth_verifier'] = $_GET['oauth_verifier'];

		//var_dump($_SESSION['twitter_notifier']);

		// TwitterOAuth instance, with two new parameters we have
		$TwitterOAuth = new TwitterOAuth(
			$_SESSION['twitter_notifier']['consumer_key'],
			$_SESSION['twitter_notifier']['consumer_secret'],
			$_SESSION['twitter_notifier']['oauth_token'],
			$_SESSION['twitter_notifier']['oauth_token_secret']
		);

		// Get the access tokens for the access
		$access_token = $TwitterOAuth->getAccessToken($_SESSION['twitter_response']['oauth_verifier']);

		unset($_SESSION['twitter_notifier']['oauth_token'], $_SESSION['twitter_notifier']['oauth_token_secret']);

		$_SESSION['twitter_response']['access_token'] = $access_token['oauth_token'];
		$_SESSION['twitter_response']['access_token_secret'] = $access_token['oauth_token_secret'];
		$_SESSION['twitter_response']['twitter_user_id'] = $access_token['user_id'];
		$_SESSION['twitter_response']['twitter_screen_name'] = $access_token['screen_name'];

		if($TwitterOAuth->http_code == 200){
			$_SESSION['twitter_response']['authorised'] = true;
		}
	}
/*
	// Print user's info
	echo("final session and info:\n");
	var_dump($_SESSION);
	echo("\n");
	var_dump($user_info);
*/
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