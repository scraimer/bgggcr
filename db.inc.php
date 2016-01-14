<?php

require_once('config.inc.php');

$dbh = NULL;

function db_connect()
{
	global $dbh;
	global $config;

	$dsn = 'mysql:host=' . $config['db']['hostname'] . ';' .
		'dbname=' . $config['db']['dbname'];
	$dbh = new PDO( $dsn, $config['db']['username'], $config['db']['password'] );

	return $dbh;
}

function db_get_user_by_cookie( $cookie )
{
	global $dbh;
	
	$sth = $dbh->prepare('
		SELECT username
			FROM `users`
			WHERE cookie=:cookie
		');
	$sth->bindParam('cookie', $cookie, PDO::PARAM_STR);
	$sth->execute();

	$items = array();
	if( $row = $sth->fetch(PDO::FETCH_ASSOC) )
	{
		if ($row === FALSE)
		{
			return FALSE;
		}

		return array(
			'username' => $row['username']
		);
	}
	else
	{
		return FALSE;
	}
}

function db_was_geekmail_sent_recently( $bggusername_tainted )
{
	global $dbh;

	$sth = $dbh->prepare('
		SELECT requested_at
			FROM `authrequests`
	WHERE username=:username
		');
	$sth->bindParam('username', $bggusername_tainted, PDO::PARAM_STR);
	$sth->execute();

	$items = array();
	if( $row = $sth->fetch(PDO::FETCH_ASSOC) )
	{
		if ($row === FALSE)
		{
			return FALSE;
		}

		return ( time() - $row['requested_at'] < 60 * 60 );
	}
	else
	{
		return FALSE;
	}
}

function db_add_auth_request( $bggusername_tainted, $cookie )
{
	global $dbh;

	# Delete any old outstanding requests
	$sth = $dbh->prepare('DELETE FROM authrequests where username=:username');
	$result = $sth->execute(array(
		'username' => $bggusername_tainted
	));

	# Add the new request
	$sth = $dbh->prepare('INSERT INTO authrequests' . 
		'(username,requested_at,cookie) VALUES (:username,:requested_at,:cookie)');
	$result = $sth->execute(array(
			'username' => $bggusername_tainted,
			'requested_at' => time(),
			'cookie' => $cookie
		));

	return $result;
}

function db_get_auth_requests_to_send( $limit )
{
	global $dbh;
	
	$sth = $dbh->prepare('
		SELECT * 
			FROM `authrequests`
			WHERE gm_sent_at IS NULL
		');
	$sth->execute();

	$items = array();
	while( $row = $sth->fetch(PDO::FETCH_ASSOC) )
	{
		$item = array(
			'id' => $row['id'],
			'username' => $row['username'],
			'cookie' => $row['cookie']
		);
		array_push( $items, $item );
	}

	return $items;
}

function db_get_auth_request_by_cookie( $cookie )
{
	global $dbh;
	
	$sth = $dbh->prepare('
		SELECT * 
			FROM `authrequests`
			WHERE cookie=:cookie
		');
	$sth->execute(array(
		'cookie' => $cookie
	));

	$items = array();
	if( $row = $sth->fetch(PDO::FETCH_ASSOC) )
	{
		$item = array(
			'id' => $row['id'],
			'username' => $row['username'],
			'cookie' => $row['cookie']
		);
		return $item;
	}
	else
	{
		return FALSE;
	}
}

function db_mark_auth_request_as_sent( $id )
{
	global $dbh;

	$sth = $dbh->prepare('UPDATE authrequests SET gm_sent_at=:gm_sent_at WHERE id=:id');
	$result = $sth->execute(array(
			'gm_sent_at' => time(),
			'id' => $id
		));

	return $result;
}

function db_mark_user_as_authenicated( $bggusername, $cookie, $auth_req_id )
{
	global $dbh;
	$id = FALSE;

	$sth = $dbh->prepare('
		SELECT id
			FROM `users`
	WHERE username=:username
		');
	$sth->bindParam('username', $bggusername, PDO::PARAM_STR);
	$sth->execute();

	$id = FALSE;
	if( $row = $sth->fetch(PDO::FETCH_ASSOC) )
	{
		$id = $row['id'];
	}

	$sth = NULL;
	$params = array(
		'username' => $bggusername,
		'cookie' => $cookie,
	);
	
	if( $id === FALSE )
	{
		$sth = $dbh->prepare(
			'INSERT INTO users (username, cookie) VALUES (:username, :cookie)');
	}
	else
	{
		$sth = $dbh->prepare(
			'UPDATE users SET username=:username, cookie=:cookie WHERE id=:id');
		$params['id'] = $id;
	}
	$result = $sth->execute( $params );

	# Now the user is authenticated, we can delete the auth request
	if( $result )
	{
		$sth = $dbh->prepare('DELETE FROM authrequests WHERE id=:id');
		$delete_result = $sth->execute( array( ':id' => $auth_req_id ) );
		if( $delete_result === FALSE )
		{
			$info = $sth->errorInfo();
			error_report("Warning: There was en error deleting auth request ID=" . $auth_req_id . " (" . $info[2] . "). Until it is deleted, that user cannot re-auth.");
		}
	}

	return ( $result !== FALSE );
}

?>
