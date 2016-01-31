<?php

require_once("award.inc.php");
require_once("bgg.inc.php");

db_connect();

$user = db_get_user_by_cookie( $_COOKIE['bggcookie'] );
if( !$user )
{
?>
	Error authenticating. Try logging in again.
<?php
	exit();
}

$recipient = bgg_untaint_username( trim($_POST['recipient']) );
if( strlen($recipient) <= 0 )
{
?>
	Error: recipient not specified. Go back and try again.
<?php
	exit();
}

# check if the BGG user is the same as the BGG user of the giver
if( strcasecmp($recipient, $user['username']) == 0 )
{
?>
	Error: You cannot award to yourself!
<?php
	exit();
}

$recipient_profile = bgg_get_profile( $recipient );
if( !$recipient_profile || !$recipient_profile['result'] )
{
	if( $recipient_profile['user_not_found'] )
	{
?>
		Error: Could not find user profile for <?=$recipient?>! Did you mis-spell it?
<?php
	}
	else
	{
?>
	Error while checking user profile!
<?php
	}
	exit();
}

if( $recipient_profile['stars'][4] )
{
?>
	The user <?=$recipient?> already has 5 stars!<br/>
   How about choosing someone else?
<?php
	exit();
}

$result = award_add_to_queue( $user['id'], $recipient );
if( $result )
{
?>
	The microbadge has been queued to be awarded. You will get notified when it is.
<?php
}
else
{
?>
	Error adding microbagde to the queue: Perhaps you already awarded a star this month?
<?php
	exit();
}

?>
