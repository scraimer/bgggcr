<?php

require_once('config.inc.php');
require_once('error.inc.php');
require_once('http.inc.php');

# The microbadge numbers for stars I,II,III,IV,V
$bgg_mb_stars = array( 36371, 36372, 36373, 36374, 36375 );

function bgg_untaint_username( $username_tainted )
{
	return preg_replace( '/[^0-9 a-z\._]/i', '', $username_tainted );
}

function bgg_get_cookies_from_headers( $headers )
{
	$cookie_prefix = "Set-Cookie:";
	$prefix_length = strlen($cookie_prefix);
		
	$cookies = array();
	foreach( $headers as $h )
	{
		if( substr( $h, 0, $prefix_length ) == $cookie_prefix )
		{
			$semi_colon_pos = strpos( $h, ";" );
			if( $semi_colon_pos !== FALSE )
			{
				$c = substr( $h, $prefix_length, $semi_colon_pos - $prefix_length );
				$c = trim( $c );
				$a = explode( "=", $c );
				$cookies[$a[0]] = $a[1];
			}
		}
	}
	
	return $cookies;
}

function bgg_login()
{
	global $config;

	if( $config['bgg']['cookie'] !== FALSE )
	{
		error_report("Already logged in! Not logging in again.");
		return TRUE;
	}

	$url = "https://" . $config['bgg']['domain'] . "/login";
	$params = array(
		'lasturl' => '/',
		'username' => $config['bgg']['username'],
		'password' => $config['bgg']['password'],
	);
	
	$result = http_post( $url, $params );
	if( $result === FALSE )
	{
		return FALSE;
	}

	$cookies = bgg_get_cookies_from_headers( $result['headers'] );

	if( !isset( $cookies['bggusername'] ) ||
		!isset( $cookies['bggpassword'] ) ||
		!isset( $cookies['SessionID'] ) )
	{
		error_log("Missing cookies after BGG logon. Logon error!");
		return FALSE;
	}

	$bgg_cookies = sprintf("bggusername=%s; bggpassword=%s; SessionID=%s",
		$cookies['bggusername'], $cookies['bggpassword'], $cookies['SessionID'] );

	$config['bgg']['cookie'] = $bgg_cookies;

	return TRUE;
}

# We don't care about the entire profile, just about two things:
# 
# 1. Does the user exist?
# 
# 2. Which 'star' microbadges does the user have?
#
function bgg_get_profile( $username_tainted )
{
	global $config;

	$username = bgg_untaint_username( $username_tainted );
	$url = 'https://' . $config['bgg']['domain'] . '/user/' . rawurlencode( $username );
	$data = http_get_simple($url);

	# If it's not a 200 response, it's an error
	if( strcmp( $data['headers'][0], "HTTP/1.1 200 OK" ) != 0 )
	{
		return array(
			'error' => 'Unable to fetch user profile.',
			'result' => FALSE
		);
	}

	$content = $data['content'];
	if( strpos( $content, "Error: User does not exist." ) !== FALSE )
	{
		return array(
			'user_not_found' => 1,
			'result' => FALSE
		);
	}

	$mb_start_off = strpos( $content, 'Microbadges for ' );

	global $bgg_mb_stars;
	$found = array( );

	for( $i=0; $i < count( $bgg_mb_stars ); ++$i )
	{
		$found[$i] = 
			( strpos( $content, '/microbadge/' . $bgg_mb_stars[$i], $mb_start_off ) !== FALSE );
	}

	return array(
		'stars' => $found,
		'result' => TRUE
	);
}

/**
 * $users - a newline-separated list of users to award the microbadge to
 */
function bgg_award_microbadge_bulk( $microbadge_id, $users, $msg )
{
	global $config;
	
	$url = 'https://' . $config['bgg']['domain'] . '/geekmicrobadge.php';
	$params = array(
		'action' => 'savebulkgive',
		'badgeid' => $microbadge_id,
		'users' => $users,
		'message' => $msg,
		'B1' => 'Submit'
	);
	$data = http_post($url, $params, $config['bgg']['cookie']);
	if( $data === FALSE )
	{
		return array(
			'error' => 'Error POSTing request to award microbadge',
			'result' => FALSE
		);
	}

	# If it's not a 200 response, it's an error
	if( strcmp( $data['headers'][0], "HTTP/1.1 200 OK" ) != 0 )
	{
		return array(
			'error' => 'Unable to award microbadge, non-OK HTTP response',
			'result' => FALSE
		);
	}

	# Errors:
	# 1. If the 'admin' user isn't allowed to give microbadges, we'll get an error:
	#     giftmgr access required
	# 2. Actually, all errors appear inside a div:
	#     <div class='messagebox error'>
	#

	$content = $data['content'];
	if( strpos( $content, "Error: User does not exist." ) !== FALSE )
	{
		return array(
			'user_not_found' => 1,
			'result' => FALSE
		);
	}
	elseif( strpos( $content, "<div class='messagebox error'>" ) !== FALSE )
	{
		return array(
			'other_error' => 1,
			'result' => FALSE
		);
	}

	return array(
		'result' => TRUE
	);
}
