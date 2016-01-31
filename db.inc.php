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

function db_award_get( $id )
{
	global $dbh;

	$sth = $dbh->prepare('
		SELECT awards.*, username AS giver_username
			FROM `awards`
			LEFT JOIN users ON users.id = awards.giver_user_id
			WHERE awarded_at IS NULL AND awards.id = :id
		');
	$result = $sth->execute(array(
		'id' => $id
	));
	if( $result === FALSE )
	{
		$info = $sth->errorInfo();
		error_report_to_log("Error executing statement to get the award request ID=$id (" . $info[2] . ").");
		return FALSE;
	}

	if( $row = $sth->fetch(PDO::FETCH_ASSOC) )
	{
		$item = array(
			'id' => $row['id'],
			'giver_user_id' => $row['giver_user_id'],
			'recipient' => $row['receiver_bgg_username'],
			'giver_username' => $row['giver_username']
		);
		return $item;
	}
	else
	{
		$info = $sth->errorInfo();
		error_report_to_log("Error fetching award with ID=$id (" . $info[2] . ")");
		return FALSE;
	}
}

function db_get_user_by_cookie( $cookie )
{
	global $dbh;

	$sth = $dbh->prepare('
		SELECT id, username
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
			'id' => $row['id'],
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

function db_can_user_give( $user_id )
{
	global $dbh;
	
	$date_info = getdate();
	$month = $date_info["month"];
	$year = $date_info["year"];

	$sth = $dbh->prepare('
		SELECT count(*)
			FROM `awards`
			WHERE giver_user_id=:giver_user_id AND month=:month AND year=:year
		');
	$sth->execute(array(
		'month' => $month,
		'year' => $year,
		'giver_user_id' => $user_id
	));

	return ( ! ! $sth->fetch(PDO::FETCH_ASSOC) );
}

function db_award_get_last( $user_id )
{
	global $dbh;
	
	$date_info = getdate();
	$month = $date_info["month"];
	$year = $date_info["year"];

	$sth = $dbh->prepare('
		SELECT count(*)
			FROM `awards`
			WHERE giver_user_id=:giver_user_id AND month=:month AND year=:year
		');
	$sth->execute(array(
		'month' => $month,
		'year' => $year,
		'giver_user_id' => $user_id
	));

	if ( $row == $sth->fetch(PDO::FETCH_ASSOC) )
	{
		return array(
			'month' => $row['month'],
			'year' => $row['year']
		);
	}
	else
	{
		return FALSE;
	}
}

function db_award_add_to_queue( $giver_user_id, $recipient )
{
	global $dbh;
	
	$date_info = getdate();
	$month = $date_info["month"];
	$year = $date_info["year"];

	# Add the new request
	$sth = $dbh->prepare('INSERT INTO awards' . 
		'(giver_user_id, receiver_bgg_username, year, month) ' .
		'VALUES (:giver_user_id, :recipient, :year, :month)');
	$result = $sth->execute(array(
			'giver_user_id' => $giver_user_id,
			'recipient' => $recipient,
			'year' => $year,
			'month' => $month
		));

	if( $result === FALSE )
	{
		$info = $sth->errorInfo();
		#error_report_to_log("Warning: There was an error adding an award request (" . $info[2] . ").");
	}

	return $result;
}

function db_get_award_requests( $limit_tainted )
{
	global $dbh;

	$limit = intval( $limit_tainted );
	
	$sth = $dbh->prepare('
		SELECT awards.*, username AS giver_username
			FROM `awards`
			LEFT JOIN users ON users.id = awards.giver_user_id
			WHERE awarded_at IS NULL
			LIMIT ' . $limit . '
		');
	$result = $sth->execute(array(
		'limit' => $limit
	));
	if( $result === FALSE )
	{
		$info = $sth->errorInfo();
		error_report_to_log("Error executing statement to get the list of award requests (" . $info[2] . ").");
	}

	$items = array();
	while( $row = $sth->fetch(PDO::FETCH_ASSOC) )
	{
		$item = array(
			'id' => $row['id'],
			'giver_user_id' => $row['giver_user_id'],
			'recipient' => $row['receiver_bgg_username'],
			'giver_username' => $row['giver_username']
		);
		array_push( $items, $item );
	}

	return $items;
}

function db_award_set_awarded( $id )
{
	global $dbh;

	$sth = $dbh->prepare('UPDATE awards SET awarded_at=:awarded_at WHERE id=:id');
	$result = $sth->execute(array(
			'awarded_at' => time(),
			'id' => $id
		));

	return $result;
}

function db_award_set_verified( $id )
{
	global $dbh;

	$sth = $dbh->prepare('UPDATE awards SET award_verified=:award_verified WHERE id=:id');
	$result = $sth->execute(array(
			'award_verified' => 1,
			'id' => $id
		));
	
	if( $result === FALSE )
	{
		$info = $sth->errorInfo();
		error_report_to_log("Error setting request ID=$id as verified (" . $info[2] . ").");
	}

	return $result;
}

function db_award_delete( $id )
{
	global $dbh;

	$sth = $dbh->prepare( 'DELETE FROM awards WHERE id=:id' );
	$sth->bindParam( ':id', $id, PDO::PARAM_INT );
	$result = $sth->execute();

	if( $result === FALSE )
	{
		$info = $sth->errorInfo();
		error_report_to_log("Error executing statement to get the list of award requests (" . $info[2] . ").");
	}

	return $result;
}


?>
