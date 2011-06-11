<?php
session_start();
?>
<html>
<head>
<title>Twitter OAuth via popup</title>
</head>
<body>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script src="../assets/jquery.oauthpopup.js"></script>
<script>
$(document).ready(function(){
    $('#connect').click(function(){
        $.oauthpopup({
            path: 'connect.php',
            callback: function(){
                window.location.reload();
            }
        });
    });
});
</script>
<div><?php
print_r($_SESSION);
?></div>
<input type="button" value="Connect with Twitter" id="connect" /><br />
<a href="signout.php">Sign Out</a>
</body>
</html>