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
	
	# This is the BGG account for the PHP script - who it acts as when sending 
	# geekmails awarding microbadges
	'bgg' => array(
		'username' => 'nobody',
		
		# This is the value of the cookie 'bggpassword' from rpggeek.com or 
		# boardgamegeek.com. To get it, visit either of those sites, and enable 
		# the developer console, and then view the cookies for the site. You 
		# should be able to copy 'bggpassword'
		'password_cookie' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',

		# Max number of geekmails that can be sent for authentication of new users
		'max_auth_geekmails_rate_per_hour' => 30,
		
		# Max number of geekmails that can be sent for awards of stars
		'max_award_geekmails_rate_per_hour' => 30,
	),
);
?>
