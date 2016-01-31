<?php

require_once("award.inc.php");

$user = db_get_user_by_cookie( $_COOKIE['bggcookie'] );
if( !$user )
{
?>
	Error authenticating. Try logging in again.
<?php
	exit();
}



?>
