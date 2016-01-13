<?php

require_once("db.inc.php");

db_connect();

$user = db_get_user_by_cookie( $_COOKIE['bggcookie'] );

if( $user )
{
	include "home.php";
}
else
{
	include "authenticate.php";
}

?>
