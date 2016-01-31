<?php

require_once("db.inc.php");
require_once("geekmail.inc.php");

function award_can_user_give( $user_id )
{
	return db_can_user_give( $user_id );
}

function award_get_last( $user_id )
{
	return db_award_get_last( $user_id );
}

function award_add_to_queue( $giver_user_id, $recipient )
{
	return db_award_add_to_queue( $giver_user_id, $recipient );
}

function award_refund( $req_id )
{
	$req = db_award_get( $req_id );
	if( $req === FALSE )
	{
		return FALSE;
	}

	$result = db_award_delete( $req_id );
	if( $result === FALSE )
	{
		return FALSE;
	}

	$result == geekmail_send( $req['giver_username'], 
		'Microbadge Refund',
		'The user ' . $req['recipient'] .
			' already has five stars. How about awarding a star to someone else?' );
	if( $result === FALSE )
	{
		return FALSE;
	}

	return TRUE;
}

?>
