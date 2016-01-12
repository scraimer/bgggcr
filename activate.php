<?php

if( !isset($_GET['code']) )
{
?>
	Account activation error.<br/>
	Please go to the <a href="<?=$config['http']['base_url']?>">homepage</a> and try again.
<?php
	exit();
}

$success = auth_activate_by_cookie( $_GET['code'] );
if( $success )
{
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
