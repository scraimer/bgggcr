<?php
require_once("db.inc.php");
require_once("error.inc.php");

function is_user_authenticated()
{
	if( !isset( $_COOKIE['BGGKEY'] ) )
	{
		return false;
	}
	if( !db_get_user_by_cookie( $_COOKIE['BGGKEY'] ) )
	{
		return false;
	}

	return true;
}

function auth_generate_cookie( $bggusername_tainted )
{
	return (time() % 1000000) . "-" . rand(100000, 999999);
}

function auth_queue_geekmail( $bggusername_tainted )
{
	# Find an available cookie
	$cookie = auth_generate_cookie( $bggusername_tainted );
	while( db_get_user_by_cookie( $cookie ) )
	{
		$cookie = auth_generate_cookie( $bggusername_tainted );
	}
	
	return db_add_auth_request( $bggusername_tainted, $cookie );
}

function auth_activate_by_cookie( $cookie )
{
	$result = db_get_auth_request_by_cookie( $cookie );
	if( $result === FALSE )
	{
		error_report("Error: No such cookie is known.");
		return FALSE;
	}

	$success = db_mark_user_as_authenicated( $result['username'], $result['cookie'], $result['id'] );
	if( $success === FALSE )
	{
		error_report("Error: Could not mark user as 'authenticated'. Aborting.");
		return FALSE;
	}

	return TRUE;
}

?>
