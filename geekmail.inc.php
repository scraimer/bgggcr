<?php

require_once('config.inc.php');

function geekmail_sent( $recipient, $subject, $message )
{
	/*
 	 * POST request
 	 * 
 	 * URL: https://boardgamegeek.com/geekmail_controller.php
 	 * Cookie: <BGG cookies>
 	 * 
 	 * Form data:
 	 * 
 	 * action: save
 	 * messageid:
 	 * touser: scraimer
 	 * subject: test_subject
 	 * savecopy: 1
 	 * geek_link_select_1:
 	 * sizesel:10
 	 * body:test message
 	 * B1:Send
 	 * folder:inbox
 	 * label:
 	 * ajax:1
 	 * searchid:0
 	 * pageID:1
 	 * 
 	 */

	return FALSE;
}

?>
