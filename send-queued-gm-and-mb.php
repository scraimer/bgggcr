<?php

require_once("geekmail.inc.php");
require_once("db.inc.php");
require_once("error.inc.php");
require_once("bgg.inc.php");

function send_geekmails_for_auth_requests()
{
	global $config;
	
	$how_many_to_send = $config['bgg']['max_auth_geekmails_rate_per_hour'];

	$reqs = db_get_auth_requests_to_send( $how_many_to_send );

	if( count($reqs) == 0 )
	{
		return TRUE;
	}

	$result = bgg_login();
	if( $result === FALSE )
	{
		error_report( "Error logging into BGG." );
		exit();
	}

	foreach( $reqs as $req )
	{
		$auth_url = $config['http']['base_url'] . "activate.php?code=" . urlencode($req['cookie']);
		$msg = sprintf(
			$config['bgg']['auth_msg_template'],
			$req['username'],
			$auth_url,
			$config['bgg']['base_url'],
			$config['bgg']['username']
		);

		$success = geekmail_send( $req['username'], $config['bgg']['auth_msg_subject'], $msg );
		if( $success )
		{
			$result = db_mark_auth_request_as_sent( $req['id'] );
			if( !$result )
			{
				error_report( "Error marking auth request ID " . $req['id'] . " as sent" );
				exit;
			}
		}
		else
		{
			error_report( "Error sending geekmail to '" . $req['username'] .
				"' in request ID=" . $req['id'] );
			exit;
		}
	}
}

function award_microbadges_and_send_confirmations()
{
}

db_connect();
send_geekmails_for_auth_requests();
award_microbadges_and_send_confirmations();

?>
