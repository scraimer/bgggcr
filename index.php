<?php

$user_is_authenticated = false;

if( $user_is_authenticated )
{
	include "index.authed.php";
}
else
{
	include "authenticate.php";
}

?>
