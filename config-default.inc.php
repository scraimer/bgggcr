<?php

# Setup:
# 1. This file must be named 'config.inc.php' for it to be used.
# 2. Fill out the details below to configure it.

$config = array(
	'db' => array(
		'hostname' => 'mysql_host.com',
		'dbname' => 'bgggcr_db',
		'username' => 'admin',
		'password' => 'secret',
	),
	
	'http' => array(
		# The base URL for all the links to this directory. Used by the script to 
		# send out URLs. Please include a trailing slash.
		'base_url' => 'http://bgggcr.shalom.craimer.org/'
	),
	
	'bgg' => array(
		# The base URL for all the links to this directory. Used by the script to 
		# send out URLs. Please include a trailing slash.
		'username' => 'nobody',
		'password' => 'secret',
		'domain' => 'boardgamegeek.com',
		
		# This is the cookie string for accessing BGG/RPGEEK. It is set by 
		# bgg_login(), once per session.
		'cookie' => FALSE,

		# Max number of geekmails that can be sent for authentication of new users
		'max_auth_geekmails_rate_per_hour' => 30,
		
		# Max number of geekmails that can be sent for awards of stars
		'max_award_geekmails_rate_per_hour' => 30,

		# Auth message subject
		'auth_msg_subject' => "Geek Citizenship Award: Site Authentication",
		
		# Template for auth message. Use the following placeholders:
		# 
		#     %1$s = BGG Username
		#     %2$s = URL for authenticating their BGG username
		#     %3$s = URL for this website's home
		#     %4$s = BGG Username of the human in charge of this
		'auth_msg_template' =>
			"You're nearly done. After clicking on the link below, you'll be able " .
			"to award a star in recognition of another user's contribution to the 'geek: \n" .
			"[url]%2$s" . "[\/url]\n" .
			"\n".
			"If you have no idea what this is about, please contact the admin:\n" .
			"[user=%4$s][/user]\n"
	),
);
?>
