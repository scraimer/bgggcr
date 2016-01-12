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

function db_get_user_by_cookie( $key )
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

		return $row['username'];
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

	$sth = $dbh->prepare('INSERT INTO authrequests' . 
		'(username,requested_at,cookie) VALUES (:username,:requested_at,:cookie)');
	$result = $sth->execute(array(
			'username' => $bggusername_tainted,
			'requested_at' => time(),
			'cookie' => $cookie
		));

	return $result;
}

?>
