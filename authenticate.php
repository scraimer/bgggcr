<?php
require_once("auth.inc.php");
db_connect();

if( is_user_authenticated() )
{
?>
	Hey, you're already authenticated!. Try reloading the <a href="<?=$config['http']['base_url']?>">homepage</a>.
<?php
	exit;
}

?>
<html>
	<head>
		<title>Log In | Geek Citizenship Recognition</title>
	</head>
<body>

<?php
if( !isset( $_POST["bggusername"] ) )
{
?>

<form action="authenticate.php" method="POST">
	To log in, type in your BGG username: <input type="text" name="bggusername" />
	<input type="submit" value="Log me in!" />
</form>

<?php
}
else
{
	$bggusername = $_POST["bggusername"];

	if( db_was_geekmail_sent_recently( $bggusername ) )
	{
?>
	A geekmail to the account '<tt><?=htmlentities($bggusername)?></tt>' has been sent recently, please check your geekmail.<br/>
	<a href="/">Oops, that's not my account! Let me type it in again!</a>
<?php
	}
	else
	{
		if( auth_queue_geekmail( $bggusername ) )
		{
?>
	Thank you! A geekmail will be sent to you on BGG with a link to authenticate your account.<br/>
	Please go to your geekmail to view it. Here's a couple of handy links to get you there more quickly:<br/>
	<ul>
		<li><a href="http://rpggeek.com/geekmail">Your geekmail on RPGgeek.com</a></li>
		<li><a href="http://boardgamegeek.com/geekmail">Your geekmail on BoardGameGeek.com</a></li>
	</ul>
<?php
		}
		else
		{
			// TODO: log the error and repot it to the BGG admin
?>
	There was an error sending the authentication geekmail to your account!
<?php
		}
	}
}
?>
</body>
</html>
