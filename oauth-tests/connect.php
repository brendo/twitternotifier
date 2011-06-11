<?php
define('TWITTER_CONSUMER_KEY',      '***');
define('TWITTER_CONSUMER_SECRET',   '***');
define('TWITTER_REQUEST_URL',       'https://api.twitter.com/oauth/request_token');
define('TWITTER_ACCESS_URL',        'https://api.twitter.com/oauth/access_token');
define('TWITTER_AUTHORIZE_URL',     'https://api.twitter.com/oauth/authorize');
 
session_start();
if (!isset($_SESSION['twitter'])) {
    setOAuth();
    $_SESSION['twitter'] = getUserInfo($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
}
 
function setOAuth()
{
    //  pecl_oauth
    $oauth = new OAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_FORM);
    $oauth->enableDebug();
    try {
        if (isset($_GET['oauth_token'], $_SESSION['oauth_token_secret'])) {
            $oauth->setToken($_GET['oauth_token'], $_SESSION['oauth_token_secret']);
            $accessToken = $oauth->getAccessToken(TWITTER_ACCESS_URL);
            $_SESSION['oauth_token'] = $accessToken['oauth_token'];
            $_SESSION['oauth_token_secret'] = $accessToken['oauth_token_secret'];
 
            $response = $oauth->getLastResponse();
            parse_str($response, $get);
            if (!isset($get['user_id'])) {
                throw new Exception('Authentication failed.');
            }
        } else {
            $requestToken = $oauth->getRequestToken(TWITTER_REQUEST_URL);
            $_SESSION['oauth_token_secret'] = $requestToken['oauth_token_secret'];
            header('Location: ' . TWITTER_AUTHORIZE_URL . '?oauth_token=' . $requestToken['oauth_token']);
            die();
        }
    } catch (Exception $e) {
        var_dump($oauth->debugInfo);
        die($e->getMessage());
    }
}
 
function getUserInfo($token, $secret)
{
    $oauth = new OAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
    $oauth->setToken($token, $secret);
    $oauth->fetch('http://twitter.com/account/verify_credentials.json');
    $buf = $oauth->getLastResponse();
    return json_decode($buf, true);
}
 
?>
<html>
<head>
<title>Connect</title>
</head>
<body>
<script>
window.close();
</script>
</body>
</html>