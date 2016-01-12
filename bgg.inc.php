<?php

require_once('config.inc.php');
require_once('error.inc.php');
require_once('http.inc.php');

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
