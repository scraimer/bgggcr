<?php

require_once("config.inc.php");
require_once("geekmail.inc.php");
require_once("db.inc.php");
require_once("error.inc.php");
require_once("bgg.inc.php");
require_once("award.inc.php");

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

function array_counter( $count, $item )
{
	if( $item !== FALSE )
	{
		++$count;
	}
	return $count;
}

function award_microbadges_and_send_confirmations()
{
	global $config;
	
	$how_many_to_send = $config['bgg']['max_award_geekmails_rate_per_hour'];

	# This is a little tricky. We don't need to send a geemail to the 
	# recipients, since that will get sent by the microbadge awarding in BGG. We 
	# will send a geekmail to the givers, but really only a single geekmail to 
	# many recipients. Howerver, because I don't want this script going crazy, 
	# I'll just use the configuration value to limit how many awards are given 
	# out.
	$reqs = db_get_award_requests( $how_many_to_send );

	if( count($reqs) == 0 )
	{
		print "No reqs\n";
		return TRUE;
	}

	$result = bgg_login();
	if( $result === FALSE )
	{
		error_report( "Error logging into BGG." );
		exit();
	}

	# I don't want to bulk-award the microbadges. It will make it hard to refund 
	# awards, or handle re-try. (For example, a user might accidentaly get a 
	# 4-star before getting a 3-star).
	# 
	# So we'll just award them to each recipient, one at a time.
	# 
	$givers = array();
	foreach( $reqs as $req )
	{
		echo "REQ: recipient=" . $req['recipient'] . " id=" . $req['id'] . "\n";
		$user = bgg_get_profile( $req['recipient'] );
		if( $user === FALSE || $user['result'] === FALSE )
		{
			error_report("Error fetching profile of '" . $req['recipient'] . "' while awarding a microbage (req ID " . $req['id'] . ")");
			continue;
		}

		# Does the recipient already have 5 stars?
		$has_stars = array_reduce( $user['stars'], "array_counter", 0 );
		if( $has_stars >= 5 )
		{
			# Refund the current request
			error_report("Warning: The user \"" . $req['recipient'] . 
				"\" already has five stars, so the request with ID " . $req['id'] . 
				" will be refunded to " . $req['giver_username']);
			
			$result = award_refund( $req['id'] );
			if( $result === FALSE )
			{
				error_report("Warning: The award could not be refunded. It will be " .
					"checked again later. Unless these are eventually refunded or " .
					"deleted, they will accumulate and clog up the award queue.");
			}
			continue;
		}

		$mb_id = $bgg_mb_stars[ $has_stars ];

		# Award the microbadge
		$result = bgg_award_microbadge_bulk( 
			$mb_id, $req['recipient'], $config['bgg']['mb_award_msg_template'] );
		if( $result === FALSE || $result['result'] === FALSE )
		{
			error_report("Error while awarding microbadge for award ID " . $req['id']);
			continue;
		}

		# Mark as awarded
		db_award_set_awarded( $req['id'] );
		
		# Add the giver to the list of givers who will recieve confirmation of award.
		# Both for later geekmailing.
		array_push( $givers, $req['giver_username'] );

		# Sleep for a bit, to give the BGG database time to update the user's profile
		usleep(300 * 1000); 
		
		# Verify it was received, in the recpient profile
		$user = bgg_get_profile( $req['recipient'] );
		if( $user === FALSE || $user['result'] === FALSE )
		{
			error_report("Error verifying whether award ID " . $req['id'] . " was awarded.");
		}
		else
		{
			if( $user['stars'][$has_stars - 1] )
			{
				# Mark as verified
				if( !db_award_set_verified( $req['id'] ) )
				{
					error_report("Error marking award ID " . $req['id'] . " as verified.");
				}
			}
			else
			{
				error_report("Warning: The star hasn't appeared yet, " . 
					"(request ID=$id, star=$has_stars) so it will not be marked as verified.");
			}
		}
	}

	# Send a mass-mail to all the givers, notifying them the microbadge has been awarded
	$to_givers = implode( ',', $givers );
	geekmail_send( $to_givers, $config['bgg']['giver_confirmation_msg_subject'], 
		$config['bgg']['giver_confirmation_msg_body'] );

	return TRUE;
}

db_connect();
send_geekmails_for_auth_requests();
award_microbadges_and_send_confirmations();

?>
