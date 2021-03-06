<?php

require_once("auth.inc.php");
require_once("db.inc.php");


if( !isset($_GET['code']) )
{
?>
	Account activation error.<br/>
	Please go to the <a href="<?=$config['http']['base_url']?>">homepage</a> and try again.
<?php
	exit();
}

db_connect();
$success = auth_activate_by_cookie( $_GET['code'] );
if( $success )
{
	$result = setcookie( "bggcookie", $_GET['code'] );
	if( $result === FALSE )
	{
		error_report("An error has occurred while setting the cookie.");
		exit();
	}
	header("Location: " . $config['http']['base_url']);
?>
	You've been authenticated and will be redirected to the
	<a href="<?=$config['http']['base_url']?>">homepage</a>.
<?php
}
else
{
?>
	Error activating your account. Please go to the 
	<a href="<?=$config['http']['base_url']?>">homepage</a> and try again.
<?php
}

?>
